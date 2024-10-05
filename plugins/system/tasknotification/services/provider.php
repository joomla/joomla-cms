<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.tasknotification
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Event\LazyServiceSubscriber;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\TaskNotification\Extension\TaskNotification;

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
            TaskNotification::class,
            function (Container $container) {
                $plugin     = new TaskNotification(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'tasknotification')
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));
                $plugin->setUserFactory($container->get(UserFactoryInterface::class));

                return $plugin;
            }
        )->set(
            PluginInterface::class,
            function (Container $container) {
                return new LazyServiceSubscriber($container, TaskNotification::class);
            }
        );
    }
};
