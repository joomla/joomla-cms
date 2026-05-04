<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.compat7
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\Behaviour\Compat7\Extension\Compat7;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     * @since   7.0.0
     */
    public function register(Container $container)
    {
        // The compatibility plugin is a special case which does not use the lazy loading because it
        // uses the constructor to load b/c code, the constructor might not be initialized when lazy loading is used.
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Compat7((array) PluginHelper::getPlugin('behaviour', 'compat7'));
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
