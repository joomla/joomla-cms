<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Plugin\PluginFactory;
use Joomla\CMS\Plugin\PluginFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Service provider for the form dependency
 *
 * @since  4.0
 */
class Plugin implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function register(Container $container)
	{
		$container->alias('plugin.factory', PluginFactoryInterface::class)
			->share(
				PluginFactoryInterface::class,
				function (Container $container)
				{
					return new PluginFactory($container->get(DispatcherInterface::class));
				},
				true
			);
	}
}
