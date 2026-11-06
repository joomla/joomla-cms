<?php

/**
 * @package     Joomla.Tags
 * @subpackage  Webservices.tags
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Tags\Extension;

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_tags.
 *
 * @since  4.0.0
 */
final class Tags extends CMSPlugin implements SubscriberInterface
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
     * Registers com_tags's API's routes in the application
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
            'v1/tags',
            'tags',
            ['component' => 'com_tags']
        );

        $this->createTagHistoryRoutes($router);
    }

    /**
     * Create tag history routes
     *
     * @param   ApiRouter  &$router  The API Routing object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createTagHistoryRoutes(&$router): void
    {
        $defaults    = [
            'component'  => 'com_contenthistory',
            'type_alias' => 'com_tags.tag',
            'type_id'    => 8,
        ];
        $getDefaults = array_merge(['public' => false], $defaults);

        $routes = [
            new Route(['GET'], 'v1/tags/:id/contenthistory', 'history.displayList', ['id' => '(\d+)'], $getDefaults),
            new Route(['PATCH'], 'v1/tags/:id/contenthistory/keep', 'history.keep', ['id' => '(\d+)'], $defaults),
            new Route(['DELETE'], 'v1/tags/:id/contenthistory', 'history.delete', ['id' => '(\d+)'], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
