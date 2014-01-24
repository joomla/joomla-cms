<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('JDatabaseException is deprecated, use SPL Exceptions instead.', JLog::WARNING, 'deprecated');

/**
 * Exception class definition for the Database subpackage.
 *
 * @package     Joomla.Legacy
 * @subpackage  Database
 * @since       11.1
 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use semantic exceptions instead
 */
class JDatabaseException extends RuntimeException
{
}
