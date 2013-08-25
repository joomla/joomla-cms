<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.3
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
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.1
 */
class JApplicationHelperTest extends TestCase
{
	/**
	 * Test JApplicationHelper::getComponentName
	 *
	 * @return  void
	 *
	 * @todo    Implement testGetComponentName().
	 */
	public function testGetComponentName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplicationHelper::getClientInfo
	 *
	 * @return  void
	 *
	 * @todo    Implement testGetClientInfo().
	 */
	public function testGetClientInfo()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
