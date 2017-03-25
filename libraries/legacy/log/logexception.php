<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JLog::add('LogException is deprecated, use SPL Exceptions instead.', JLog::WARNING, 'deprecated');

/**
 * Exception class definition for the Log subpackage.
 *
 * @since       1.7
 * @deprecated  2.5.5 Use semantic exceptions instead
 */
class LogException extends RuntimeException
{
}
