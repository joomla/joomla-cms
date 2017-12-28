<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * SQL Server database driver
 *
 * @link   https://azure.microsoft.com/en-us/documentation/services/sql-database/
 * @since  12.1
 */
class JDatabaseDriverSqlazure extends JDatabaseDriverSqlsrv
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  12.1
	 */
	public $name = 'sqlazure';
}
