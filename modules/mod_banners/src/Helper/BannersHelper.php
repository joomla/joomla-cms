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
     * @param   CMSApplication  $app     The application
     *
     * @return  mixed
     *
     * @since   5.1.0
     */
    public function getBanners(Registry $params, CMSApplication $app)
    {
        /** @var BannersModel $model */
        $model = $app->bootComponent('com_banners')->getMVCFactory()->createModel('Banners', 'Site', ['ignore_request' => true]);

        $keywords = explode(',', $app->getDocument()->getMetaData('keywords'));
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

    /**
     * Retrieve list of banners
     *
     * @param   Registry        $params  The module parameters
     * @param   BannersModel    $model   The model
     * @param   CMSApplication  $app     The application
     *
     * @return  mixed
     *
     * @since   1.5
     *
     * @deprecated 5.1.0 will be removed in 7.0
     *             Use the non-static method getBanners
     *             Example: Factory::getApplication()->bootModule('mod_banners', 'site')
     *                          ->getHelper('BannersHelper')
     *                          ->getBanners($params, Factory::getApplication())
     */
    public static function getList(Registry $params, BannersModel $model, CMSApplication $app)
    {
        return (new self())->getBanners($params, $app);
    }
}
