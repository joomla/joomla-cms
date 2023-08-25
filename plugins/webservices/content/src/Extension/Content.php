<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Content\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_content.
 *
 * @since  4.0.0
 */
final class Content extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Registers com_content's API's routes in the application
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
            'v1/content/articles',
            'articles',
            ['component' => 'com_content']
        );

        $router->createCRUDRoutes(
            'v1/content/categories',
            'categories',
            ['component' => 'com_categories', 'extension' => 'com_content']
        );

        $this->createFieldsRoutes($router);

        $this->createContentHistoryRoutes($router);
    }

    /**
     * Create fields routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function createFieldsRoutes(&$router)
    {
        $router->createCRUDRoutes(
            'v1/fields/content/articles',
            'fields',
            ['component' => 'com_fields', 'context' => 'com_content.article']
        );

        $router->createCRUDRoutes(
            'v1/fields/content/categories',
            'fields',
            ['component' => 'com_fields', 'context' => 'com_content.categories']
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/content/articles',
            'groups',
            ['component' => 'com_fields', 'context' => 'com_content.article']
        );

        $router->createCRUDRoutes(
            'v1/fields/groups/content/categories',
            'groups',
            ['component' => 'com_fields', 'context' => 'com_content.categories']
        );
    }

    /**
     * Create contenthistory routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function createContentHistoryRoutes(&$router)
    {
        $defaults    = [
            'component'  => 'com_contenthistory',
            'type_alias' => 'com_content.article',
            'type_id'    => 1,
        ];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/content/articles/:id/contenthistory', 'history.displayList', ['id' => '(\d+)'], $getDefaults),
            new Route(['PATCH'], 'v1/content/articles/:id/contenthistory/keep', 'history.keep', ['id' => '(\d+)'], $defaults),
            new Route(['DELETE'], 'v1/content/articles/:id/contenthistory', 'history.delete', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
