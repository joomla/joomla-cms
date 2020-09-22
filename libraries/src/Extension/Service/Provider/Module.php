<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Dispatcher\ModuleDispatcherFactoryInterface;
use Joomla\CMS\Extension\ModuleInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the service based modules.
 *
 * @since  4.0.0
 */
class Module implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->set(
			ModuleInterface::class,
			function (Container $container)
			{
				return new \Joomla\CMS\Extension\Module($container->get(ModuleDispatcherFactoryInterface::class));
			}
		);
	}
}
