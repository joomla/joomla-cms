<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   © 2012 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseSqlazure is deprecated, use JDatabaseDriverSqlazure instead.', JLog::WARNING, 'deprecated');

/**
 * SQL Server database driver
 *
 * @link        https://azure.microsoft.com/en-us/documentation/services/sql-database/
 * @since       1.7
 * @deprecated  3.0 Use JDatabaseDriverSqlazure instead.
 */
class JDatabaseSqlazure extends JDatabaseDriverSqlazure
{
}
