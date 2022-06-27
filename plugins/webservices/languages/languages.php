<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.Languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

/**
 * Web Services adapter for com_languages.
 *
 * @since  4.0.0
 */
class PlgWebservicesLanguages extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_languages's API's routes in the application
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(&$router)
    {
        $router->createCRUDRoutes(
            'v1/languages/content',
            'languages',
            ['component' => 'com_languages']
        );

        $this->createLanguageOverridesRoutes($router);
        $this->createLanguageInstallerRoutes($router);
    }

    /**
     * Create language overrides routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function createLanguageOverridesRoutes(&$router)
    {
        $defaults = ['component' => 'com_languages'];

        $routes = [
            new Route(['POST'], 'v1/languages/overrides/search', 'strings.search', [], $defaults),
            new Route(['POST'], 'v1/languages/overrides/search/cache/refresh', 'strings.refresh', [], $defaults),
        ];

        $router->addRoutes($routes);

        /** @var \Joomla\Component\Languages\Administrator\Model\LanguagesModel $model */
        $model = Factory::getApplication()->bootComponent('com_languages')
            ->getMVCFactory()->createModel('Languages', 'Administrator', ['ignore_request' => true]);

        foreach ($model->getItems() as $item) {
            $baseName          = 'v1/languages/overrides/site/' . $item->lang_code;
            $controller        = 'overrides';
            $overridesDefaults = array_merge($defaults, ['lang_code' => $item->lang_code, 'app' => 'site']);
            $getDefaults       = array_merge(['public' => false], $overridesDefaults);

            $routes = [
                new Route(['GET'], $baseName, $controller . '.displayList', [], $getDefaults),
                new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', ['id' => '([A-Z0-9_]+)'], $getDefaults),
                new Route(['POST'], $baseName, $controller . '.add', [], $overridesDefaults),
                new Route(['PATCH'], $baseName . '/:id', $controller . '.edit', ['id' => '([A-Z0-9_]+)'], $overridesDefaults),
                new Route(['DELETE'], $baseName . '/:id', $controller . '.delete', ['id' => '([A-Z0-9_]+)'], $overridesDefaults),
            ];

            $router->addRoutes($routes);

            $baseName          = 'v1/languages/overrides/administrator/' . $item->lang_code;
            $overridesDefaults = array_merge($defaults, ['lang_code' => $item->lang_code, 'app' => 'administrator']);
            $getDefaults       = array_merge(['public' => false], $overridesDefaults);

            $routes = [
                new Route(['GET'], $baseName, $controller . '.displayList', [], $getDefaults),
                new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', ['id' => '([A-Z0-9_]+)'], $getDefaults),
                new Route(['POST'], $baseName, $controller . '.add', [], $overridesDefaults),
                new Route(['PATCH'], $baseName . '/:id', $controller . '.edit', ['id' => '([A-Z0-9_]+)'], $overridesDefaults),
                new Route(['DELETE'], $baseName . '/:id', $controller . '.delete', ['id' => '([A-Z0-9_]+)'], $overridesDefaults),
            ];

            $router->addRoutes($routes);
        }
    }

    /**
     * Create language installer routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function createLanguageInstallerRoutes(&$router)
    {
        $defaults    = ['component' => 'com_installer'];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/languages', 'languages.displayList', [], $getDefaults),
            new Route(['POST'], 'v1/languages', 'languages.install', [], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
