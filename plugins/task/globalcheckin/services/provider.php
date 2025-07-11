<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.Globalcheckin
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\Globalcheckin\Extension\Globalcheckin;

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
                $checkin = new Globalcheckin(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('task', 'globalcheckin')
                );
                $checkin->setDatabase($container->get(DatabaseInterface::class));

                return $checkin;
            }
        );
    }
};
