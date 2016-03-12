<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Client helper class
 *
 * @since  11.1
 */
class JClientHelper
{
	/**
	 * Method to return the array of client layer configuration options
	 *
	 * @param   string   $client  Client name, currently only 'ftp' is supported
	 * @param   boolean  $force   Forces re-creation of the login credentials. Set this to
	 *                            true if login credentials in the session storage have changed
	 *
	 * @return  array    Client layer configuration options, consisting of at least
	 *                   these fields: enabled, host, port, user, pass, root
	 *
	 * @since   11.1
	 */
	public static function getCredentials($client, $force = false)
	{
		static $credentials = array();

		$client = strtolower($client);

		if (!isset($credentials[$client]) || $force)
		{
			$config = JFactory::getConfig();

			// Fetch the client layer configuration options for the specific client
			switch ($client)
			{
				case 'ftp':
					$options = array(
						'enabled' => $config->get('ftp_enable'),
						'host' => $config->get('ftp_host'),
						'port' => $config->get('ftp_port'),
						'user' => $config->get('ftp_user'),
						'pass' => $config->get('ftp_pass'),
						'root' => $config->get('ftp_root'));
					break;

				default:
					$options = array('enabled' => false, 'host' => '', 'port' => '', 'user' => '', 'pass' => '', 'root' => '');
					break;
			}

			// If user and pass are not set in global config lets see if they are in the session
			if ($options['enabled'] == true && ($options['user'] == '' || $options['pass'] == ''))
			{
				$session = JFactory::getSession();
				$options['user'] = $session->get($client . '.user', null, 'JClientHelper');
				$options['pass'] = $session->get($client . '.pass', null, 'JClientHelper');
			}

			// If user or pass are missing, disable this client
			if ($options['user'] == '' || $options['pass'] == '')
			{
				$options['enabled'] = false;
			}

			// Save the credentials for later use
			$credentials[$client] = $options;
		}

		return $credentials[$client];
	}

	/**
	 * Method to set client login credentials
	 *
	 * @param   string  $client  Client name, currently only 'ftp' is supported
	 * @param   string  $user    Username
	 * @param   string  $pass    Password
	 *
	 * @return  boolean  True if the given login credentials have been set and are valid
	 *
	 * @since   11.1
	 */
	public static function setCredentials($client, $user, $pass)
	{
		$return = false;
		$client = strtolower($client);

		// Test if the given credentials are valid
		switch ($client)
		{
			case 'ftp':
				$config = JFactory::getConfig();
				$options = array('enabled' => $config->get('ftp_enable'), 'host' => $config->get('ftp_host'), 'port' => $config->get('ftp_port'));

				if ($options['enabled'])
				{
					$ftp = JClientFtp::getInstance($options['host'], $options['port']);

					// Test the connection and try to log in
					if ($ftp->isConnected())
					{
						if ($ftp->login($user, $pass))
						{
							$return = true;
						}

						$ftp->quit();
					}
				}
				break;

			default:
				break;
		}

		if ($return)
		{
			// Save valid credentials to the session
			$session = JFactory::getSession();
			$session->set($client . '.user', $user, 'JClientHelper');
			$session->set($client . '.pass', $pass, 'JClientHelper');

			// Force re-creation of the data saved within JClientHelper::getCredentials()
			self::getCredentials($client, true);
		}

		return $return;
	}

	/**
	 * Method to determine if client login credentials are present
	 *
	 * @param   string  $client  Client name, currently only 'ftp' is supported
	 *
	 * @return  boolean  True if login credentials are available
	 *
	 * @since   11.1
	 */
	public static function hasCredentials($client)
	{
		$return = false;
		$client = strtolower($client);

		// Get (unmodified) credentials for this client
		switch ($client)
		{
			case 'ftp':
				$config = JFactory::getConfig();
				$options = array('enabled' => $config->get('ftp_enable'), 'user' => $config->get('ftp_user'), 'pass' => $config->get('ftp_pass'));
				break;

			default:
				$options = array('enabled' => false, 'user' => '', 'pass' => '');
				break;
		}

		if ($options['enabled'] == false)
		{
			// The client is disabled in global config, so let's pretend we are OK
			$return = true;
		}
		elseif ($options['user'] != '' && $options['pass'] != '')
		{
			// Login credentials are available in global config
			$return = true;
		}
		else
		{
			// Check if login credentials are available in the session
			$session = JFactory::getSession();
			$user = $session->get($client . '.user', null, 'JClientHelper');
			$pass = $session->get($client . '.pass', null, 'JClientHelper');

			if ($user != '' && $pass != '')
			{
				$return = true;
			}
		}

		return $return;
	}

	/**
	 * Determine whether input fields for client settings need to be shown
	 *
	 * If valid credentials were passed along with the request, they are saved to the session.
	 * This functions returns an exception if invalid credentials have been given or if the
	 * connection to the server failed for some other reason.
	 *
	 * @param   string  $client  The name of the client.
	 *
	 * @return  mixed  True, if FTP settings; JError if using legacy tree.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException if credentials invalid
	 */
	public static function setCredentialsFromRequest($client)
	{
		// Determine whether FTP credentials have been passed along with the current request
		$input = JFactory::getApplication()->input;
		$user = $input->post->getString('username', null);
		$pass = $input->post->getString('password', null);

		if ($user != '' && $pass != '')
		{
			// Add credentials to the session
			if (self::setCredentials($client, $user, $pass))
			{
				$return = false;
			}
			else
			{
				if (class_exists('JError'))
				{
					$return = JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_CLIENT_ERROR_HELPER_SETCREDENTIALSFROMREQUEST_FAILED'));
				}
				else
				{
					throw new InvalidArgumentException('Invalid user credentials');
				}
			}
		}
		else
		{
			// Just determine if the FTP input fields need to be shown
			$return = !self::hasCredentials('ftp');
		}

		return $return;
	}
}
