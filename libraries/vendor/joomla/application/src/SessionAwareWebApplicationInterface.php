<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Session\SessionInterface;

/**
 * Application sub-interface defining a web application class which supports sessions
 *
 * @since  __DEPLOY_VERSION__
 */
interface SessionAwareWebApplicationInterface extends WebApplicationInterface
{
	/**
	 * Method to get the application session object.
	 *
	 * @return  SessionInterface  The session object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSession();

	/**
	 * Sets the session for the application to use, if required.
	 *
	 * @param   SessionInterface  $session  A session object.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSession(SessionInterface $session);

	/**
	 * Checks for a form token in the request.
	 *
	 * @param   string  $method  The request method in which to look for the token key.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkToken($method = 'post');

	/**
	 * Method to determine a hash for anti-spoofing variable names
	 *
	 * @param   boolean  $forceNew  If true, force a new token to be created
	 *
	 * @return  string  Hashed var name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getFormToken($forceNew = false);
}
