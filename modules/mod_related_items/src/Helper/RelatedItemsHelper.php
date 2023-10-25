<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RelatedItems\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_related_items
 *
 * @since  1.5
 */
class RelatedItemsHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Retrieve a list of related articles based on the metakey field
     *
     * @param   Registry         $params  The module parameters.
     * @param   SiteApplication  $app     The current application.
     *
     * @return  \stdClass[]
     *
     * @since   4.4.0
     */
    public function getRelatedArticles(Registry $params, SiteApplication $app): array
    {
        $db        = $this->getDatabase();
        $input     = $app->getInput();
        $groups    = $app->getIdentity()->getAuthorisedViewLevels();
        $maximum   = (int) $params->get('maximum', 5);
        $factory   = $app->bootComponent('com_content')->getMVCFactory();

        // Get an instance of the generic articles model
        /** @var ArticlesModel $articles */
        $articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

        // Set application parameters in model
        $articles->setState('params', $app->getParams());

        $option = $input->get('option');
        $view   = $input->get('view');

        if (!($option === 'com_content' && $view === 'article')) {
            return [];
        }

        $temp = $input->getString('id');
        $temp = explode(':', $temp);
        $id   = (int) $temp[0];

        $now      = Factory::getDate()->toSql();
        $related  = [];
        $query    = $db->getQuery(true);

        if ($id) {
            // Select the meta keywords from the item
            $query->select($db->quoteName('metakey'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $metakey = trim($db->loadResult());
            } catch (\RuntimeException $e) {
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

                return [];
            }

            // Explode the meta keys on a comma
            $keys  = explode(',', $metakey);
            $likes = [];

            // Assemble any non-blank word(s)
            foreach ($keys as $key) {
                $key = trim($key);

                if ($key) {
                    $likes[] = $db->escape($key);
                }
            }

            if (\count($likes)) {
                // Select other items based on the metakey field 'like' the keys found
                $query->clear()
                    ->select($db->quoteName('a.id'))
                    ->from($db->quoteName('#__content', 'a'))
                    ->where($db->quoteName('a.id') . ' != :id')
                    ->where($db->quoteName('a.state') . ' = ' . ContentComponent::CONDITION_PUBLISHED)
                    ->whereIn($db->quoteName('a.access'), $groups)
                    ->bind(':id', $id, ParameterType::INTEGER);

                $binds  = [];
                $wheres = [];

                foreach ($likes as $keyword) {
                    $binds[] = '%' . $keyword . '%';
                }

                $bindNames = $query->bindArray($binds, ParameterType::STRING);

                foreach ($bindNames as $keyword) {
                    $wheres[] = $db->quoteName('a.metakey') . ' LIKE ' . $keyword;
                }

                $query->extendWhere('AND', $wheres, 'OR')
                    ->extendWhere('AND', [ $db->quoteName('a.publish_up') . ' IS NULL', $db->quoteName('a.publish_up') . ' <= :nowDate1'], 'OR')
                    ->extendWhere(
                        'AND',
                        [
                            $db->quoteName('a.publish_down') . ' IS NULL',
                            $db->quoteName('a.publish_down') . ' >= :nowDate2',
                        ],
                        'OR'
                    )
                    ->bind([':nowDate1', ':nowDate2'], $now);

                // Filter by language
                if (Multilanguage::isEnabled()) {
                    $query->whereIn($db->quoteName('a.language'), [$app->getLanguage()->getTag(), '*'], ParameterType::STRING);
                }

                $query->setLimit($maximum);
                $db->setQuery($query);

                try {
                    $articleIds = $db->loadColumn();
                } catch (\RuntimeException $e) {
                    $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

                    return [];
                }

                if (\count($articleIds)) {
                    $articles->setState('filter.article_id', $articleIds);
                    $articles->setState('filter.published', 1);
                    $related = $articles->getItems();
                }

                unset($articleIds);
            }
        }

        if (\count($related)) {
            // Prepare data for display using display options
            foreach ($related as &$item) {
                $item->slug  = $item->id . ':' . $item->alias;
                $item->route = Route::_(RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language));
            }
        }

        return $related;
    }

    /**
     * Get a list of related articles
     *
     * @param   Registry  &$params  module parameters
     *
     * @return  array
     *
     * @since   1.6
     *
     * @deprecated  4.4.0  will be removed in 6.0
     *              Use the non-static method getRelatedArticles
     *              Example: Factory::getApplication()->bootModule('mod_related_items', 'site')
     *                           ->getHelper('RelatedItemsHelper')
     *                           ->getRelatedArticles($params, Factory::getApplication())
     */
    public static function getList(&$params)
    {
        /** @var SiteApplication $app */
        $app = Factory::getApplication();

        return (new self())->getRelatedArticles($params, $app);
    }
}
