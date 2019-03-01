<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Ldap\LdapClient;

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
			$response->error_message = JText::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

			return false;
		}

		// Load plugin params info
		$ldap_email    = $this->params->get('ldap_email');
		$ldap_fullname = $this->params->get('ldap_fullname');
		$ldap_uid      = $this->params->get('ldap_uid');
		$auth_method   = $this->params->get('auth_method');

		$ldap = new LdapClient($this->params);

		if (!$ldap->connect())
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NOT_CONNECT');

			return;
		}

		switch ($auth_method)
		{
			case 'search':
			{
				// Bind using Connect Username/password
				// Force anon bind to mitigate misconfiguration like [#7119]
				if ($this->params->get('username', '') !== '')
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
					$binddata = $this->searchByString(
						str_replace(
							'[search]',
							str_replace(';', '\3b', $ldap->escape($credentials['username'], null, LDAP_ESCAPE_FILTER)),
							$this->params->get('search_string')
						),
						$ldap
					);

					if (isset($binddata[0], $binddata[0]['dn']))
					{
						// Verify Users Credentials
						$success = $ldap->bind($binddata[0]['dn'], $credentials['password'], 1);

						// Get users details
						$userdetails = $binddata;
					}
					else
					{
						$response->status = JAuthentication::STATUS_FAILURE;
						$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
					}
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_NOT_CONNECT');
				}
			}	break;

			case 'bind':
			{
				// We just accept the result here
				$success = $ldap->bind($ldap->escape($credentials['username'], null, LDAP_ESCAPE_DN), $credentials['password']);

				if ($success)
				{
					$userdetails = $this->searchByString(
						str_replace(
							'[search]',
							str_replace(';', '\3b', $ldap->escape($credentials['username'], null, LDAP_ESCAPE_FILTER)),
							$this->params->get('search_string')
						),
						$ldap
					);
				}
				else
				{
					$response->status = JAuthentication::STATUS_FAILURE;
					$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
				}
			}	break;
		}

		if (!$success)
		{
			$response->status = JAuthentication::STATUS_FAILURE;

			if ($response->error_message === '')
			{
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_PASS');
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

	/**
	 * Shortcut method to build a LDAP search based on a semicolon separated string
	 *
	 * Note that this method requires that semicolons which should be part of the search term to be escaped
	 * to correctly split the search string into separate lookups
	 *
	 * @param   string      $search  search string of search values
	 * @param   LdapClient  $ldap    The LDAP client
	 *
	 * @return  array  Search results
	 *
	 * @since   3.8.2
	 */
	private function searchByString($search, LdapClient $ldap)
	{
		$results = explode(';', $search);

		foreach ($results as $key => $result)
		{
			$results[$key] = '(' . str_replace('\3b', ';', $result) . ')';
		}

		return $ldap->search($results);
	}
}
