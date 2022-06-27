<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.SiteStatus
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\SiteStatus\Extension\SiteStatus;
use Joomla\Utilities\ArrayHelper;

return new class implements ServiceProviderInterface
{
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
                $plugin = new SiteStatus(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('task', 'sitestatus'),
                    ArrayHelper::fromObject(new JConfig()),
                    JPATH_CONFIGURATION . '/configuration.php'
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
