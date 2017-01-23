<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Event\DispatcherInterface;

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
	 * @param   PHPUnit_Framework_TestCase  $test      A test object.
	 * @param   boolean                     $defaults  True to create the default mock handlers and triggers.
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
		$mockObject = $test->getMockBuilder(DispatcherInterface::class)
			->setMethods($methods)
			->getMock();

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
				)
			);
		}

		return $mockObject;
	}

	/**
	 * Callback for the DispatcherInterface register method.
	 *
	 * @param   string  $event  Name of the event to register handler for.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since   11.3
	 */
	public static function mockDispatch($event, $args = [])
	{
		// Track the events that were triggered, in order.
		self::$triggered[] = $event;

		if (!empty(self::$handlers[$event]))
		{
			return self::$handlers[$event];
		}

		return array();
	}

	/**
	 * Callback for the DispatcherInterface register method.
	 *
	 * @param   string    $event    Name of the event to register handler for.
	 * @param   callable  $handler  Callback
	 * @param   mixed     $return   The mock value to return for the given event handler.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public static function mockRegister($event, $handler, $return = null)
	{
		if (empty(self::$handlers[$event]))
		{
			self::$handlers[$event] = [];
		}

		if (is_a($handler, 'closure') || is_object($handler))
		{
			$identifier = spl_object_hash($handler);
		}
		elseif (is_array($handler) && count($handler) == 2 && is_object($handler[0]))
		{
			$identifier = spl_object_hash($handler[0]) . '::' . $handler[1];
		}
		else
		{
			throw new InvalidArgumentException('The handler is not a valid callable.');
		}

		self::$handlers[$event][$identifier] = $return;
	}
}
