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
	 *
	 * @return  DatabaseInterface
	 *
	 * @since   1.6
	 */
	public static function getDbo($driver, $host, $user, $password, $database, $prefix, $select = true)
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
	 * Generates random prefix string for DB table
	 *
	 * @param   int  $size  Size of the Prefix
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	public static function getPrefix(int $size = 15)
	{
		// Create the random prefix.
		$prefix  = '';
		$chars   = range('a', 'z');
		$numbers = range(0, 9);

		// We want the first character to be a random letter.
		shuffle($chars);
		$prefix .= $chars[0];

		// Next we combine the numbers and characters to get the other characters.
		$symbols = array_merge($numbers, $chars);
		shuffle($symbols);

		for ($i = 1, $j = $size - 1; $i < $j; ++$i)
		{
			$prefix .= $symbols[$i];
		}

		// Add in the underscore.
		$prefix .= '_';

		return $prefix;
	}
}
