<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Interface for events.
 * An event has a name and its propagation can be stopped.
 *
 * @since  1.0
 */
interface EventInterface
{
	/**
	 * Get an event argument value.
	 *
	 * @param   string  $name     The argument name.
	 * @param   mixed   $default  The default value if not found.
	 *
	 * @return  mixed  The argument value or the default value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getArgument($name, $default = null);

	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 *
	 * @since   1.0
	 */
	public function getName();

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean  True if stopped, false otherwise.
	 *
	 * @since   1.0
	 */
	public function isStopped();

	/**
	 * Stops the propagation of the event to further event listeners.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stopPropagation();
}
