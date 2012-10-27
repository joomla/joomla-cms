<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 */
class JApplicationHelperInspector extends JApplicationHelper
{
	/**
	* Method to get the current application data
	*
	* @return array The array of application data objects.
	*/
	public static function get()
	{
		return self::$_clients;
	}

	/**
	* Set the application data.
	*
	* @param   string  $apps  The app to set.
	*
	* @return void
	*/
	public static function set($apps)
	{
		self::$_clients = $apps;
	}
}

/**
 * Test class for JApplicationHelper.
 *
 * @since  11.1
 */
class JApplicationHelperTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @todo Implement testGetComponentName().
	 */
	public function testGetComponentName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testGetClientInfo().
	 */
	public function testGetClientInfo()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
