<?php
/**
 * Part of the Joomla Framework Authentication Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Authentication\Strategies;

use Joomla\Authentication\Password\HandlerInterface;
use Joomla\Authentication\AbstractUsernamePasswordAuthenticationStrategy;
use Joomla\Authentication\Authentication;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;

/**
 * Joomla Framework Database Strategy Authentication class
 *
 * @since  1.1.0
 */
class DatabaseStrategy extends AbstractUsernamePasswordAuthenticationStrategy
{
	/**
	 * DatabaseInterface object
	 *
	 * @var    DatabaseInterface
	 * @since  1.1.0
	 */
	private $db;

	/**
	 * Database connection options
	 *
	 * @var    array
	 * @since  1.1.0
	 */
	private $dbOptions;

	/**
	 * The Input object
	 *
	 * @var    Input
	 * @since  1.1.0
	 */
	private $input;

	/**
	 * Strategy Constructor
	 *
	 * @param   Input              $input            The input object from which to retrieve the request credentials.
	 * @param   DatabaseInterface  $database         DatabaseDriver for retrieving user credentials.
	 * @param   array              $options          Optional options array for configuring the credential storage connection.
	 * @param   HandlerInterface   $passwordHandler  The password handler.
	 *
	 * @since   1.1.0
	 */
	public function __construct(Input $input, DatabaseInterface $database, array $options = [], HandlerInterface $passwordHandler = null)
	{
		parent::__construct($passwordHandler);

		$this->input = $input;
		$this->db    = $database;

		$options['database_table']  = $options['database_table'] ?? '#__users';
		$options['username_column'] = $options['username_column'] ?? 'username';
		$options['password_column'] = $options['password_column'] ?? 'password';

		$this->dbOptions = $options;
	}

	/**
	 * Attempt to authenticate the username and password pair.
	 *
	 * @return  string|boolean  A string containing a username if authentication is successful, false otherwise.
	 *
	 * @since   1.1.0
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
		try
		{
			$password = $this->db->setQuery(
				$this->db->getQuery(true)
					->select($this->dbOptions['password_column'])
					->from($this->dbOptions['database_table'])
					->where($this->dbOptions['username_column'] . ' = ' . $this->db->quote($username))
			)->loadResult();
		}
		catch (\RuntimeException $exception)
		{
			return false;
		}

		if (!$password)
		{
			return false;
		}

		return $password;
	}
}
