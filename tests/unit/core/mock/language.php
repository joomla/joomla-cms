<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JLanguage.
 *
 * @package  Joomla.Test
 * @since    3.0.0
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
	 * @since   1.7.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'_',
			'getInstance',
			'getTag',
			'isRTL',
			'test',
		);

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JLanguage')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		// Mock selected methods.
		$test->assignMockReturns(
			$mockObject, array(
				'getInstance' => $mockObject,
				'getTag' => 'en-GB',
				'isRTL' => false,
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
	 * @since  1.7.3
	 */
	public static function mock_($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		return $string;
	}
}
