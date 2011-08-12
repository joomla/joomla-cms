<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JConfig.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JConfigGlobalMock
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
		// Collect all the relevant methods in JConfig.
		$methods = array(
			'get',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JConfig',
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