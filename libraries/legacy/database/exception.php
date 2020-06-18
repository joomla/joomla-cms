<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   © 2011 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseException is deprecated, use SPL Exceptions instead.', JLog::WARNING, 'deprecated');

/**
 * Exception class definition for the Database subpackage.
 *
 * @since       1.7
 * @deprecated  2.5.5
 */
class JDatabaseException extends RuntimeException
{
}
