<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Mysql\MysqlDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Service provider for the application's database dependency
 *
 * @since  4.0
 */
class Database implements ServiceProviderInterface
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
		$container->alias('db', DatabaseInterface::class)
			->alias('JDatabaseDriver', DatabaseInterface::class)
			->alias(DatabaseDriver::class, DatabaseInterface::class)
			->share(
				DatabaseInterface::class,
			function (Container $container)
			{
				$conf = \JFactory::getConfig();

				$dbtype = $conf->get('dbtype');

				/*
				 * In Joomla! 3.x and earlier the `mysql` type was used for the `ext/mysql` PHP extension, which is no longer supported.
				 * The `pdomysql` type represented the PDO MySQL adapter.  With the Framework's package in use, the PDO MySQL adapter
				 * is now the `mysql` type.  Therefore, we check two conditions:
				 *
				 * 1) Is the type `pdomysql`, if so switch to `mysql`
				 * 2) Is the type `mysql`, if so make sure PDO MySQL is supported and if not switch to `mysqli`
				 *
				 * For these cases, if a connection cannot be made with MySQLi, the database API will handle throwing an Exception
				 * so we don't need to make any additional checks for MySQLi.
				 */
				if (strtolower($dbtype) === 'pdomysql')
				{
					$dbtype = 'mysql';
				}

				if (strtolower($dbtype) === 'mysql')
				{
					if (!MysqlDriver::isSupported())
					{
						$dbtype = 'mysqli';
					}
				}

				$options = [
					'driver'   => $dbtype,
					'host'     => $conf->get('host'),
					'user'     => $conf->get('user'),
					'password' => $conf->get('password'),
					'database' => $conf->get('db'),
					'prefix'   => $conf->get('dbprefix'),
				];

				try
				{
					$db = DatabaseDriver::getInstance($options);
				}
				catch (\RuntimeException $e)
				{
					if (!headers_sent())
					{
						header('HTTP/1.1 500 Internal Server Error');
					}

					jexit('Database Error: ' . $e->getMessage());
				}

				$db->setDispatcher($container->get(DispatcherInterface::class));

				return $db;
			},
			true
		);
	}
}
