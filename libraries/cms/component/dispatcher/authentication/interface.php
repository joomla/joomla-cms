<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Interface for transparent authentication in the dispatcher
 *
 * @since  3.4
 */
interface JComponentDispatcherAuthenticationInterface
{
	/**
	 * Method to check if the authentication method is valid.
	 *
	 * @param   string $format The format being dispatched.
	 *
	 * @return  boolean    True if valid, false otherwise.
	 *
	 * @since   3.4
	 */
	public function isValid($format);

	/**
	 * Method to authenticate a user by the given method.
	 *
	 * @return  mixed    Array with username and password keys if valid, false otherwise.
	 *
	 * @since   3.4
	 */
	public function authenticate();
}
