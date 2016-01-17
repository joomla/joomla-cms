<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseMysql is deprecated, use JDatabaseDriverMysql instead.', JLog::WARNING, 'deprecated');

/**
 * MySQL database driver
 *
 * @see         http://dev.mysql.com/doc/
 * @since       11.1
 * @deprecated  13.1 (Platform) & 4.0 (CMS) - Use JDatabaseDriverMysql instead.
 */
class JDatabaseMysql extends JDatabaseDriverMysql
{
}
