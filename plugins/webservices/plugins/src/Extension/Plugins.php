<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.plugins
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Plugins\Extension;

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_plugins.
 *
 * @since  4.0.0
 */
final class Plugins extends CMSPlugin implements SubscriberInterface
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
     * Registers com_plugins's API's routes in the application
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
