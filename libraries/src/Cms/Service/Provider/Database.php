<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Service
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

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
		$container->alias('db', 'JDatabaseDriver')
			->alias('Joomla\Database\DatabaseInterface', 'JDatabaseDriver')
			->share(
			'JDatabaseDriver',
			function (Container $container)
			{
				$conf = \JFactory::getConfig();

				$options = array(
					'driver'   => $conf->get('dbtype'),
					'host'     => $conf->get('host'),
					'user'     => $conf->get('user'),
					'password' => $conf->get('password'),
					'database' => $conf->get('db'),
					'prefix'   => $conf->get('dbprefix')
				);

				try
				{
					$db = \JDatabaseDriver::getInstance($options);
				}
				catch (\RuntimeException $e)
				{
					if (!headers_sent())
					{
						header('HTTP/1.1 500 Internal Server Error');
					}

					jexit('Database Error: ' . $e->getMessage());
				}

				$db->setDebug((bool) $conf->get('debug', false));

				return $db;
			},
			true
		);
	}
}
