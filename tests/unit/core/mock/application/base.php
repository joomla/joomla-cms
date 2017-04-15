<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationBase.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplicationBase
{
	/**
	 * Gets the methods of the JApplicationBase object.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public static function getMethods()
	{
		return array(
			'close',
			'getIdentity',
			'registerEvent',
			'triggerEvent',
			'loadDispatcher',
			'loadIdentity',
			'getDispatcher',
		);
	}

	/**
	 * Adds mock objects for some methods.
	 *
	 * @param  TestCase                                 $test        A test object.
	 * @param  PHPUnit_Framework_MockObject_MockObject  $mockObject  The mock object.
	 * @param  array                                    $options     A set of options to configure the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject  The object with the behaviours added
	 *
	 * @since   3.4
	 */
	public static function addBehaviours($test, $mockObject, $options)
	{
		// Mock a call to JApplicationBase::getDispatcher().
		if (isset($options['dispatcher']))
		{
			$mockObject->expects($test->any())->method('getDispatcher')->willReturn($options['dispatcher']);
		}
		else
		{
			$mockObject->expects($test->any())->method('getDispatcher')->willReturn(TestMockDispatcher::create($test));
		}

		$test->assignMockReturns(
			$mockObject,
			array('close' => true)
		);

		return $mockObject;
	}

	/**
	 * Creates and instance of the mock JApplicationBase object.
	 *
	 * @param   TestCase  $test     A test object.
	 * @param   array     $options  A set of options to configure the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   11.3
	 */
	public static function create($test, $options = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationBase.
		$methods = self::getMethods();

		// Build the mock object & allow Call to original constructor
		$mockObject = $test->getMockBuilder('JApplicationBase')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->getMock();

		$mockObject = self::addBehaviours($test, $mockObject, $options);

		return $mockObject;
	}
}
