<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.guidedtours
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\GuidedTours\Extension\GuidedTours;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $app        = Factory::getApplication();

                $plugin = new GuidedTours(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('system', 'guidedtours'),
                    $app->isClient('administrator')
                );

                $plugin->setApplication($app);

                $wa = $container->get(WebAssetRegistry::class);

                $wa->addRegistryFile('media/plg_system_guidedtours/joomla.asset.json');

                return $plugin;
            }
        );
    }
};
