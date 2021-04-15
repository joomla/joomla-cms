<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JDocument.
 *
 * @package  Joomla.Test
 * @since    3.0.0
 */
class TestMockDocument
{
	/**
	 * Creates and instance of the mock JLanguage object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   1.7.3
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
