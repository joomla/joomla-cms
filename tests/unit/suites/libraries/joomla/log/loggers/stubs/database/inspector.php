<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JLogLoggerDatabaseInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       1.7.0
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
