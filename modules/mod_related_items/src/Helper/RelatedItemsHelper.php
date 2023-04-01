<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RelatedItems\Site\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\ArticleModel;
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
     * @param   Registry         $moduleParams  The module parameters.
     * @param   SiteApplication  $app           The current application.
     *
     * @return  \stdClass[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getRelatedArticles(Registry $moduleParams, SiteApplication $app): array
    {
        // Check if we are in an article view
        $input  = $app->getInput();
        $option = $input->getString('option');
        $view   = $input->getString('view');

        if (!($option === 'com_content' && $view === 'article')) {
            return [];
        }

        // Get the main article object
        $mvcContentFactory = $app->bootComponent('com_content')->getMVCFactory();

        /** @var ArticleModel $articleModel */
        $articleModel   = $mvcContentFactory->createModel('Article', 'Site', ['ignore_request' => true]);

        $urlId          = $input->getString('id');
        $currentArticle = explode(':', $urlId)[0];
        $appParams      = $app->getParams();

        $articleModel->setState('params', $appParams);
        $articleModel->setState('filter.published', 1);
        $articleModel->setState('article.id', (int) $currentArticle);

        $mainArticle = $articleModel->getItem();

        if (!$mainArticle || empty($mainArticle->metakey)) {
            return [];
        }

        $props = new \stdClass();

        $props->mainArticle = $mainArticle;
        $props->params      = $appParams->merge($moduleParams, true);
        $props->app         = $app;
        $props->factory     = $mvcContentFactory;

        return $this->getRelatedArticlesByMetakeys($props);
    }
    /**
     * Get the related articles matching by metakeys
     *
     * @param   \stdClass  $props
     *
     * @return  \stdClass[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getRelatedArticlesByMetakeys(\stdClass $props): array
    {
        $keys = explode(',', $props->mainArticle->metakey);

        // Clean the article meta-keys and add wildcards for the SQL LIKE Operator
        $metaKeys = array_map(function ($k) {
            $keyTrimmed = \trim($k);
            return $keyTrimmed ? '%' . $keyTrimmed . '%' : '';
        }, $keys);

        // Select other articles based on the metakey field 'like' the keys found
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $user       = $props->app->getIdentity();
        $authorised = Access::getAuthorisedViewLevels($user->get('id'));

        $id = (int) $props->mainArticle->id;

        $query->select($db->quoteName('a.id'))
            ->from($db->quoteName('#__content', 'a'))
            ->where($db->quoteName('a.id') . ' != :id')
            ->where($db->quoteName('a.state') . ' = ' . ContentComponent::CONDITION_PUBLISHED)
            ->whereIn($db->quoteName('a.access'), $authorised)
            ->bind(':id', $id, ParameterType::INTEGER);

        $bindWords = $query->bindArray($metaKeys, ParameterType::STRING);
        $wheres    = [];

        foreach ($bindWords as $keyword) {
            $wheres[] = $db->quoteName('a.metakey') . ' LIKE ' . $keyword;
        }

        $now = Factory::getDate()->toSql();

        $query->extendWhere('AND', $wheres, 'OR')
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :nowDate1',
                ],
                'OR'
            )
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
        if ($props->app->getLanguageFilter()) {
            $query->whereIn($db->quoteName('a.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        $query->setLimit((int) $props->params->get('maximum', 5));
        $db->setQuery($query);

        try {
            $articlesIds = $db->loadColumn();
        } catch (\RuntimeException $e) {
            $props->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return [];
        }

        $relatedArticles = [];

        if (\count($articlesIds)) {
            /** @var ArticlesModel $articlesModel */
            $articlesModel = $props->factory->createModel('Articles', 'Site', ['ignore_request' => true]);

            // Set application parameters in model
            $articlesModel->setState('params', $props->params);

            // This module does not use tags data
            $articlesModel->setState('load_tags', false);

            // Filter only for the related articles ID found
            $articlesModel->setState('filter.article_id', $articlesIds);
            $articlesModel->setState('filter.published', 1);

            $relatedArticles = $articlesModel->getItems();
        }

        if (\count($relatedArticles)) {
            // Prepare data for display using display options
            foreach ($relatedArticles as &$article) {
                $article->slug  = $article->id . ':' . $article->alias;
                $article->route = Route::_(RouteHelper::getArticleRoute($article->slug, $article->catid, $article->language));
            }
        }

        return $relatedArticles;
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
     * @deprecated  __DEPLOY_VERSION__  will be removed in 6.0
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
