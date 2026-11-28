<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.jadp
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Plugin\System\JADP\Extension\JADP;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            $container->lazy(JADP::class, static function (Container $container) {
                $plugin = new JADP((array) PluginHelper::getPlugin('system', 'jadp'));
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            })
        );
    }
};
