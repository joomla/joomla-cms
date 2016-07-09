<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class to mock DispatcherInterface.
 *
 * @package  Joomla.Test
 * @since    12.1
 */
class TestMockDispatcher
{
	/**
	 * Keeps track of mock handlers.
	 *
	 * @var    array
	 * @since  11.3
	 */
	public static $handlers = array();

	/**
	 * Keeps track of triggers.
	 *
	 * @var    array
	 * @since  11.3
	 */
	public static $triggered = array();

	/**
	 * Creates and instance of the mock DispatcherInterface object.
	 *
	 * @param   PHPUnit_Framework_TestCase $test     A test object.
	 * @param   boolean                    $defaults True to create the default mock handlers and triggers.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   11.3
	 */
	public static function create($test, $defaults = true)
	{
		// Clear the static tracker properties.
		self::$handlers  = array();
		self::$triggered = array();

		// Collect all the relevant methods in DispatcherInterface.
		$methods = array(
			'addListener',
			'dispatch',
			'register',
			'removeListener',
			'trigger',
			'test',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'\\Joomla\\Event\\DispatcherInterface',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		// Mock selected methods.
		$test->assignMockReturns(
			$mockObject, array(
				// An additional 'test' method for confirming this object is successfully mocked.
				'test' => 'ok',
			)
		);

		if ($defaults)
		{
			$test->assignMockCallbacks(
				$mockObject,
				array(
					'dispatch'     => array(get_called_class(), 'mockDispatch'),
					'addListener'  => array(get_called_class(), 'mockRegister'),
					'triggerEvent' => array(get_called_class(), 'mockTrigger'),
				)
			);
		}

		return $mockObject;
	}

	/**
	 * Callback for the DispatcherInterface register method.
	 *
	 * @param   string $event Name of the event to register handler for.
	 * @param   array  $args  An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since   11.3
	 */
	public function mockDispatch($event, $args = array())
	{
		if (empty(self::$handlers[$event]))
		{
			// Track the events that were triggered, in order.
			self::$triggered[] = $event;

			return self::$handlers[$event];
		}

		return array();
	}

	/**
	 * Callback for the DispatcherInterface register method.
	 *
	 * +     * @param   string $event Name of the event to register handler for.
	 * +     * @param   Callable $handler Callback
	 * +     * @param   mixed $return The mock value to return for the given event handler.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function mockRegister($event, $handler, $return = null)
	{
		if (empty(self::$handlers[$event]))
		{
			self::$handlers[$event] = array();
		}

		self::$handlers[$event][print_r($handler, true)] = $return;
	}


	/**
	 * Callback for the DispatcherInterface trigger method.
	 *
	 * @param   string $event The event to trigger.
	 * @param   array  $args  An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since  11.3
	 */
	public static function mockTrigger($event, $args = array())
	{
		if (isset(self::$handlers[ $event ]))
		{
			// Track the events that were triggered, in order.
			self::$triggered[] = $event;

			return self::$handlers[$event];
		}

		return array();
	}
}
