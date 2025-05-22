<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.Autoupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Quickicon\Autoupdate\Extension\Autoupdate;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                // @Todo This needs to be changed to a proper factory
                $plugin = \Joomla\CMS\Plugin\PluginHelper::getPlugin('quickicon', 'autoupdate');

                $plugin = new Autoupdate(
                    $container->get(DispatcherInterface::class),
                    Factory::getApplication()->getDocument(),
                    (array) $plugin
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
