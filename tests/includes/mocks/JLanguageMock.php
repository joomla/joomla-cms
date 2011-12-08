<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license     GNU General Public License
 */

/**
 * Mock class for JLanguage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       11.3
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
	public function mock_($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		return $string;
	}
}
