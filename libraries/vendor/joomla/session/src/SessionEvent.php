<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Event\Event;

/**
 * Class representing a Session event
 *
 * @since  __DEPLOY_VERSION__
 */
class SessionEvent extends Event
{
	/**
	 * SessionInterface object for this event
	 *
	 * @var    SessionInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $session;

	/**
	 * Constructor.
	 *
	 * @param   string            $name     The event name.
	 * @param   SessionInterface  $session  The SessionInterface object for this event.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, SessionInterface $session)
	{
		parent::__construct($name);

		$this->session = $session;
	}

	/**
	 * Retrieve the SessionInterface object attached to this event.
	 *
	 * @return  SessionInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSession(): SessionInterface
	{
		return $this->session;
	}
}
