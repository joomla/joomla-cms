<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	 * Creates and instance of the mock JApplicationWeb object.
	 *
	 * The test can implement the following overrides:
	 * - mockAppendBody
	 * - mockGetBody
	 * - mockPrepentBody
	 * - mockSetBody
	 *
	 * If any *Body methods are implemented in the test class, all should be implemented otherwise behaviour will be unreliable.
	 *
	 * @param   TestCase  $test     A test object.
	 * @param   array     $options  A set of options to configure the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.2
	 */
	public static function create($test, $options = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationCms (work in progress).
		$methods = array(
			'allowCache',
			'appendBody',
			'clearHeaders',
			'close',
			'execute',
			'get',
			'getBody',
			'getDocument',
			'getHeaders',
			'getIdentity',
			'getLanguage',
			'getMenu',
			'getPathway',
			'getSession',
			'getTemplate',
			'initialiseApp',
			'isAdmin',
			'loadConfiguration',
			'loadDispatcher',
			'loadDocument',
			'loadIdentity',
			'loadLanguage',
			'loadSession',
			'prependBody',
			'redirect',
			'registerEvent',
			'sendHeaders',
			'set',
			'setBody',
			'setHeader',
			'triggerEvent',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JApplicationCms',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		// Mock calls to JApplicationWeb::getDocument().
		$mockObject->expects($test->any())->method('getDocument')->will($test->returnValue(TestMockDocument::create($test)));

		// Mock calls to JApplicationWeb::getLanguage().
		$mockObject->expects($test->any())->method('getLanguage')->will($test->returnValue(TestMockLanguage::create($test)));

		// Mock a call to JApplicationWeb::getSession().
		if (isset($options['session']))
		{
			$mockObject->expects($test->any())->method('getSession')->will($test->returnValue($options['session']));
		}
		else
		{
			$mockObject->expects($test->any())->method('getSession')->will($test->returnValue(TestMockSession::create($test)));
		}

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'appendBody' => array((is_callable(array($test, 'mockAppendBody')) ? $test : get_called_class()), 'mockAppendBody'),
				'getBody' => array((is_callable(array($test, 'mockGetBody')) ? $test : get_called_class()), 'mockGetBody'),
				'prependBody' => array((is_callable(array($test, 'mockPrependBody')) ? $test : get_called_class()), 'mockPrependBody'),
				'setBody' => array((is_callable(array($test, 'mockSetBody')) ? $test : get_called_class()), 'mockSetBody'),
				'getHeaders' => array((is_callable(array($test, 'mockGetHeaders')) ? $test : get_called_class()), 'mockGetHeaders'),
				'setHeader' => array((is_callable(array($test, 'mockSetHeader')) ? $test : get_called_class()), 'mockSetHeader'),
				'clearHeaders' => array((is_callable(array($test, 'mockClearHeaders')) ? $test : get_called_class()), 'mockClearHeaders'),
				'allowCache' => array((is_callable(array($test, 'mockAllowCache')) ? $test : get_called_class()), 'mockAllowCache'),
			)
		);

		// Reset the body storage.
		static::$body = array();

		// Reset the headers storage.
		static::$headers = array();

		// Reset the cache storage.
		static::$cachable = array();

		return $mockObject;
	}
}
