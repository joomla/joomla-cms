<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseMysqli is deprecated, use JDatabaseDriverMysqli instead.', JLog::WARNING, 'deprecated');

/**
 * MySQLi database driver
 *
 * @package     Joomla.Legacy
 * @subpackage  Database
 * @see         http://php.net/manual/en/book.mysqli.php
 * @since       11.1
 * @deprecated  13.1 Use JDatabaseDriverMysqli instead.
 */
class JDatabaseMysqli extends JDatabaseDriverMysqli
{
}
