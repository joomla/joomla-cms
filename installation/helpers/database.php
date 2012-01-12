<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Joomla Installation Database Helper Class.
 *
 * @static
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationHelperDatabase
{
	/**
	 * Method to get a JDatabase object.
	 *
	 * @param	string	$driver		The database driver to use.
	 * @param	string	$host		The hostname to connect on.
	 * @param	string	$user		The user name to connect with.
	 * @param	string	$password	The password to use for connection authentication.
	 * @param	string	$database	The database to use.
	 * @param	string	$prefix		The table prefix to use.
	 * @param	boolean $select		True if the database should be selected.
	 *
	 * @return	mixed	JDatabase object on success, JException on error.
	 * @since	1.0
	 */
	public static function & getDBO($driver, $host, $user, $password, $database, $prefix, $select = true)
	{
		static $db;

		if (!$db) {
			// Build the connection options array.
			$options = array (
				'driver' => $driver,
				'host' => $host,
				'user' => $user,
				'password' => $password,
				'database' => $database,
				'prefix' => $prefix,
				'select' => $select
			);

			// Get a database object.
			$db = JDatabase::getInstance($options);
		}

		return $db;
	}
}
