<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseSqlsrv is deprecated, use JDatabaseDriverSqlsrv instead.', JLog::WARNING, 'deprecated');

/**
 * SQL Server database driver
 *
 * @package     Joomla.Legacy
 * @subpackage  Database
 * @see         http://msdn.microsoft.com/en-us/library/cc296152(SQL.90).aspx
 * @since       11.1
 * @deprecated  13.1 (Platform) & 4.0 (CMS) - Use JDatabaseDriverSqlsrv instead.
 */
class JDatabaseSqlsrv extends JDatabaseDriverSqlsrv
{
}
