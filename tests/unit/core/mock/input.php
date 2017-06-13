<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JInput.
 *
 * @package  Joomla.Test
 * @since    3.4
 */
class TestMockInput
{
	/**
	 * Array to hold mock get and set values.
	 *
	 * @var    array
	 * @since  3.4
	 */
	private static $inputs;

	/**
	 * @var    PHPUnit_Framework_TestCase
	 * @since  3.4
	 */
	private static $test;

	/**
	 * Class contructor.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test class.
	 *
	 * @since   3.4
	 */
	public function __construct(PHPUnit_Framework_TestCase $test)
	{
		self::$inputs = [];
		self::$test   = $test;
	}

	/**
	 * Creates an instance of a mock JInput object.
	 *
	 * @param   array  $options  An associative array of options to configure the mock.
	 *                           * methods => an array of additional methods to mock
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.4
	 */
	public function createInput(array $options = null)
	{
		// Collect all the relevant methods in JInput.
		$methods = [
			'count',
			'def',
			'get',
			'getArray',
			'getInt',
			'getMethod',
			'set',
			'serialize',
			'unserialize',
		];

		// Add custom methods if required for derived application classes.
		if (isset($options['methods']) && is_array($options['methods']))
		{
			$methods = array_merge($methods, $options['methods']);
		}

		// Build the mock object.
		$mockObject = self::$test->getMockBuilder('JInput')
					->setMethods($methods)
					->setConstructorArgs([])
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		self::$test->assignMockCallbacks(
			$mockObject,
			[
				'get'      => [(is_callable([self::$test, 'mockInputGet']) ? self::$test : $this), 'mockInputGet'],
				'getArray' => [(is_callable([self::$test, 'mockInputGetArray']) ? self::$test : $this), 'mockInputGetArray'],
				'getInt'   => [(is_callable([self::$test, 'mockInputGetInt']) ? self::$test : $this), 'mockInputGetInt'],
				'set'      => [(is_callable([self::$test, 'mockInputSet']) ? self::$test : $this), 'mockInputSet'],
			]
		);

		$mockObject->get = $mockObject;
		$mockObject->post = $mockObject;
		$mockObject->request = $mockObject;

		return $mockObject;
	}

	/**
	 * Creates an instance of a mock JInputJson object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.4
	 */
	public function createInputJson()
	{
		$mockObject = $this->createInput(['methods' => ['getRaw']]);

		self::$test->assignMockCallbacks(
			$mockObject,
			[
				'getRaw' => [(is_callable([self::$test, 'mockInputGetRaw']) ? self::$test : $this), 'mockInputGetRaw'],
			]
		);

		return $mockObject;
	}

	/**
	 * Callback for the mock get method.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  string
	 *
	 * @since   3.4
	 */
	public static function mockInputGet($name, $default = null, $filter = 'cmd')
	{
		return isset(self::$inputs[$name]) ? self::$inputs[$name] : $default;
	}

	/**
	 * Callback for the mock getArray method.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 *                              If empty and datasource is null, all the input data will be returned
	 *                              but filtered using the default case in JFilterInput::clean.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public static function mockInputGetArray(array $vars = [], $datasource = null)
	{
		return [];
	}

	/**
	 * Callback for the mock getInt method.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 *
	 * @return  string
	 *
	 * @since   3.4
	 */
	public static function mockInputGetInt($name, $default = null)
	{
		return (int) self::mockInputGet($name, $default);
	}

	/**
	 * Callback for the mock JInputJson::getRaw method.
	 *
	 * @return  string
	 *
	 * @since   3.4
	 */
	public static function mockInputGetRaw()
	{
		return '';
	}

	/**
	 * Callback for the mock set method.
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public static function mockInputSet($name, $value)
	{
		self::$inputs[$name] = $value;
	}
}
