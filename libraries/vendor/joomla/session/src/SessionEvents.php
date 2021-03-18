<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

/**
 * Class defining the events dispatched by the session API
 *
 * @since  __DEPLOY_VERSION__
 */
final class SessionEvents
{
	/**
	 * Private constructor to prevent instantiation of this class
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function __construct()
	{
	}

	/**
	 * Session event which is dispatched after the session has been started.
	 *
	 * Listeners to this event receive a `Joomla\Session\SessionEvent` object.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const START = 'session.start';

	/**
	 * Session event which is dispatched after the session has been restarted.
	 *
	 * Listeners to this event receive a `Joomla\Session\SessionEvent` object.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public const RESTART = 'session.restart';
}
