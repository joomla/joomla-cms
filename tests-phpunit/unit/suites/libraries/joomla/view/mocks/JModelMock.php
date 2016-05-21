<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JModel.
 *
 * @package  Joomla.UnitTest
 * @since    12.1
 */
class JModelMock
{
	/**
	 * Creates and instance of the mock JModel object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JModel.
		$methods = array(
			'getState',
			'loadState',
			'setState',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JModel',
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
