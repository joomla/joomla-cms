<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Psr\Log\LoggerInterface;

/**
 * Trait for application classes which dispatch events
 *
 * @since  4.0.0
 */
trait EventAware
{
	/**
	 * Get the event dispatcher.
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   4.0.0
	 * @throws  \UnexpectedValueException May be thrown if the dispatcher has not been set.
	 */
	abstract public function getDispatcher();

	/**
	 * Get the logger.
	 *
	 * @return  LoggerInterface
	 *
	 * @since   4.0.0
	 */
	abstract public function getLogger();

	/**
	 * Registers a handler to a particular event group.
	 *
	 * @param   string    $event    The event name.
	 * @param   callable  $handler  The handler, a function or an instance of an event object.
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	public function registerEvent($event, callable $handler)
	{
		try
		{
			$this->getDispatcher()->addListener($event, $handler);
		}
		catch (\UnexpectedValueException $e)
		{
			// No dispatcher is registered, don't throw an error (mimics old behavior)
		}

		return $this;
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
	 * @param   string       $eventName  The event name.
	 * @param   array|Event  $args       An array of arguments or an Event object (optional).
	 *
	 * @return  array  An array of results from each function call. Note this will be an empty array if no dispatcher is set.
	 *
	 * @since       4.0.0
	 * @throws      \InvalidArgumentException
	 * @deprecated  5.0
	 */
	public function triggerEvent($eventName, $args = [])
	{
		try
		{
			$dispatcher = $this->getDispatcher();
		}
		catch (\UnexpectedValueException $exception)
		{
			$this->getLogger()->error(sprintf('Dispatcher not set in %s, cannot trigger events.', \get_class($this)));

			return [];
		}

		if ($args instanceof Event)
		{
			$event = $args;
		}
		elseif (\is_array($args))
		{
			$event = new Event($eventName, $args);
		}
		else
		{
			throw new \InvalidArgumentException('The arguments must either be an event or an array');
		}

		$result = $dispatcher->dispatch($eventName, $event);

		// @todo - There are still test cases where the result isn't defined, temporarily leave the isset check in place
		return !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];
	}
}
