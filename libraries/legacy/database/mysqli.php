<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseMysqli is deprecated, use JDatabaseDriverMysqli instead.', JLog::WARNING, 'deprecated');

/**
 * MySQLi database driver
 *
 * @link        https://www.php.net/manual/en/book.mysqli.php
 * @since       1.5
 * @deprecated  3.0 Use JDatabaseDriverMysqli instead.
 */
class JDatabaseMysqli extends JDatabaseDriverMysqli
{
}
