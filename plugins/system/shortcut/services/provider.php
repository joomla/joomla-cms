<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\DummyPlugin;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Shortcut\Extension\Shortcut;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $app = Factory::getApplication();

                if (!$app->isClient('administrator')) {
                    // Return an empty class when we in wrong App
                    return new DummyPlugin();
                }

                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new Shortcut(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('system', 'shortcut')
                );
                $plugin->setApplication($app);

                return $plugin;
            }
        );
    }
};
