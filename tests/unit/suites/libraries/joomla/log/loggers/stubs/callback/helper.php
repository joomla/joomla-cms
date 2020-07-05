<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
 * @since       3.0.1
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
	 * @since   3.0.1
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
	 * @since   3.0.1
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
 * @since   3.0.1
 */
function jLogLoggerCallbackTestHelperFunction(JLogEntry $entry)
{

}
