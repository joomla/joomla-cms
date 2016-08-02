<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Sample callbacks for the JLog package.
 */

/**
 * Helper class for JLogLoggerCallbackTest
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @since       12.2
 */
class JLogLoggerCallbackTestHelper
{
	public static $lastEntry;

	/**
	 * Function for testing JLogLoggerCallback with a static method
	 *
	 * @param   JLogEntry  $entry  A log entry to be processed.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public static function callback01(JLogEntry $entry)
	{
		self::$lastEntry = $entry;
	}

	/**
	 * Function for testing JLogLoggerCallback with an object method
	 *
	 * @param   JLogEntry  $entry  A log entry to be processed.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function callback02(JLogEntry $entry)
	{

	}
}

/**
 * Function for testing JLogLoggerCallback
 *
 * @param   JLogEntry  $entry  A log entry to be processed.
 *
 * @return  null
 *
 * @since   12.2
 */
function jLogLoggerCallbackTestHelperFunction(JLogEntry $entry)
{

}
