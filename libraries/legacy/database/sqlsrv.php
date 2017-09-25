<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseSqlsrv is deprecated, use JDatabaseDriverSqlsrv instead.', JLog::WARNING, 'deprecated');

/**
 * SQL Server database driver
 *
 * @link        https://msdn.microsoft.com/en-us/library/cc296152(SQL.90).aspx
 * @since       1.7
 * @deprecated  3.0 Use JDatabaseDriverSqlsrv instead.
 */
class JDatabaseSqlsrv extends JDatabaseDriverSqlsrv
{
}
