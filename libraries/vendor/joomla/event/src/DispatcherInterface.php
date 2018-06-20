<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for event dispatchers.
 *
 * @since  1.0
 */
interface DispatcherInterface
{
	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param   string          $name   The name of the event to dispatch.
	 *                                  The name of the event is the name of the method that is invoked on listeners.
	 * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
	 *                                  If not supplied, an empty EventInterface instance is created.
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch(string $name, EventInterface $event = null): EventInterface;

	/**
	 * Attaches a listener to an event
	 *
	 * @param   string    $eventName  The event to listen to.
	 * @param   callable  $callback   A callable function.
	 * @param   integer   $priority   The priority at which the $callback executed.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addListener(string $eventName, callable $callback, int $priority = 0): bool;

	/**
	 * Get the listeners registered to the given event.
	 *
	 * @param   string  $event  The event to fetch listeners for
	 *
	 * @return  callable[]  An array of registered listeners sorted according to their priorities.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getListeners($event);

	/**
	 * Tell if the given listener has been added.
	 *
	 * If an event is specified, it will tell if the listener is registered for that event.
	 *
	 * @param   callable  $callback   The callable to check is listening to the event.
	 * @param   string    $eventName  The event to check a listener is subscribed to.
	 *
	 * @return  boolean  True if the listener is registered, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasListener(callable $callback, $eventName = null);

	/**
	 * Removes an event listener from the specified event.
	 *
	 * @param   string    $eventName  The event to remove a listener from.
	 * @param   callable  $listener   The listener to remove.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeListener(string $eventName, callable $listener);

	/**
	 * Adds an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addSubscriber(SubscriberInterface $subscriber);

	/**
	 * Removes an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeSubscriber(SubscriberInterface $subscriber);
}
