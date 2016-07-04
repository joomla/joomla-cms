<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\Event\Dispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Registry\Registry;

/**
 * Joomla Platform Base Application Class
 *
 * @property-read  JInput  $input  The application input object
 *
 * @since  12.1
 */
abstract class JApplicationBase extends AbstractApplication
{
	/**
	 * The application dispatcher object.
	 *
	 * @var    DispatcherInterface
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
	 * Class constructor.
	 *
	 * @param   JInput    $input   An optional argument to provide dependency injection for the application's
	 *                             input object.  If the argument is a JInput object that object will become
	 *                             the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's
	 *                             config object.  If the argument is a Registry object that object will become
	 *                             the application's config object, otherwise a default config object is created.
	 *
	 * @since   12.1
	 */
	public function __construct(JInput $input = null, Registry $config = null)
	{
		$this->input = $input instanceof JInput ? $input : new JInput;
		$this->config = $config instanceof Registry ? $config : new Registry;

		$this->initialise();
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
	 * @param   callable  $handler  The handler, a function or an instance of an event object.
	 *
	 * @return  JApplicationBase  The application to allow chaining.
	 *
	 * @since   12.1
	 */
	public function registerEvent($event, $handler)
	{
		if ($this->dispatcher instanceof DispatcherInterface)
		{
			$this->dispatcher->addListener($event, $handler);
		}

		return $this;
	}
	/**
	 * Returns the event dispatcher of the application. This is a temporary method added during the Event package
	 * refactoring.
	 *
	 * @deprecated
	 *
	 * TODO REFACTOR ME! Remove this and go through a Container.
	 *
	 * @return  DispatcherInterface
	 */
	public function getDispatcher()
	{
		if (!($this->dispatcher instanceof DispatcherInterface))
		{
			$this->loadDispatcher();
		}

		return $this->dispatcher;
	}

	/**
	 * Calls all handlers associated with an event group.
	 *
	 * This is a legacy method, implementing old-style (Joomla! 3.x) plugin calls. It's best to go directly through the
	 * Dispatcher and handle the returned EventInterface object instead of going through this method. This method is
	 * deprecated and will be removed in Joomla! 5.x.
	 *
	 * This method will only return the 'result' argument of the event
	 *
	 * @param   string        $eventName  The event name.
	 * @param   array|Event   $args       An array of arguments or an Event object (optional).
	 *
	 * @return  array   An array of results from each function call, or null if no dispatcher is defined.
	 *
	 * @since       12.1
	 * @throws      InvalidArgumentException
	 * @deprecated  5.0
	 */
	public function triggerEvent($eventName, $args = array())
	{
		$dispatcher = $this->getDispatcher();

		if ($this->dispatcher instanceof DispatcherInterface)
		{
			if ($args instanceof Event)
			{
				$event = $args;
			}
			elseif (is_array($args))
			{
				$event = new Event($eventName, $args);
			}
			else
			{
				throw new InvalidArgumentException('The arguments must either be an event or an array');
			}

			$result = $dispatcher->dispatch($eventName, $event);

			// TODO - There are still test cases where the result isn't defined, temporarily leave the isset check in place
			return !isset($result['result']) || is_null($result['result']) ? [] : $result['result'];
		}

		return;
	}

	/**
	 * Allows the application to load a custom or default dispatcher.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create event
	 * dispatchers, if required, based on more specific needs.
	 *
	 * @param   DispatcherInterface  $dispatcher  An optional dispatcher object. If omitted, the factory dispatcher is created.
	 *
	 * @return  JApplicationBase This method is chainable.
	 *
	 * @since   12.1
	 */
	public function loadDispatcher(DispatcherInterface $dispatcher = null)
	{
		$this->dispatcher = ($dispatcher === null) ? new Dispatcher() : $dispatcher;

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

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   3.4 (CMS)
	 * @deprecated  4.0  The default concrete implementation of doExecute() will be removed, subclasses will need to provide their own implementation.
	 */
	protected function doExecute()
	{
		return;
	}

}
