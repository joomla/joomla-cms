<?php

/**
 * @package Joomla.Plugin
 * @subpackage Task.WorkflowTransition
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\Task\WorkflowTransition\Extension\WorkflowTransition;

\defined('_JEXEC') or die;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container The DI container.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */

    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            $container->lazy(WorkflowTransition::class, function (Container $container) {
                $plugin = new WorkflowTransition(
                    (array) PluginHelper::getPlugin('task', 'workflowtransition')
                );

                $plugin->setDatabase($container->get(DatabaseInterface::class));
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            })
        );
    }
};
