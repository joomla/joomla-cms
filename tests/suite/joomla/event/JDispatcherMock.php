<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/event/dispatcher.php';

/**
 * Inspector JContentHelperTest class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       11.3
 */
class JDispatcherGlobalMock
{
	/**
	 * Keeps track of mock handlers.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected $handlers = array();

	/**
	 * Creates and instance of the mock JLanguage object.
	 *
	 * @param   object  $test   A test object.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public static function create($test, $defaults = true)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'register',
			'trigger',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JDispatcher',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		if ($defaults)
		{
			$test->assignMockCallbacks(
				$mockObject,
				array(
					'register' => array(get_called_class(), 'mockRegister'),
					'trigger' => array(get_called_class(), 'mockTrigger'),
				)
			);

		}

		return $mockObject;
	}

	/**
	 * Callback for the JDispatcher register method.
	 *
	 * @param   string  $event    Name of the event to register handler for.
	 * @param   string  $handler  Name of the event handler.
	 * @param   mixed   $return   The mock value to return for the given event handler.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function mockRegister($event, $handler, $return)
	{
		if (empty(self::$handlers[$event]))
		{
			self::$handlers[$event] = array();
		}

		self::$handlers[$event][$handler] = $return;
	}

	/**
	 * Callback for the JDispatcher trigger method.
	 *
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since  11.3
	 */
	public function mockTrigger($event, $args = array())
	{
		if (isset(self::$handlers[$event]))
		{
			return self::$handlers[$event];
		}

		return array();
	}

}