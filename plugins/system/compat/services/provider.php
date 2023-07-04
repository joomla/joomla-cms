<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.compat
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Router\SiteRouter;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\Compat\Extension\Compat;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     * @since   5.0.0
     */
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin     = PluginHelper::getPlugin('system', 'compat');
                $dispatcher = $container->get(DispatcherInterface::class);
                $profiler   = (defined('JDEBUG') && JDEBUG) ? Profiler::getInstance('Application') : null;
                $router     = $container->has(SiteRouter::class) ? $container->get(SiteRouter::class) : null;

                $plugin = new Compat($dispatcher, (array) $plugin, $profiler, $router);
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
