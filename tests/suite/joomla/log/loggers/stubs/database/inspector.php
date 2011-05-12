<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage  Log
 */
class JLoggerDatabaseInspector extends JLoggerDatabase
{
	public $driver;
	public $host;
	public $user;
	public $password;
	public $database;
	public $table;
	public $dbo;

	public function connect()
	{
		parent::connect();
	}
}