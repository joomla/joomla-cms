<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Plugins
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_plugins.
 *
 * @since  4.0.0
 */
class PlgWebservicesPlugins extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_plugins's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $defaults    = ['component' => 'com_plugins'];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/plugins', 'plugins.displayList', [], $getDefaults),
            new Route(['GET'], 'v1/plugins/:id', 'plugins.displayItem', ['id' => '(\d+)'], $getDefaults),
            new Route(['PATCH'], 'v1/plugins/:id', 'plugins.edit', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
