<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

use Joomla\CMS\Dispatcher\ModuleDispatcherFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service dispatcher factory.
 *
 * @since  4.0.0
 */
class ModuleDispatcherFactory implements ServiceProviderInterface
{
    /**
     * ComponentDispatcherFactory constructor.
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct(private readonly string $namespace)
    {
    }

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
        $container->set(
            ModuleDispatcherFactoryInterface::class,
            fn(Container $container) => new \Joomla\CMS\Dispatcher\ModuleDispatcherFactory($this->namespace)
        );
    }
}
