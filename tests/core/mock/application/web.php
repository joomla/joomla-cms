<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationWeb.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplicationWeb
{
	/**
	 * Mock storage for the response body.
	 *
	 * @var    array
	 * @since  12.2
	 */
	public static $body = array();

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
	 * @since   11.3
	 */
	public static function create($test, $options = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationWeb (work in progress).
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
			'getSession',
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
			'JApplicationWeb',
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
			)
		);

		// Reset the body storage.
		self::$body = array();

		return $mockObject;
	}

	/**
	 * Mock JApplicationWeb->appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public static function mockAppendBody($content)
	{
		array_push(self::$body, (string) $content);
	}

	/**
	 * Mock JApplicationWeb->getBody method.
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public static function mockGetBody($asArray = false)
	{
		return $asArray ? self::$body : implode((array) self::$body);
	}

	/**
	 * Mock JApplicationWeb->appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public static function mockPrependBody($content)
	{
		array_unshift(self::$body, (string) $content);
	}

	/**
	 * Mock JApplicationWeb->setBody method.
	 *
	 * @param   string  $content  The body of the response.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public static function mockSetBody($content)
	{
		self::$body = array($content);
	}
}
