<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JLanguage.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockLanguage
{
	/**
	 * Creates and instance of the mock JLanguage object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'_',
			'getInstance',
			'getTag',
			'test',
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
				'getInstance' => $mockObject,
				'getTag' => 'en-GB',
				// An additional 'test' method for confirming this object is successfully mocked.
				'test' => 'ok',
			)
		);

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'_' => array(get_called_class(), 'mock_'),
			)
		);

		return $mockObject;
	}

	/**
	 * Callback for the mock JLanguage::_ method.
	 *
	 * @param   string   $string                The string to translate
	 * @param   boolean  $jsSafe                Make the result javascript safe
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n
	 *
	 * @return void
	 *
	 * @since  11.3
	 */
	public static function mock_($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		return $string;
	}
}
