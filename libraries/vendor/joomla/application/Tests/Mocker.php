<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Tests;

use Joomla\Test\TestHelper;

/**
 * Class to mock the \Joomla\Application package.
 *
 * @since  1.0
 */
class Mocker
{
	/**
	 * @var    array
	 * @since  1.0
	 */
	public $body;

	/**
	 * @var    array
	 * @since  1.0
	 */
	public $config;

	/**
	 * @var    array
	 * @since  1.0
	 */
	public $headers;

	/**
	 * @var    \PHPUnit_Framework_TestCase
	 * @since  1.0
	 */
	private $test;

	/**
	 * Class contructor.
	 *
	 * @param   \PHPUnit_Framework_TestCase  $test  A test class.
	 *
	 * @since   1.0
	 */
	public function __construct(\PHPUnit_Framework_TestCase $test)
	{
		$this->body = array();
		$this->headers = array();
		$this->test = $test;
	}

	/**
	 * Creates an instance of a mock Joomla\Application\AbstractApplication object.
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function createMockBase()
	{
		// Collect all the relevant methods in JApplicationBase (work in progress).
		$methods = array(
			'close',
			'doExecute',
			'execute',
			'fetchConfigurationData',
			'get',
			'getLogger',
			'hasLogger',
			'initialise',
			'set',
			'setConfiguration',
			'setLogger',
		);

		// Create the mock.
		$mockObject = $this->test->getMock(
			'Joomla\\Application\\AbstractApplication',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		return $mockObject;
	}

	/**
	 * Creates an instance of the mock Joomla\Application\AbstractCliApplication object.
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function createMockCli()
	{
		// Collect all the relevant methods in JApplicationBase (work in progress).
		$methods = array(
			'close',
			'doExecute',
			'execute',
			'fetchConfigurationData',
			'get',
			'getLogger',
			'hasLogger',
			'in',
			'initialise',
			'out',
			'set',
			'setConfiguration',
			'setLogger',
		);

		// Create the mock.
		$mockObject = $this->test->getMock(
			'Joomla\\Application\\AbstractCliApplication',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		return $mockObject;
	}

	/**
	 * Creates an instance of the mock Joomla\Application\AbstractWebApplication object.
	 *
	 * @param   array  $options  A associative array of options to configure the mock.
	 *                           session => a mock session
	 *                           class => an alternative class to mock (used for hybird legacy applications)
	 *                           methods => an array of additional methods to mock
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function createMockWeb($options = array())
	{
			// Set expected server variables.
		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		// Collect all the relevant methods in JApplicationBase (work in progress).
		$methods = array(
			'allowCache',
			'appendBody',
			'checkConnectionAlive',
			'checkHeadersSent',
			'close',
			'clearHeaders',
			'compress',
			'detectRequestUri',
			'doExecute',
			'execute',
			'fetchConfigurationData',
			'get',
			'getBody',
			'getHeaders',
			'getLogger',
			'getSession',
			'hasLogger',
			'header',
			'initialise',
			'isSSLConnection',
			'loadSystemUris',
			'prependBody',
			'redirect',
			'respond',
			'sendHeaders',
			'set',
			'setBody',
			'setConfiguration',
			'setHeader',
			'setLogger',
			'setSession',
		);

		// Add custom methods if required for derived application classes.
		if (isset($options['methods']) && is_array($options['methods']))
		{
			$methods = array_merge($methods, $options['methods']);
		}

		// Create the mock.
		$mockObject = $this->test->getMock(
			isset($options['class']) ? $options['class'] : 'Joomla\\Application\\AbstractWebApplication',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			true
		);

		// Mock a call to JApplicationWeb::getSession().
		if (isset($options['session']))
		{
			$mockObject->expects($this->test->any())->method('getSession')->will($this->test->returnValue($options['session']));
		}

		Helper::assignMockCallbacks(
			$mockObject,
			$this->test,
			array(
				'appendBody' => array((is_callable(array($this->test, 'mockWebAppendBody')) ? $this->test : $this), 'mockWebAppendBody'),
				'get' => array((is_callable(array($this->test, 'mockWebGet')) ? $this->test : $this), 'mockWebGet'),
				'getBody' => array((is_callable(array($this->test, 'mockWebGetBody')) ? $this->test : $this), 'mockWebGetBody'),
				'getHeaders' => array((is_callable(array($this->test, 'mockWebGetHeaders')) ? $this->test : $this), 'mockWebGetHeaders'),
				'prependBody' => array((is_callable(array($this->test, 'mockWebPrependBody')) ? $this->test : $this), 'mockWebPrependBody'),
				'set' => array((is_callable(array($this->test, 'mockWebSet')) ? $this->test : $this), 'mockWebSet'),
				'setBody' => array((is_callable(array($this->test, 'mockWebSetBody')) ? $this->test : $this), 'mockWebSetBody'),
				'setHeader' => array((is_callable(array($this->test, 'mockWebSetHeader')) ? $this->test : $this), 'mockWebSetHeader'),
			)
		);

		return $mockObject;
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebAppendBody($content)
	{
		array_push($this->body, (string) $content);
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::get method.
	 *
	 * @param   string  $name     The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebGet($name, $default = null)
	{
		return isset($this->config[$name]) ? $this->config[$name] : $default;
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::getBody method.
	 *
	 * @param   boolean  $asArray  True to return the body as an array of strings.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebGetBody($asArray = false)
	{
		return $asArray ? $this->body : implode((array) $this->body);
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::getHeaders method.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebGetHeaders()
	{
		return $this->headers;
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::appendBody method.
	 *
	 * @param   string  $content  The content to append to the response body.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebPrependBody($content)
	{
		array_unshift($this->body, (string) $content);
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::set method.
	 *
	 * @param   string  $name   The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function mockWebSet($name, $value)
	{
		$this->config[$name] = $value;
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::setBody method.
	 *
	 * @param   string  $content  The body of the response.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function mockWebSetBody($content)
	{
		$this->body = array($content);
	}

	/**
	 * Mock the Joomla\Application\AbstractWebApplication::setHeader method.
	 *
	 * @param   string   $name     The name of the header to set.
	 * @param   string   $value    The value of the header to set.
	 * @param   boolean  $replace  True to replace any headers with the same name.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function mockWebSetHeader($name, $value, $replace = false)
	{
		if (!$replace)
		{
			if (!isset($this->headers[$name]))
			{
				$this->headers[$name] = $value;
			}
		}
		else
		{
			$this->headers[$name] = $value;
		}
	}
}
