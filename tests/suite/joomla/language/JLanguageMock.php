<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JLanguage.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JLanguageGlobalMock
{
	/**
	 * Creates and instance of the mock JLanguage object.
	 *
	 * @param   object  $test   A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'getTag',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JLanguage',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		// Mock selected methods.
		$test->assignMockReturns(
			$mockObject, array(
				'getTag' => 'en-GB'
			)
		);

		return $mockObject;
	}
}