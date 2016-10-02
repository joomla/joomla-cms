<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock JCache.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockCache
{
	/**
	 * Public cache to inject faux data.
	 *
	 * @var    array
	 * @since  12.1
	 */
	public static $cache = array();

	/**
	 * Creates and instance of the mock JApplication object.
	 *
	 * @param   TestCase  $test  A test object.
	 * @param   array     $data  Data to prime the cache with.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public static function create(TestCase $test, $data = array())
	{
		self::$cache = $data;

		// Collect all the relevant methods in JConfig.
		$methods = array(
			'get',
			'store',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JCache',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		$test->assignMockCallbacks(
			$mockObject,
			array(
				'get' => array(get_called_class(), 'mockGet'),
				'store' => array(get_called_class(), 'mockStore'),
			)
		);

		return $mockObject;
	}

	/**
	 * Callback for the cache get method.
	 *
	 * @param   string  $id  The name of the cache key to retrieve.
	 *
	 * @return  mixed  The value of the key or null if it does not exist.
	 *
	 * @since   12.1
	 */
	public static function mockGet($id)
	{
		return isset(self::$cache[$id]) ? self::$cache[$id] : null;
	}

	/**
	 * Callback for the cache get method.
	 *
	 * @param   string  $value  The value to store.
	 * @param   string  $id     The name of the cache key.
	 *
	 * @return  mixed  The value of the key or null if it does not exist.
	 *
	 * @since   12.1
	 */
	public static function mockStore($value, $id)
	{
		self::$cache[$id] = $value;
	}
}
