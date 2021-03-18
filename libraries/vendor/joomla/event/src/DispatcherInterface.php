<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
	 * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
	 *                                  If not supplied, an empty EventInterface instance is created.
	 *                                  Note, not passing an event is deprecated and will be required as of 3.0.
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch(string $name, ?EventInterface $event = null): EventInterface;

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
	 * Clear the listeners in this dispatcher.
	 *
	 * If an event is specified, the listeners will be cleared only for that event.
	 *
	 * @param   string  $event  The event name.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clearListeners($event = null);

	/**
	 * Count the number of registered listeners for the given event.
	 *
	 * @param   string  $event  The event name.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function countListeners($event);

	/**
	 * Get the listeners registered to the given event.
	 *
	 * @param   string|null  $event  The event to fetch listeners for or null to fetch all listeners
	 *
	 * @return  callable[]  An array of registered listeners sorted according to their priorities.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getListeners(?string $event = null);

	/**
	 * Tell if the given listener has been added.
	 *
	 * If an event is specified, it will tell if the listener is registered for that event.
	 *
	 * @param   callable     $callback   The callable to check is listening to the event.
	 * @param   string|null  $eventName  An optional event name to check a listener is subscribed to.
	 *
	 * @return  boolean  True if the listener is registered, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasListener(callable $callback, ?string $eventName = null);

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
	public function removeListener(string $eventName, callable $listener): void;

	/**
	 * Adds an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addSubscriber(SubscriberInterface $subscriber): void;

	/**
	 * Removes an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeSubscriber(SubscriberInterface $subscriber): void;
}
