<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
