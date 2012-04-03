<?php
/**
 * @package    Joomla.UnitTest
* @copyright  Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
* @license    GNU General Public License
*/

/**
 * Mock class for JController.
 *
 * @package  Joomla.UnitTest
 * @since    12.1
 */
class JControllerMock
{
	/**
	 * Creates and instance of the mock JController object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JController.
		$methods = array(
			'execute',
			'getApplication',
			'getInput',
			'serialize',
			'unserialize',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JControllerBase',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		// TODO  Mock the input.
		TestReflection::setValue($mockObject, 'input', new JInput);

		return $mockObject;
	}
}
