<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseSqlazure is deprecated, use JDatabaseDriverSqlazure instead.', JLog::WARNING, 'deprecated');

/**
 * SQL Server database driver
 *
 * @see         https://azure.microsoft.com/en-us/documentation/services/sql-database/
 * @since       1.7
 * @deprecated  3.0 Use JDatabaseDriverSqlazure instead.
 */
class JDatabaseSqlazure extends JDatabaseDriverSqlazure
{
}
