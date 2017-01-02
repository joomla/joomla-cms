<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JApplicationWeb.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockApplicationWeb extends TestMockApplicationBase
{
	/**
	 * Mock storage for the response body.
	 *
	 * @var    array
	 * @since  12.2
	 */
	public static $body = array();

	/**
	 * Mock storage for the response headers.
	 *
	 * @var    array
	 * @since  3.2
	 */
	public static $headers = array();

	/**
	 * Mock storage for the response cache status.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	public static $cachable = false;

	/**
	 * Gets the methods of the JApplicationWeb object.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public static function getMethods()
	{
		// Collect all the relevant methods in JApplicationWeb (work in progress).
		$methods = array(
			'allowCache',
			'appendBody',
			'clearHeaders',
			'execute',
			'get',
			'getBody',
			'getDocument',
			'getHeaders',
			'getLanguage',
			'getSession',
			'loadConfiguration',
			'loadDocument',
			'loadLanguage',
			'loadSession',
			'prependBody',
			'redirect',
			'sendHeaders',
			'set',
			'setBody',
			'setHeader',
			'setSession',
		);

		return array_merge($methods, parent::getMethods());
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
		// Mock calls to JApplicationWeb::getDocument().
		$mockObject->expects($test->any())->method('getDocument')->willReturn(TestMockDocument::create($test));

		// Mock calls to JApplicationWeb::getLanguage().
		$mockObject->expects($test->any())->method('getLanguage')->willReturn(TestMockLanguage::create($test));

		// Mock a call to JApplicationWeb::getSession().
		if (isset($options['session']))
		{
			$mockObject->expects($test->any())->method('getSession')->willReturn($options['session']);
		}
		else
		{
			$mockObject->expects($test->any())->method('getSession')->willReturn(TestMockSession::create($test));
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
		static::$cachable = false;

		return parent::addBehaviours($test, $mockObject, $options);
	}

	/**
	 * Creates and instance of the mock JApplicationWeb object.
	 *
	 * The test can implement the following overrides:
	 * - mockAppendBody
	 * - mockGetBody
	 * - mockPrepentBody
	 * - mockSetBody
	 * - mockGetHeaders
	 * - mockSetHeaders
	 * - mockAllowCache
	 *
	 * If any *Body methods are implemented in the test class, all should be implemented otherwise behaviour will be unreliable.
	 *
	 * @param   TestCase  $test         A test object.
	 * @param   array     $options      A set of options to configure the mock.
	 * @param   array     $constructor  An array containing constructor arguments to inject into the mock.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   11.3
	 */
	public static function create($test, $options = array(), $constructor = array())
	{
		// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Create the mock.
		$mockObject = $test->getMockForAbstractClass(
			// Original class name.
			'JApplicationWeb',
			// Constructor arguments.
			$constructor,
			// Mock class name.
			'',
			// Call original constructor.
			true,
			// Call original clone.
			true,
			// Call autoload.
			true,
			// Mocked methods.
			self::getMethods()
		);

		return self::addBehaviours($test, $mockObject, $options);
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
		static::$body[] = (string) $content;
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
		return $asArray ? static::$body : implode((array) static::$body);
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
		array_unshift(static::$body, (string) $content);
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
		static::$body = array($content);
	}

	/**
	 * Mock JApplicationWeb->getHeaders method.
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public static function mockGetHeaders()
	{
		return static::$headers;
	}

	/**
	 * Mock JApplicationWeb->setHeader method.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function mockSetHeader($name, $value, $replace = false)
	{
		// Sanitize the input values.
		$name = (string) $name;
		$value = (string) $value;

		// If the replace flag is set, unset all known headers with the given name.
		if ($replace)
		{
			foreach (static::$headers as $key => $header)
			{
				if ($name == $header['name'])
				{
					unset(static::$headers[$key]);
				}
			}

			// Clean up the array as unsetting nested arrays leaves some junk.
			static::$headers = array_values(static::$headers);
		}

		// Add the header to the internal array.
		static::$headers[] = array('name' => $name, 'value' => $value);
	}

	/**
	 * Mock JApplicationWeb->clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function mockClearHeaders()
	{
		static::$headers = array();
	}

	/**
	 * Mock JApplicationWeb->allowCache method.
	 *
	 * @param   boolean  $allow  True to allow browser caching.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function mockAllowCache($allow = null)
	{
		if ($allow !== null)
		{
			static::$cachable = (bool) $allow;
		}

		return static::$cachable;
	}
}
