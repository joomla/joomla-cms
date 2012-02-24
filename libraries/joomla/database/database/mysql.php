<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Deprecation warning.
JLog::add('JDatabaseMysql is deprecated, use JDatabaseDriverMysql instead.', JLog::NOTICE, 'deprecated');

/**
 * MySQL database driver
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @see         http://dev.mysql.com/doc/
 * @since       11.1
 * @deprecated  13.1
 */
class JDatabaseMysql extends JDatabaseDriverMysql
{
}
