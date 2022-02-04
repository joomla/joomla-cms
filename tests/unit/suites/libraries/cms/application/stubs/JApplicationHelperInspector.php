<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JApplicationHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.4
 */
class JApplicationHelperInspector extends JApplicationHelper
{
	/**
	 * Method to get the current application data
	 *
	 * @return  array  The array of application data objects.
	 *
	 * @since   3.4
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
	 * @return  void
	 *
	 * @since   3.4
	 */
	public static function set($apps)
	{
		self::$_clients = $apps;
	}
}
