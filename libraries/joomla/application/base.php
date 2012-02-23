<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.event.dispatcher');

/**
 * Joomla Platform Base Application Class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       12.1
 */
abstract class JApplicationBase extends JObject
{
	/**
	 * The application input object.
	 *
	 * @var    JInput
	 * @since  12.1
	 */
	public $input = null;

	/**
	 * The application dispatcher object.
	 *
	 * @var    JDispatcher
	 * @since  12.1
	 */
	protected $dispatcher;

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callback  $handler  The handler, a function or an instance of a event object.
	 *
	 * @return  JApplicationBase  The application to allow chaining.
	 *
	 * @since   12.1
	 */
	public function registerEvent($event, $handler)
	{
		if ($this->dispatcher instanceof JDispatcher)
		{
			$this->dispatcher->register($event, $handler);
		}

		return $this;
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * @param   string  $event  The event name.
	 * @param   array   $args   An array of arguments (optional).
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since   12.1
	 */
	public function triggerEvent($event, array $args = null)
	{
		if ($this->dispatcher instanceof JDispatcher)
		{
			return $this->dispatcher->trigger($event, $args);
		}

		return null;
	}

	/**
	 * Method to create an event dispatcher for the application. The logic and options for creating
	 * this object are adequately generic for default cases but for many applications it will make
	 * sense to override this method and create event dispatchers based on more specific needs.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadDispatcher()
	{
		$this->dispatcher = JDispatcher::getInstance();
	}
}
