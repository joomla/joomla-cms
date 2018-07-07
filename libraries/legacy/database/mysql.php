<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseMysql is deprecated, use JDatabaseDriverMysql instead.', JLog::WARNING, 'deprecated');

/**
 * MySQL database driver
 *
 * @link        http://dev.mysql.com/doc/
 * @since       1.5
 * @deprecated  3.0 Use JDatabaseDriverMysql instead.
 */
class JDatabaseMysql extends JDatabaseDriverMysql
{
}
