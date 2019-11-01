<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Helper;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

/**
 * Joomla Installation Database Helper Class.
 *
 * @since  1.6
 */
abstract class DatabaseHelper
{
	/**
	 * Method to get a database driver.
	 *
	 * @param   string   $driver    The database driver to use.
	 * @param   string   $host      The hostname to connect on.
	 * @param   string   $user      The user name to connect with.
	 * @param   string   $password  The password to use for connection authentication.
	 * @param   string   $database  The database to use.
	 * @param   string   $prefix    The table prefix to use.
	 * @param   boolean  $select    True if the database should be selected.
	 * @param   array    $ssl       Database TLS connection options.
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   1.6
	 */
	public static function getDbo($driver, $host, $user, $password, $database, $prefix, $select = true, array $ssl = [])
	{
		static $db;

		if (!$db)
		{
			// Build the connection options array.
			$options = [
				'driver'   => $driver,
				'host'     => $host,
				'user'     => $user,
				'password' => $password,
				'database' => $database,
				'prefix'   => $prefix,
				'select'   => $select,
			];

			if (isset($ssl['dbencryption']) === true && (int) $ssl['dbencryption'] !== 0)
			{
				$options['ssl'] = [
					'enable'             => true,
					'verify_server_cert' => (bool) $ssl['dbsslverifyservercert'],
				];

				foreach (['cipher', 'ca', 'capath', 'key', 'cert'] as $value)
				{
					$confVal = trim($ssl['dbssl' . $value]);

					if ($confVal !== '')
					{
						$options['ssl'][$value] = $confVal;
					}
				}
			}

			// Enable utf8mb4 connections for mysql adapters
			if (strtolower($driver) === 'mysqli')
			{
				$options['utf8mb4'] = true;
			}

			if (strtolower($driver) === 'mysql')
			{
				$options['charset'] = 'utf8mb4';
			}

			// Get a database object.
			$db = DatabaseDriver::getInstance($options);
		}

		return $db;
	}

	/**
	 * Convert encryption options to array.
	 *
	 * @param   \stdClass  $options  The session options
	 *
	 * @return  array  The encryption settings
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getEncryptionSettings($options)
	{
		return [
			'dbencryption'          => $options->db_encryption,
			'dbsslverifyservercert' => $options->db_sslverifyservercert,
			'dbsslkey'              => $options->db_sslkey,
			'dbsslcert'             => $options->db_sslcert,
			'dbsslca'               => $options->db_sslca,
			'dbsslcapath'           => $options->db_sslcapath,
			'dbsslcipher'           => $options->db_sslcipher,
		];
	}
}
