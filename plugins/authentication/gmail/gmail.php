<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.gmail
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\Registry\Registry;

/**
 * GMail Authentication Plugin
 *
 * @since  1.5
 */
class PlgAuthenticationGMail extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array                   $credentials  Array holding the user credentials
	 * @param   array                   $options      Array of extra options
	 * @param   AuthenticationResponse  &$response    Authentication response object
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		// Load plugin language
		$this->loadLanguage();

		// No backend authentication
		if (JFactory::getApplication()->isClient('administrator') && !$this->params->get('backendLogin', 0))
		{
			return;
		}

		$success = false;

		$curlParams = array(
			'follow_location' => true,
			'transport.curl'  => array(
				CURLOPT_SSL_VERIFYPEER => $this->params->get('verifypeer', 1)
			),
		);

		$transportParams = new Registry($curlParams);

		try
		{
			$http = JHttpFactory::getHttp($transportParams, 'curl');
		}
		catch (RuntimeException $e)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->type          = 'GMail';
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_CURL_NOT_INSTALLED'));

			return;
		}

		// Check if we have a username and password
		if ($credentials['username'] === '' || $credentials['password'] === '')
		{
			$response->type          = 'GMail';
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_USER_BLACKLISTED'));

			return;
		}

		$blacklist = explode(',', $this->params->get('user_blacklist', ''));

		// Check if the username isn't blacklisted
		if (in_array($credentials['username'], $blacklist))
		{
			$response->type          = 'GMail';
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_USER_BLACKLISTED'));

			return;
		}

		$suffix      = $this->params->get('suffix', '');
		$applysuffix = $this->params->get('applysuffix', 0);
		$offset      = strpos($credentials['username'], '@');

		// Check if we want to do suffix stuff, typically for Google Apps for Your Domain
		if ($suffix && $applysuffix)
		{
			if ($applysuffix == 1 && $offset === false)
			{
				// Apply suffix if missing
				$credentials['username'] .= '@' . $suffix;
			}
			elseif ($applysuffix == 2)
			{
				// Always use suffix
				if ($offset)
				{
					// If we already have an @, get rid of it and replace it
					$credentials['username'] = substr($credentials['username'], 0, $offset);
				}

				$credentials['username'] .= '@' . $suffix;
			}
		}

		$headers = array(
			'Authorization' => 'Basic ' . base64_encode($credentials['username'] . ':' . $credentials['password'])
		);

		try
		{
			$result = $http->get('https://mail.google.com/mail/feed/atom', $headers);
		}
		catch (Exception $e)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->type          = 'GMail';
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED'));

			return;
		}

		$code = $result->code;

		switch ($code)
		{
			case 200 :
				$message = JText::_('JGLOBAL_AUTH_ACCESS_GRANTED');
				$success = true;
				break;

			case 401 :
				$message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
				break;

			default :
				$message = JText::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
				break;
		}

		$response->type = 'GMail';

		if (!$success)
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', $message);

			return;
		}

		if (strpos($credentials['username'], '@') === false)
		{
			if ($suffix)
			{
				// If there is a suffix then we want to apply it
				$email = $credentials['username'] . '@' . $suffix;
			}
			else
			{
				// If there isn't a suffix just use the default gmail one
				$email = $credentials['username'] . '@gmail.com';
			}
		}
		else
		{
			// The username looks like an email address (probably is) so use that
			$email = $credentials['username'];
		}

		// Extra security checks with existing local accounts
		$db                  = JFactory::getDbo();
		$localUsernameChecks = array(strstr($email, '@', true), $email);

		$query = $db->getQuery(true)
			->select('id, activation, username, email, block')
			->from('#__users')
			->where('username IN(' . implode(',', array_map(array($db, 'quote'), $localUsernameChecks)) . ')'
				. ' OR email = ' . $db->quote($email)
			);

		$db->setQuery($query);

		if ($localUsers = $db->loadObjectList())
		{
			foreach ($localUsers as $localUser)
			{
				// Local user exists with same username but different email address
				if ($email !== $localUser->email)
				{
					$response->status        = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', JText::_('PLG_GMAIL_ERROR_LOCAL_USERNAME_CONFLICT'));

					return;
				}
				else
				{
					// Existing user disabled locally
					if ($localUser->block || !empty($localUser->activation))
					{
						$response->status        = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');

						return;
					}

					// We will always keep the local username for existing accounts
					$credentials['username'] = $localUser->username;

					break;
				}
			}
		}
		elseif (JFactory::getApplication()->isClient('administrator'))
		{
			// We wont' allow backend access without local account
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JERROR_LOGIN_DENIED');

			return;
		}

		$response->status        = JAuthentication::STATUS_SUCCESS;
		$response->error_message = '';
		$response->email         = $email;

		// Reset the username to what we ended up using
		$response->username = $credentials['username'];
		$response->fullname = $credentials['username'];
	}
}
