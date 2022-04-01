<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Class to mock JCache.
 *
 * @package  Joomla.Test
 * @since    3.0.0
 */
class TestMockCache
{
	/**
	 * Public cache to inject faux data.
	 *
	 * @var    array
	 * @since  3.0.0
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
	 * @since   3.0.0
	 */
	public static function create(TestCase $test, $data = array())
	{
		self::$cache = $data;

		// Collect all the relevant methods in JConfig.
		$methods = array(
			'get',
			'store',
		);

		// Build the mock object.
		$mockObject = $test->getMockBuilder('JCache')
					->setMethods($methods)
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

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
	 * @since   3.0.0
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
	 * @since   3.0.0
	 */
	public static function mockStore($value, $id)
	{
		self::$cache[$id] = $value;
	}
}
