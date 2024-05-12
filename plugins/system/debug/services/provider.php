<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.debug
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\Debug\Extension\Debug;

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
        $container->share(
            PluginInterface::class,
            function (Container $container) {
                return new Debug(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'debug'),
                    Factory::getApplication(),
                    $container->get(DatabaseInterface::class)
                );
            }
        )->share(
            'plugin.information',
            [
                'class'      => Debug::class,
                'implements' => [SubscriberInterface::class => true],
                'eager'      => true,
            ]
        )->share(
            'plugin.executeValidation',
            function () {
                $app = Factory::getApplication();

                return $app->get('debug') || $app->get('debug_lang');
            }
        );
    }
};
