<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\Dispatcher as EventDispatcher;
use Joomla\Event\DispatcherInterface as EventDispatcherInterface;

/**
 * Service provider for the application's event dispatcher dependency
 *
 * @since  4.0.0
 */
class Dispatcher implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container)
    {
        $container->alias('dispatcher', EventDispatcherInterface::class)
            ->alias(EventDispatcher::class, EventDispatcherInterface::class)
            ->share(
                EventDispatcherInterface::class,
                function (Container $container) {
                    return new EventDispatcher();
                },
                true
            );
    }
}
