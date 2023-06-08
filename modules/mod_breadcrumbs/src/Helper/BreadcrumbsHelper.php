<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_breadcrumbs
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Breadcrumbs\Site\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_breadcrumbs
 *
 * @since  1.5
 */
class BreadcrumbsHelper
{
    /**
     * Retrieve breadcrumb items
     *
     * @param   Registry         $params  The module parameters
     * @param   SiteApplication  $app     The application
     *
     * @return  array
     *
     * @since   4.4.0
     */
    public function getBreadcrumbs(Registry $params, SiteApplication $app): array
    {
        // Get the PathWay object from the application
        $pathway = $app->getPathway();
        $items   = $pathway->getPathway();
        $count   = \count($items);

        // Don't use $items here as it references JPathway properties directly
        $crumbs = [];

        for ($i = 0; $i < $count; $i++) {
            $crumbs[$i]       = new \stdClass();
            $crumbs[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
            $crumbs[$i]->link = $items[$i]->link;
        }

        if ($params->get('showHome', 1)) {
            array_unshift($crumbs, $this->getHomeItem($params, $app));
        }

        return $crumbs;
    }

    /**
     * Retrieve home item (start page)
     *
     * @param   Registry         $params  The module parameters
     * @param   SiteApplication  $app     The application
     *
     * @return  object
     *
     * @since   4.4.0
     */
    public function getHomeItem(Registry $params, SiteApplication $app): object
    {
        $menu = $app->getMenu();

        if (Multilanguage::isEnabled()) {
            $home = $menu->getDefault($app->getLanguage()->getTag());
        } else {
            $home = $menu->getDefault();
        }

        $item       = new \stdClass();
        $item->name = htmlspecialchars($params->get('homeText', $app->getLanguage()->_('MOD_BREADCRUMBS_HOME')), ENT_COMPAT, 'UTF-8');
        $item->link = 'index.php?Itemid=' . $home->id;

        return $item;
    }

    /**
     * Set the breadcrumbs separator for the breadcrumbs display.
     *
     * @param   string  $custom  Custom xhtml compliant string to separate the items of the breadcrumbs
     *
     * @return  string  Separator string
     *
     * @since   1.5
     *
     * @deprecated 4.4.0 will be removed in 6.0 as this function is not used anymore
     */
    public static function setSeparator($custom = null)
    {
        $lang = Factory::getApplication()->getLanguage();

        // If a custom separator has not been provided we try to load a template
        // specific one first, and if that is not present we load the default separator
        if ($custom === null) {
            if ($lang->isRtl()) {
                $_separator = HTMLHelper::_('image', 'system/arrow_rtl.png', null, null, true);
            } else {
                $_separator = HTMLHelper::_('image', 'system/arrow.png', null, null, true);
            }
        } else {
            $_separator = htmlspecialchars($custom, ENT_COMPAT, 'UTF-8');
        }

        return $_separator;
    }

    /**
     * Retrieve breadcrumb items
     *
     * @param   Registry        $params  The module parameters
     * @param   CMSApplication  $app     The application
     *
     * @return  array
     *
     * @since   1.5
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *             Use the non-static method getBreadcrumbs
     *             Example: Factory::getApplication()->bootModule('mod_breadcrumbs', 'site')
     *                          ->getHelper('BreadcrumbsHelper')
     *                          ->getBreadcrumbs($params, Factory::getApplication())
     */
    public static function getList(Registry $params, CMSApplication $app)
    {
        return (new self())->getBreadcrumbs($params, Factory::getApplication());
    }

    /**
     * Retrieve home item (start page)
     *
     * @param   Registry        $params  The module parameters
     * @param   CMSApplication  $app     The application
     *
     * @return  object
     *
     * @since   4.2.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *             Use the non-static method getHomeItem
     *             Example: Factory::getApplication()->bootModule('mod_breadcrumbs', 'site')
     *                          ->getHelper('BreadcrumbsHelper')
     *                          ->getHomeItem($params, Factory::getApplication())
     */
    public static function getHome(Registry $params, CMSApplication $app)
    {
        return (new self())->getHomeItem($params, Factory::getApplication());
    }
}
