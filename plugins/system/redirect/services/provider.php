<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Menu\MenuFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\System\Redirect\Extension\Redirect;

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
            PluginInterface::class,
            $container->lazy(Redirect::class, function (Container $container) {
                $plugin     = new Redirect(
                    (array) PluginHelper::getPlugin('system', 'redirect')
                );
                $plugin->setApplication(Factory::getApplication());
                // We need the site application for the router and menu in guest context
                $plugin->setSiteApplication($container->get(SiteApplication::class));
                $plugin->setDatabase($container->get(DatabaseInterface::class));
                $plugin->setMenuFactory($container->get(MenuFactoryInterface::class));
                $plugin->setLanguageFactory($container->get(LanguageFactoryInterface::class));
                $plugin->setCurrentUser(Factory::getApplication()->getIdentity());

                return $plugin;
            })
        );
    }
};
