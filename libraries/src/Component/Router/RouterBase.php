<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base component routing class
 *
 * @since  3.3
 */
abstract class RouterBase implements RouterInterface
{
    /**
     * Application object to use in the router
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.4
     */
    public $app;

    /**
     * Menu object to use in the router
     *
     * @var    \Joomla\CMS\Menu\AbstractMenu
     * @since  3.4
     */
    public $menu;

    /**
     * Class constructor.
     *
     * @param   \Joomla\CMS\Application\CMSApplication  $app   Application-object that the router should use
     * @param   \Joomla\CMS\Menu\AbstractMenu           $menu  Menu-object that the router should use
     *
     * @since   3.4
     */
    public function __construct($app = null, $menu = null)
    {
        if ($app) {
            $this->app = $app;
        } else {
            $this->app = Factory::getApplication();
        }

        if ($menu) {
            $this->menu = $menu;
        } else {
            $this->menu = $this->app->getMenu();
        }
    }

    /**
     * Generic method to preprocess a URL
     *
     * @param   array  $query  An associative array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function preprocess($query)
    {
        return $query;
    }
}
