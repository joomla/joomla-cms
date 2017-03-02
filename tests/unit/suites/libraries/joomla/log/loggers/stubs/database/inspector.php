<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JLogLoggerDatabaseInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogLoggerDatabaseInspector extends JLogLoggerDatabase
{
	public $driver;

	public $host;

	public $user;

	public $password;

	public $database;

	public $table;

	public $db;

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function connect()
	{
		parent::connect();
	}
}
