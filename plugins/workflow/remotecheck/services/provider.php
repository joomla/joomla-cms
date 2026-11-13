<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.RemoteCheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\Workflow\RemoteCheck\Extension\RemoteCheck;

\defined('_JEXEC') or die;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            $container->lazy(RemoteCheck::class, function (Container $container) {
                $plugin = new RemoteCheck(
                    (array) PluginHelper::getPlugin('workflow', 'remotecheck')
                );
                $plugin->setApplication(Factory::getApplication());

                $plugin->setCacheControllerFactory($container->get(CacheControllerFactoryInterface::class));

                return $plugin;
            })
        );
    }
};
