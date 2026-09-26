<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\WebServices\Joomlaupdate\Extension;

use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Services adapter for com_joomlaupdate.
 *
 * @since  5.4.0
 */
final class Joomlaupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeApiRoute' => 'onBeforeApiRoute',
        ];
    }

    /**
     * Registers com_joomlaupdate's API's routes in the application
     *
     * @param   BeforeApiRouteEvent  $event  The event object
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function onBeforeApiRoute(BeforeApiRouteEvent $event): void
    {
        $router   = $event->getRouter();
        $defaults = ['component' => 'com_joomlaupdate', 'public' => true];

        $routes = [
            new Route(['GET'], 'v1/joomlaupdate/healthcheck', 'healthcheck.show', [], $defaults),
            new Route(['GET'], 'v1/joomlaupdate/getUpdate', 'updates.getUpdate', [], $defaults),
            new Route(['POST'], 'v1/joomlaupdate/prepareUpdate', 'updates.prepareUpdate', [], $defaults),
            new Route(['POST'], 'v1/joomlaupdate/finalizeUpdate', 'updates.finalizeUpdate', [], $defaults),
            new Route(['POST'], 'v1/joomlaupdate/notificationSuccess', 'notification.success', [], $defaults),
            new Route(['POST'], 'v1/joomlaupdate/notificationFailed', 'notification.failed', [], $defaults),
        ];

        $router->addRoutes($routes);
    }
}
