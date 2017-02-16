<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * LDAP Authentication Plugin
 *
 * @since  1.5
 */
class PlgAuthenticationLdap extends JPlugin
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
		$userdetails = null;
		$success = 0;
		$userdetails = array();

		// For JLog
		$response->type = 'LDAP';

		// Strip null bytes from the password
		$credentials['password'] = str_replace(chr(0), '', $credentials['password']);

		// LDAP does not like Blank passwords (tries to Anon Bind which is bad)
		if (empty($credentials['password']))
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_PASS_BLANK');

			return false;
		}

		// Load plugin params info
		$ldap_email    = $this->params->get('ldap_email');
		$ldap_fullname = $this->params->get('ldap_fullname');
		$ldap_uid      = $this->params->get('ldap_uid');
		$auth_method   = $this->params->get('auth_method');

		$ldap = new JClientLdap($this->params);

		if (!$ldap->connect())
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_CONNECT');

			return;
		}

		switch ($auth_method)
		{
			case 'search':
			{
				// Bind using Connect Username/password
				// Force anon bind to mitigate misconfiguration like [#7119]
				if (strlen($this->params->get('username')))
				{
					$bindtest = $ldap->bind();
				}
				else
				{
					$bindtest = $ldap->anonymous_bind();
				}

				if ($bindtest)
				{
					// Search for users DN
					$binddata = $ldap->simple_search(str_replace('[search]', $credentials['username'], $this->params->get('search_string')));

					if (isset($binddata[0]) && isset($binddata[0]['dn']))
					{
						// Verify Users Credentials
						$success = $ldap->bind($binddata[0]['dn'], $credentials['password'], 1);

						// Get users details
						$userdetails = $binddata;
					}
					else
					{
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_USER_NOT_FOUND');
					}
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_NO_BIND');
				}
			}	break;

			case 'bind':
			{
				// We just accept the result here
				$success = $ldap->bind($credentials['username'], $credentials['password']);

				if ($success)
				{
					$userdetails = $ldap->simple_search(str_replace('[search]', $credentials['username'], $this->params->get('search_string')));
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_BIND_FAILED');
				}
			}	break;
		}

		if (!$success)
		{
			$response->status = JAuthentication::STATUS_FAILURE;

			if (!strlen($response->error_message))
			{
				$response->error_message = JText::_('JGLOBAL_AUTH_INCORRECT');
			}
		}
		else
		{
			// Grab some details from LDAP and return them
			if (isset($userdetails[0][$ldap_uid][0]))
			{
				$response->username = $userdetails[0][$ldap_uid][0];
			}

			if (isset($userdetails[0][$ldap_email][0]))
			{
				$response->email = $userdetails[0][$ldap_email][0];
			}

			if (isset($userdetails[0][$ldap_fullname][0]))
			{
				$response->fullname = $userdetails[0][$ldap_fullname][0];
			}
			else
			{
				$response->fullname = $credentials['username'];
			}

			// Were good - So say so.
			$response->status        = JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';
		}

		$ldap->close();
	}
}
