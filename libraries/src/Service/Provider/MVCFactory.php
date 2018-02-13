<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Service provider for the application's database dependency
 *
 * @since  4.0
 */
class MVCFactory implements ServiceProviderInterface
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
		$container->share(
				MVCFactoryFactoryInterface::class,
				function (Container $container)
				{
					return new MVCFactoryFactory($container->get(DatabaseInterface::class));
				},
				true
			);
	}
}
