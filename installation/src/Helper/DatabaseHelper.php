<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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

			// Get a database object.
			$db = DatabaseDriver::getInstance($options);
		}

		return $db;
	}
}
