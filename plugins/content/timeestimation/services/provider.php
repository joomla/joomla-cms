<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Timeestimation
 *
* @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
* @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Prevent direct access
defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Factory;
use Joomla\Plugin\Content\Timeestimation\Extension\Timeestimation;


return new class() implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config = (array) PluginHelper::getPlugin('content', 'timeestimation');
                $subject = $container->get(DispatcherInterface::class);
                $app = Factory::getApplication();

                $plugin = new Timeestimation($subject, $config);
                $plugin->setApplication($app);

                return $plugin;
            }
        );
    }
};
