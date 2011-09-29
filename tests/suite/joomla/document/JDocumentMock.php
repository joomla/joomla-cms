<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license     GNU General Public License
 */

/**
 * Mock class for JDocument.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Document
 * @since       11.3
 */
class JDocumentGlobalMock
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
			'parse',
			'render',
			'test',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JDocument',
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
				'parse' => $mockObject,
				// An additional 'test' method for confirming this object is successfully mocked.
				'test' => 'ok'
			)
		);

		return $mockObject;
	}
}