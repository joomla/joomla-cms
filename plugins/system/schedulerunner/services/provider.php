<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.schedulerunner
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Event\LazyServiceSubscriber;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\ScheduleRunner\Extension\ScheduleRunner;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function register(Container $container): void
    {
        $container->set(
            ScheduleRunner::class,
            function (Container $container) {
                $plugin     = new ScheduleRunner(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'schedulerunner')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        )->set(
            PluginInterface::class,
            function (Container $container) {
                return new LazyServiceSubscriber($container, ScheduleRunner::class);
            }
        );
    }
};
