<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationBase.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplicationBase
{
	/**
	 * Creates and instance of the mock JApplicationBase object.
	 *
	 * @param   object  $test     A test object.
	 * @param   array   $options  A set of options to configure the mock.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test, $options = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationBase (work in progress).
		$methods = array(
			'close',
			'getIdentity',
			'loadDispatcher',
			'loadDocument',
			'loadIdentity',
			'registerEvent',
			'triggerEvent',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JApplicationBase',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		return $mockObject;
	}
}
