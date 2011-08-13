<?php
/**
 * @package    Joomla.UnitTest
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license    GNU General Public License
 */

/**
 * Mock class for JSession.
 *
 * @package  Joomla.UnitTest
 * @since    11.3
 */
class JSessionGlobalMock
{
	/**
	 * Creates an instance of the mock JDatabase object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test)
	{
		// Mock all the public methods.
		$methods = array(
			'clear',
			'close',
			'destroy',
			'fork',
			'get',
			'getExpire',
			'getFormToken',
			'getId',
			'getInstance',
			'getName',
			'getState',
			'getStores',
			'getToken',
			'has',
			'hasToken',
			'getPrefix',
			'isNew',
			'restart',
			'set',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JSession',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		// Mock selected methods.
		$test->assignMockCallbacks(
			$mockObject,
			array(
				'get' => array(get_called_class(), 'mockGet'),
			)
		);

		return $mockObject;
	}

	/**
	 * Mocking the quote method.
	 *
	 * @param   string  $key  The key to get.
	 *
	 * @return  mixed
	 *
	 * @since   11.3
	 */
	public function mockGet($key)
	{
		switch ($key)
		{
			case 'user':
				include_once JPATH_PLATFORM . '/joomla/user/user.php';

				$user = new JUser;

				return $user;
		}

		return null;
	}
}