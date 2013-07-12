<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.gmail
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * GMail Authentication Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.gmail
 * @since       1.5
 */
class PlgAuthenticationGMail extends JPlugin
{
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		$success = 0;

		// Check if we have curl or not
		if (function_exists('curl_init'))
		{
			// Check if we have a username and password
			if (strlen($credentials['username']) && strlen($credentials['password']))
			{
				$blacklist = explode(',', $this->params->get('user_blacklist', ''));

				// Check if the username isn't blacklisted
				if (!in_array($credentials['username'], $blacklist))
				{
					$suffix = $this->params->get('suffix', '');
					$applysuffix = $this->params->get('applysuffix', 0);
					$offset = strpos($credentials['username'], '@');

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

					$curl = curl_init('https://mail.google.com/mail/feed/atom');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->params->get('verifypeer', 1));
					//curl_setopt($curl, CURLOPT_HEADER, 1);
					curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($curl, CURLOPT_USERPWD, $credentials['username'] . ':' . $credentials['password']);
					curl_exec($curl);
					$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

					switch ($code)
					{
						case 200:
							$message = JText::_('JGLOBAL_AUTH_ACCESS_GRANTED');
							$success = 1;
							break;

						case 401:
							$message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
							break;

						default:
							$message = JText::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');
							break;
					}
				}
				else
				{
					// The username is black listed
					$message = 'User is blacklisted';
				}
			}
			else
			{
				$message = JText::_('JGLOBAL_AUTH_USER_BLACKLISTED');
			}
		}
		else
		{
			$message = 'curl isn\'t insalled';
		}

		$response->type = 'GMail';

		if ($success)
		{
			$response->status		= JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';

			if (strpos($credentials['username'], '@') === false)
			{
				if ($suffix)
				{
					// If there is a suffix then we want to apply it
					$response->email = $credentials['username'] . '@' . $suffix;
				}
				else
				{
					// If there isn't a suffix just use the default gmail one
					$response->email = $credentials['username'] . '@gmail.com';
				}
			}
			else
			{
				// The username looks like an email address (probably is) so use that
				$response->email = $credentials['username'];
			}

			// Reset the username to what we ended up using
			$response->username = $credentials['username'];
			$response->fullname = $credentials['username'];
		}
		else
		{
			$response->status        = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', $message);
		}
	}
}
