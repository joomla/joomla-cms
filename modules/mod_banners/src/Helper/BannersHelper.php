<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Banners\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\Component\Banners\Site\Model\BannersModel;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_banners
 *
 * @since  1.5
 */
class BannersHelper
{
    /**
     * Retrieve list of banners
     *
     * @param   Registry        $params  The module parameters
     * @param   BannersModel    $model   The model
     * @param   CMSApplication  $app     The application
     *
     * @return  mixed
     */
    public static function getList(Registry $params, BannersModel $model, CMSApplication $app)
    {
        $keywords = [];
        // Get all the ids from UserState
        $ids = $app->getUserState('article.ids', null);

        if ($ids) {
            $ids = implode(',', json_decode($ids));

            $db = Factory::getContainer()->get('DatabaseDriver');
            // Select the meta keywords from the all articles
            $query    = $db->getQuery(true);
            $query->select($db->quoteName('metakey'))
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('id') . ' IN (' . $ids . ')');

            $db->setQuery($query);
            try {
                $metakeys = $db->loadColumn();
            } catch (\RuntimeException $e) {
                $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

                return [];
            }

            if ($metakeys) {
                foreach ($metakeys as $metakey) {
                    $keys     = preg_split('/\s*,\s*/', trim($metakey));
                    $keywords = array_merge($keywords, $keys);
                }
                $keywords = array_unique($keywords);
            }
        }

        $config   = ComponentHelper::getParams('com_banners');

        $model->setState('filter.client_id', (int) $params->get('cid'));
        $model->setState('filter.category_id', $params->get('catid', []));
        $model->setState('list.limit', (int) $params->get('count', 1));
        $model->setState('list.start', 0);
        $model->setState('filter.ordering', $params->get('ordering'));
        $model->setState('filter.tag_search', $params->get('tag_search'));
        $model->setState('filter.keywords', $keywords);
        $model->setState('filter.language', $app->getLanguageFilter());

        $banners = $model->getItems();

        if ($banners) {
            if ($config->get('track_robots_impressions', 1) == 1 || !Browser::getInstance()->isRobot()) {
                $model->impress();
            }
        }

        return $banners;
    }
}
