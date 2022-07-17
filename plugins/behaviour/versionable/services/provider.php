<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.versionable
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\CMSHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Filter\InputFilter;
use Joomla\Plugin\Behaviour\Versionable\Extension\Versionable;

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
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new Versionable(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('behaviour', 'versionable'),
                    new InputFilter(),
                    new CMSHelper()
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
