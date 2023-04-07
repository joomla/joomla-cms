<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Service provider for the service dispatcher factory.
 *
 * @since  4.0.0
 */
class ComponentDispatcherFactory implements ServiceProviderInterface
{
    /**
     * The component namespace
     *
     * @var  string
     *
     * @since   4.0.0
     */
    private $namespace;

    /**
     * ComponentDispatcherFactory constructor.
     *
     * @param   string  $namespace  The namespace
     *
     * @since   4.0.0
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
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
            ComponentDispatcherFactoryInterface::class,
            function (Container $container) {
                return new \Joomla\CMS\Dispatcher\ComponentDispatcherFactory($this->namespace, $container->get(MVCFactoryInterface::class));
            }
        );
    }
}
