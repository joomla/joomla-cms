<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JDatabase.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JApplicationGlobalMock
{
	/**
	 * Creates and instance of the mock JApplication object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JApplication (work in progress).
		$methods = array(
			'get',
			'getCfg',
			'getRouter',
			'getTemplate',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JApplication',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		return $mockObject;
	}
}