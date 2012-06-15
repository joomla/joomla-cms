<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Sample callbacks for the JLog package.
 */

/**
 * Helper class for JLoggerCallbackTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       12.1
 */
class JLoggerCallbackTestHelper
{
	public static $lastEntry;

	/**
	 * Function for testing JLoggerCallback with a static method
	 *
	 * @param   JLogEntry  $entry  A log entry to be processed.
	 *
	 * @return  null
	 */
	public static function callback01(JLogEntry $entry)
	{
		self::$lastEntry = $entry;
	}

	/**
	 * Function for testing JLoggerCallback with an object method
	 *
	 * @param   JLogEntry  $entry  A log entry to be processed.
	 *
	 * @return  null
	 */
	public function callback02(JLogEntry $entry)
	{

	}
}

/**
 * Function for testing JLoggerCallback
 *
 * @param   JLogEntry  $entry  A log entry to be processed.
 *
 * @return  null
 */
function jLoggerCallbackTestHelperFunction(JLogEntry $entry)
{

}
