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
JLog::add('JDatabaseException is deprecated, use SPL Exceptions instead.', JLog::NOTICE, 'deprecated');

/**
 * Exception class definition for the Database subpackage.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.1
 * @deprecated  12.3 Use semantic exceptions instead
 */
class JDatabaseException extends RuntimeException
{
}
