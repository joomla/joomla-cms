<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplication.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplication
{
	/**
	 * Creates and instance of the mock JApplication object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JApplication (work in progress).
		$methods = array(
			'get',
			'getCfg',
			'getIdentity',
			'getRouter',
			'getTemplate',
			'getMenu',
			'getLanguage'
		);

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JApplication')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		$menu = TestMockMenu::create($test);
		$mockObject->expects($test->any())
				->method('getMenu')
				->will($test->returnValue($menu));

		$language = TestMockLanguage::create($test);
		$mockObject->expects($test->any())
				->method('getLanguage')
				->will($test->returnValue($language));

		$mockObject->input = new JInput;

		return $mockObject;
	}
}
