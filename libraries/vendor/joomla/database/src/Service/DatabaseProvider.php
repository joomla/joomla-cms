<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Service;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * Database service provider
 *
 * @since  __DEPLOY_VERSION__
 */
class DatabaseProvider implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function register(Container $container)
	{
		$container->alias(DatabaseInterface::class, DatabaseDriver::class)
			->share(
				DatabaseDriver::class,
				function (Container $container)
				{
					/** @var \Joomla\Registry\Registry $config */
					$config  = $container->get('config');
					$options = (array) $config->get('database');

					return $container->get(DatabaseFactory::class)->getDriver($options['driver'], $options);
				}
			);

		$container->share(
			DatabaseFactory::class,
			function (Container $container)
			{
				return new DatabaseFactory;
			}
		);
	}
}
