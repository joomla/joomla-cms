<?php

/**
 * @package     Joomla.Privacy
 * @subpackage  Webservices.Privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_privacy.
 *
 * @since  4.0.0
 */
class PlgWebservicesPrivacy extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_privacy's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $defaults    = ['component' => 'com_privacy'];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/privacy/requests', 'requests.displayList', [], $getDefaults),
            new Route(['GET'], 'v1/privacy/requests/:id', 'requests.displayItem', ['id' => '(\d+)'], $getDefaults),
            new Route(['GET'], 'v1/privacy/requests/export/:id', 'requests.export', ['id' => '(\d+)'], $getDefaults),
            new Route(['POST'], 'v1/privacy/requests', 'requests.add', [], $defaults),
        ];

        $router->addRoutes($routes);

        $routes = [
            new Route(['GET'], 'v1/privacy/consents', 'consents.displayList', [], $getDefaults),
            new Route(['GET'], 'v1/privacy/consents/:id', 'consents.displayItem', ['id' => '(\d+)'], $getDefaults),
            new Route(['DELETE'], 'v1/privacy/consents/:id', 'consents.delete', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
