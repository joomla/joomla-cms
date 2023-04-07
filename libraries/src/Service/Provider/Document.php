<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Document\Factory;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the application's document dependency
 *
 * @since  4.0.0
 */
class Document implements ServiceProviderInterface
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
        $container->alias('document.factory', FactoryInterface::class)
            ->alias(Factory::class, FactoryInterface::class)
            ->share(
                FactoryInterface::class,
                function (Container $container) {
                    $factory = new Factory();
                    $factory->setCacheControllerFactory($container->get(CacheControllerFactoryInterface::class));

                    return $factory;
                },
                true
            );
    }
}
