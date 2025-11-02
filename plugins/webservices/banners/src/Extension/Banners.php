<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.banners
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Banners\Extension;

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_banners.
 *
 * @since  4.0.0
 */
final class Banners extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeApiRoute' => 'onBeforeApiRoute',
        ];
    }

    /**
     * Registers com_banners's API's routes in the application
     *
     * @param   BeforeApiRouteEvent  $event  The event object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeApiRoute(BeforeApiRouteEvent $event): void
    {
        $router = $event->getRouter();

        $router->createCRUDRoutes(
            'v1/banners',
            'banners',
            ['component' => 'com_banners']
        );

        $router->createCRUDRoutes(
            'v1/banners/clients',
            'clients',
            ['component' => 'com_banners']
        );

        $router->createCRUDRoutes(
            'v1/banners/categories',
            'categories',
            ['component' => 'com_categories', 'extension' => 'com_banners']
        );

        $this->createContentHistoryRoutes($router);
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
    private function createContentHistoryRoutes(&$router): void
    {
        $defaults    = [
            'component'  => 'com_contenthistory',
            'type_alias' => 'com_banners.banner',
            'type_id'    => 9,
        ];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/banners/:id/contenthistory', 'history.displayList', ['id' => '(\d+)'], $getDefaults),
            new Route(['PATCH'], 'v1/banners/:id/contenthistory/keep', 'history.keep', ['id' => '(\d+)'], $defaults),
            new Route(['DELETE'], 'v1/banners/:id/contenthistory', 'history.delete', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
