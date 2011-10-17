<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

require dirname(__FILE__).'/sqlsrv.php';

JLoader::register('JDatabaseQuerySQLAzure', dirname(__FILE__) . '/sqlazurequery.php');

/**
 * SQL Server database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://msdn.microsoft.com/en-us/library/ee336279.aspx
 * @since       11.1
 */
class JDatabaseSQLAzure extends JDatabaseSQLSrv
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = 'sqlzure';
}
