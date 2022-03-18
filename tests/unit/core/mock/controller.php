<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JController.
 *
 * @package  Joomla.Test
 * @since    3.0.0
 */
class TestMockController
{
	/**
	 * Creates and instance of the mock JController object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   3.0.0
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

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JControllerBase')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		// TODO  Mock the input.
		TestReflection::setValue($mockObject, 'input', new JInput);

		return $mockObject;
	}
}
