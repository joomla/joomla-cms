<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Strategies;

use Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy;
use Joomla\Authentication\Authentication;
use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Input\Input;

/**
 * Joomla Framework Local Strategy Authentication class
 *
 * @since  1.0
 */
class LocalStrategy extends AbstractUsernamePasswordAuthenticationStrategy
{
	/**
	 * The credential store.
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $credentialStore;

	/**
	 * The Input object
	 *
	 * @var    Input
	 * @since  1.0
	 */
	private $input;

	/**
	 * Strategy Constructor
	 *
	 * @param   Input             $input            The input object from which to retrieve the request credentials.
	 * @param   array             $credentialStore  Hash of username and hash pairs.
	 * @param   HandlerInterface  $passwordHandler  The password handler.
	 *
	 * @since   1.0
	 */
	public function __construct(Input $input, array $credentialStore = [], HandlerInterface $passwordHandler = null)
	{
		parent::__construct($passwordHandler);

		$this->credentialStore = $credentialStore;
		$this->input           = $input;
	}

	/**
	 * Attempt to authenticate the username and password pair.
	 *
	 * @return  string|boolean  A string containing a username if authentication is successful, false otherwise.
	 *
	 * @since   1.0
	 */
	public function authenticate()
	{
		$username = $this->input->get('username', false, 'username');
		$password = $this->input->get('password', false, 'raw');

		if (!$username || !$password)
		{
			$this->status = Authentication::NO_CREDENTIALS;

			return false;
		}

		return $this->doAuthenticate($username, $password);
	}

	/**
	 * Retrieve the hashed password for the specified user.
	 *
	 * @param   string  $username  Username to lookup.
	 *
	 * @return  string|boolean  Hashed password on success or boolean false on failure.
	 *
	 * @since   1.1.0
	 */
	protected function getHashedPassword($username)
	{
		return $this->credentialStore[$username] ?? false;
	}
}
