<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JConfig.
 *
 * @package  Joomla.Test
 * @since    3.0.0
 */
class TestMockConfig
{
	/**
	 * Creates and instance of the mock JApplication object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   1.7.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JConfig.
		$methods = array(
			'get',
			'set'
		);

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JConfig')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		return $mockObject;
	}
}
