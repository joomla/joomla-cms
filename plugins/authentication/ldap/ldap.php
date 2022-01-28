<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.ldap
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Symfony\Component\Ldap\Entry;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Symfony\Component\Ldap\Ldap;

/**
 * LDAP Authentication Plugin
 *
 * @since  1.5
 */
class PlgAuthenticationLdap extends CMSPlugin
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
		// If LDAP not correctly configured then bail early.
		if (!$this->params->get('host'))
		{
			return false;
		}

		// For JLog
		$response->type = 'LDAP';

		// Strip null bytes from the password
		$credentials['password'] = str_replace(chr(0), '', $credentials['password']);

		// LDAP does not like Blank passwords (tries to Anon Bind which is bad)
		if (empty($credentials['password']))
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

			return false;
		}

		// Load plugin params info
		$ldap_email    = $this->params->get('ldap_email');
		$ldap_fullname = $this->params->get('ldap_fullname');
		$ldap_uid      = $this->params->get('ldap_uid');
		$auth_method   = $this->params->get('auth_method');

		$ldap = Ldap::create(
			'ext_ldap',
			[
				'host'       => $this->params->get('host'),
				'port'       => (int) $this->params->get('port'),
				'version'    => $this->params->get('use_ldapV3', '0') == '1' ? 3 : 2,
				'referrals'  => (bool) $this->params->get('no_referrals', '0'),
				'encryption' => $this->params->get('negotiate_tls', '0') == '1' ? 'tls' : 'none',
			]
		);

		switch ($auth_method)
		{
			case 'search':
			{
				try
				{
					$dn = str_replace('[username]', $this->params->get('username', ''), $this->params->get('users_dn', ''));

					$ldap->bind($dn, $this->params->get('password', ''));
				}
				catch (ConnectionException | LdapException $exception)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_NOT_CONNECT');

					return;
				}

				// Search for users DN
				try
				{
					$entry = $this->searchByString(
						str_replace(
							'[search]',
							str_replace(';', '\3b', $ldap->escape($credentials['username'], '', LDAP_ESCAPE_FILTER)),
							$this->params->get('search_string')
						),
						$ldap
					);
				}
				catch (LdapException $exception)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');

					return;
				}

				if (!$entry)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_NOT_CONNECT');

					return;
				}

				try
				{
					// Verify Users Credentials
					$ldap->bind($entry->getDn(), $credentials['password']);
				}
				catch (ConnectionException $exception)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');

					return;
				}

				break;
			}

			case 'bind':
			{
				// We just accept the result here
				try
				{
					$ldap->bind($ldap->escape($credentials['username'], '', LDAP_ESCAPE_DN), $credentials['password']);
				}
				catch (ConnectionException | LdapException $exception)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');

					return;
				}

				try
				{
					$entry = $this->searchByString(
						str_replace(
							'[search]',
							str_replace(';', '\3b', $ldap->escape($credentials['username'], '', LDAP_ESCAPE_FILTER)),
							$this->params->get('search_string')
						),
						$ldap
					);
				}
				catch (LdapException $exception)
				{
					$response->status = Authentication::STATUS_FAILURE;
					$response->error_message = Text::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');

					return;
				}

				break;
			}

			default:
				// Unsupported configuration
				$response->status = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');

				return;
		}

		// Grab some details from LDAP and return them
		$response->username = $entry->getAttribute($ldap_uid)[0] ?? false;
		$response->email    = $entry->getAttribute($ldap_email)[0] ?? false;
		$response->fullname = $entry->getAttribute($ldap_fullname)[0] ?? trim($entry->getAttribute($ldap_fullname)[0]) ?: $credentials['username'];

		// Were good - So say so.
		$response->status        = Authentication::STATUS_SUCCESS;
		$response->error_message = '';

		// The connection is no longer needed, destroy the object to close it
		unset($ldap);
	}

	/**
	 * Shortcut method to perform a LDAP search based on a semicolon separated string
	 *
	 * Note that this method requires that semicolons which should be part of the search term to be escaped
	 * to correctly split the search string into separate lookups
	 *
	 * @param   string  $search  search string of search values
	 * @param   Ldap    $ldap    The LDAP client
	 *
	 * @return  Entry|null The search result entry if a matching record was found
	 *
	 * @since   3.8.2
	 */
	private function searchByString($search, Ldap $ldap)
	{
		$dn = $this->params->get('base_dn');

		// We return the first entry from the first search result which contains data
		foreach (explode(';', $search) as $key => $result)
		{
			$results = $ldap->query($dn, '(' . str_replace('\3b', ';', $result) . ')')->execute();

			if (count($results))
			{
				return $results[0];
			}
		}
	}
}
