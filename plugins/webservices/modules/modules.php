<?php

/**
 * @package     Joomla.Modules
 * @subpackage  Webservices.Modules
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_modules.
 *
 * @since  4.0.0
 */
class PlgWebservicesModules extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_modules's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $routes = array(
            new Route(
                ['GET'],
                'v1/modules/types/site',
                'modules.getTypes',
                [],
                ['public' => false, 'component' => 'com_modules', 'client_id' => 0]
            ),
            new Route(
                ['GET'],
                'v1/modules/types/administrator',
                'modules.getTypes',
                [],
                ['public' => false, 'component' => 'com_modules', 'client_id' => 1]
            ),
        );

        $router->addRoutes($routes);

        $router->createCRUDRoutes(
            'v1/modules/site',
            'modules',
            ['component' => 'com_modules', 'client_id' => 0]
        );

        $router->createCRUDRoutes(
            'v1/modules/administrator',
            'modules',
            ['component' => 'com_modules', 'client_id' => 1]
        );
    }
}
