<?php
/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Service\Provider;

\defined('JPATH_PLATFORM') or die;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Mysql\MysqlDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Service provider for the application's database dependency
 *
 * @since  4.0.0
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
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->alias('db', DatabaseInterface::class)
			->alias('DatabaseDriver', DatabaseInterface::class)
			->alias(DatabaseDriver::class, DatabaseInterface::class)
			->share(
				DatabaseInterface::class,
				function (Container $container)
				{
					/** @var $conf \Joomla\Registry\Registry */
					$conf = $container->get('config');

					$dbtype = $conf->get('dbtype', null);

					// Ensure we have a db type, else set a sane, assumed, default.
					if (null === $dbtype)
					{
						$dbtype = 'mysqli';
					}

					// Normalise.
					$dbtype = strtolower($dbtype);

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
					if ($dbtype === 'pdomysql')
					{
						$dbtype = 'mysql';
					}

					if ($dbtype === 'mysql' && !MysqlDriver::isSupported())
					{
						$dbtype = 'mysqli';
					}

					/*
					 * Joomla! 4.0 removes support for the `ext/pgsql` PHP extension.  To help with the migration, we will migrate the configuration
					 * to the PDO PostgreSQL driver regardless of if the environment supports it.  Instead of getting a "driver not found" type of
					 * error, this will instead force the API to report that the driver is not supported.
					 */
					if ($dbtype === 'postgresql')
					{
						$dbtype = 'pgsql';
					}

					$options = [
						'driver'   => $dbtype,
						'host'     => $conf->get('host'),
						'user'     => $conf->get('user'),
						'password' => $conf->get('password'),
						'database' => $conf->get('db'),
						'prefix'   => $conf->get('dbprefix'),
					];

					if ((int) $conf->get('dbencryption') !== 0)
					{
						$options['ssl'] = [
							'enable'             => true,
							'verify_server_cert' => (bool) $conf->get('dbsslverifyservercert'),
						];

						foreach (['cipher', 'ca', 'key', 'cert'] as $value)
						{
							$confVal = trim($conf->get('dbssl' . $value, ''));

							if ($confVal !== '')
							{
								$options['ssl'][$value] = $confVal;
							}
						}
					}

					// Enable utf8mb4 connections for mysql adapters
					if ($dbtype === 'mysqli')
					{
						$options['utf8mb4'] = true;
					}

					if ($dbtype === 'mysql')
					{
						$options['charset'] = 'utf8mb4';
					}

					if (JDEBUG)
					{
						$options['monitor'] = new \Joomla\Database\Monitor\DebugMonitor;
					}

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
