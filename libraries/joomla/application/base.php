<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.input');
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
	 * The application dispatcher object.
	 *
	 * @var    JEventDispatcher
	 * @since  12.1
	 */
	protected $dispatcher;

	/**
	 * The application identity object.
	 *
	 * @var    JUser
	 * @since  12.1
	 */
	protected $identity;

	/**
	 * The application input object.
	 *
	 * @var    JInput
	 * @since  12.1
	 */
	public $input = null;

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   12.1
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Get the application identity.
	 *
	 * @return  mixed  A JUser object or null.
	 *
	 * @since   12.1
	 */
	public function getIdentity()
	{
		return $this->identity;
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
	 * Allows the application to load a custom or default dispatcher.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create event
	 * dispatchers, if required, based on more specific needs.
	 *
	 * @param   JDispatcher  $dispatcher  An optional dispatcher object. If omitted, the factory dispatcher is created.
	 *
	 * @return  JApplicationBase This method is chainable.
	 *
	 * @since   12.1
	 */
	public function loadDispatcher(JDispatcher $dispatcher = null)
	{
		$this->dispatcher = ($dispatcher === null) ? JDispatcher::getInstance() : $dispatcher;

		return $this;
	}

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create an identity,
	 * if required, based on more specific needs.
	 *
	 * @param   JUser  $identity  An optional identity object. If omitted, the factory user is created.
	 *
	 * @return  JApplicationBase This method is chainable.
	 *
	 * @since   12.1
	 */
	public function loadIdentity(JUser $identity = null)
	{
		$this->identity = ($identity === null) ? JFactory::getUser() : $identity;

		return $this;
	}
}
