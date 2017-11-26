<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JDocument.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockDocument
{
	/**
	 * Creates and instance of the mock JLanguage object.
	 *
	 * @param   \PHPUnit\Framework\TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
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
		$mockObject = $test->getMockBuilder('JDocument')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

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
