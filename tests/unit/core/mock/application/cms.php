<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationCms.
 *
 * @package  Joomla.Test
 * @since    3.2
 */
class TestMockApplicationCms extends TestMockApplicationWeb
{
	/**
	 * Gets the methods of the JApplicationCms object.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public static function getMethods()
	{
		// Collect all the relevant methods in JApplicationCms (work in progress).
		$methods = array(
			'getMenu',
			'getPathway',
			'getTemplate',
			'getLanguageFilter',
			'initialiseApp',
			'isClient',
			'isAdmin',
			'isSite',
			'getUserState',
			'getUserStateFromRequest',
			'setUserState',
		);

		return array_merge($methods, parent::getMethods());
	}

	/**
	 * Adds mock objects for some methods.
	 *
	 * @param   TestCase                                 $test        A test object.
	 * @param   PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param   array                                    $options     A set of options to configure the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject  The object with the behaviours added
	 *
	 * @since   3.4
	 */
	public static function addBehaviours($test, $mockObject, $options)
	{
		// Mock calls to JApplicationCms::getMenu();
		$mockObject->expects($test->any())->method('getMenu')->will($test->returnValue(TestMockMenu::create($test)));

		return parent::addBehaviours($test, $mockObject, $options);
	}

	/**
	 * Creates and instance of the mock JApplicationCms object.
	 *
	 * The test can implement the following overrides:
	 * - mockAppendBody
	 * - mockGetBody
	 * - mockPrepentBody
	 * - mockSetBody
	 *
	 * If any *Body methods are implemented in the test class, all should be implemented otherwise behaviour will be unreliable.
	 *
	 * @param   TestCase  $test         A test object.
	 * @param   array     $options      A set of options to configure the mock.
	 * @param   array     $constructor  An array containing constructor arguments to inject into the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.2
	 */
	public static function create($test, $options = array(), $constructor = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		$methods = self::getMethods();

		if (isset($options))
		{
			// Build the mock object & allow call to original constructor.
			$mockObject = $test->getMockBuilder('JApplicationCms')
						->setMethods($methods)
						->setConstructorArgs($constructor)
						->setMockClassName('')
						->getMock();
		}

		$mockObject = self::addBehaviours($test, $mockObject, $options);

		return $mockObject;
	}
}
