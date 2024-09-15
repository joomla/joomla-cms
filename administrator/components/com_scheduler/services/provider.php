<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Scheduler\Administrator\Extension\SchedulerComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The com_scheduler service provider.
 * Returns an instance of the Component's Service Provider Interface
 * used to register the components initializers into a DI container
 * created by the application.
 *
 * @since  4.1.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container $container The DI container.
     *
     * @return void
     *
     * @since  4.1.0
     */
    public function register(Container $container)
    {
        /**
         * Register the MVCFactory and ComponentDispatcherFactory providers to map
         * 'MVCFactoryInterface' and 'ComponentDispatcherFactoryInterface' to their
         * initializers and register them with the component's DI container.
         */
        $container->registerServiceProvider(new MVCFactory('\\Joomla\\Component\\Scheduler'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Joomla\\Component\\Scheduler'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new SchedulerComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
            }
        );
    }
};
