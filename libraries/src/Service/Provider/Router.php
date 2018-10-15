<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Router\RouterFactory;
use Joomla\CMS\Router\RouterFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the router dependency
 *
 * @since  4.0
 */
class Router implements ServiceProviderInterface
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
		$container->alias('router.factory', RouterFactoryInterface::class)
			->alias(RouterFactory::class, RouterFactoryInterface::class)
			->share(
				RouterFactoryInterface::class,
				function (Container $container)
				{
					return new RouterFactory;
				},
				true
			);
	}
}
