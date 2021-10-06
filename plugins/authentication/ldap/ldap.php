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
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Registry\Registry;
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
	* Load the language file on instantiation.
	*
	* @var    boolean
	* @since  3.1
	*/
	protected $autoloadLanguage = true;

	/**
	* Hold LDAP response
	*
	* @var    object
	* @since  4.0
	*/
	private $response;

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
		// ----- VARS
		$response->type  = 'LDAP'; // For JLog
		$user_name       = ''; // user name from login form
		$user_domain     = ''; // user domain from login form
		$bind_dn         = ''; // DN for LDAP bind
		$bind_pwd        = ''; // password for LDAP bind
		$entry           = false; // LDAP search result

		// Get params
		$bind_method       = $this->params->get('bind_method', 'direct', 'cmd');
		$bind_search_dn    = $this->params->get('bind_search_dn', '');
		$bind_search_pwd   = $this->params->get('bind_search_pwd', '');
		$bind_allow_direct = $this->params->get('bind_allow_direct', '0', 'int');
		$bind_dn_suffix    = $this->params->get('bind_dn_suffix', '');
		$bind_domains      = $this->params->get('bind_domains', '');
		$base_dn           = $this->params->get('base_dn', '');

		// LDAP to Joomla attributes
		$fullname_scheme   = $this->params->get('fullname_scheme', '[displayName]');
		$attr_fullname     = array();
		$attr_uid          = $this->params->get('attr_uid', 'sAMAccountName');
		$attr_email        = $this->params->get('attr_email', 'mail');


		// ----- BASIC CHECK AND CORRECT
		// If LDAP not correctly configured then bail early
		// Symfony use localhost if host is empty but this can be incorrect as well
		if (!$this->params->get('conn_host'))
		{
			return false;
		}

		// Check username
		if (empty($credentials['username']))
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('PLG_LDAP_ERROR_EMPTY_USERNAME_NOT_ALLOWED');

			return false;
		}

		// Strip null bytes from the password
		$credentials['password'] = str_replace(chr(0), '', $credentials['password']);

		// LDAP does not like Blank passwords (tries to Anon Bind which is bad)
		if (empty($credentials['password']))
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_EMPTY_PASS_NOT_ALLOWED');

			return false;
		}

		// Check auth method
		if (!in_array($bind_method, array('direct', 'search')))
		{
			// Unsupported auth method
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('PLG_LDAP_ERROR_UNSUPPORTED_AUTH_METHOD');

			return false;
		}

		// ----- GET LDAP ATTRIBUTES
		// from full name scheme
		preg_match_all("/\[([^\]]*)\]/", $fullname_scheme, $attr_fullname);


		// ----- CONNECT TO LDAP
		$ldap = Ldap::create('ext_ldap', [
			'host'       => $this->params->get('conn_host', 'localhost', 'string'),
			'port'       => $this->params->get('conn_port', '0', 'int'),
			'encryption' => $this->params->get('conn_encryption', 'none', 'cmd'),
			'version'    => $this->params->get('conn_version', '3', 'int'),
			'referrals'  => (bool) $this->params->get('conn_referrals', '0', 'int'),
			'debug'      => (bool) $this->params->get('ldap_debug', '0', 'int'),
			//'options'    => [],
		]);

		// ----- GET USER NAME / DOMAIN
		// Possible: domain\username, username@domain or just username or DN
		// Get user name and user domain
		if (strpos($credentials['username'], '\\') !== false)
		{
			// domain\username
			list($user_domain, $user_name) = explode('\\', $credentials['username']);

		}
		else if (strpos($credentials['username'], '@') !== false)
		{
			// username@domain
			list($user_name, $user_domain) = explode('@', $credentials['username']);

		}
		else
		{
			// Username or DN; store as username
			$user_name = $credentials['username'];

		}


		// ----- SEARCH METHOD CHECKS
		if ($bind_method == 'search')
		{
			// if bind DN or pwd is empty then alow direct bind so that users can still login and correct it
			if (!$bind_search_dn || !$bind_search_pwd)
			{
				$bind_allow_direct = 1;
			}

			// check allowed direct bind
			// if direct bind is allowed and is user domain or user name is DN
			if ($bind_allow_direct && ($user_domain || strpos($user_name, 'dc=') !== false))
			{
				$bind_method = 'direct';
			}
		}


		// ----- SET BIND DN AND PASSWORD
		if ($bind_method == 'direct')
		{
			// Assign only username
			$bind_dn = $user_name;

			// If the user has provided a domain, add it
			if ($user_domain)
			{
				// trim allowed bind domains
				$bind_domains = trim($bind_domains);

				// check allowed domains if not empty
				if ($bind_domains)
				{
					// explode to array
					$bind_domains = (array) explode(';', $bind_domains);

					// add default domain if not already in allowed domains array
					if (!in_array($bind_dn_suffix, $bind_domains))
					{
						array_push($bind_domains, $bind_dn_suffix);
					}

					// check if given domain is allowed domain; if not then end with error
					if (!in_array($user_domain, $bind_domains))
					{
						// Unsupported auth method
						$response->status = Authentication::STATUS_FAILURE;
						$response->error_message = Text::_('PLG_LDAP_ERROR_NOT_ALLOWED_DOMAIN');

						return false;
					}
				}

				// add user domain do bind DN
				$bind_dn .= '@'. $user_domain;
			}
			// Else add default domain if not empty
			else if ($bind_dn_suffix)
			{
				$bind_dn .= '@'. $bind_dn_suffix;
			}

			// Assign values for binding
			$bind_dn  = $ldap->escape($bind_dn, '', LDAP_ESCAPE_DN);
			$bind_pwd = $credentials['password'];

		}
		else if ($bind_method == 'search')
		{
			// Assign values for binding
			$bind_dn  = $ldap->escape($bind_search_dn, '', LDAP_ESCAPE_DN);
			$bind_pwd = $bind_search_pwd;

		}


		// ----- BIND
		// Try to bind
		try
		{
			$ldap->bind($bind_dn, $bind_pwd);
		}
		catch (ConnectionException $exception)
		{
			$response->status = Authentication::STATUS_FAILURE;
			// Exception can be: LDAP_INVALID_CREDENTIALS, LDAP_TIMEOUT or LDAP_ALREADY_EXISTS
			// For most common error LDAP_INVALID_CREDENTIALS display Joomla! message
			// For others display Symfony message
			if ($exception->getCode() == 0)
			{
				$response->error_message = Text::_('JGLOBAL_AUTH_NOT_CONNECT');
			}
			else
			{
				$response->error_message = $exception->getMessage();
			}

			return false;
		}

		// ----- SEARCH
		// Bind succesfull; prepare search

		// -- Limit attributes returned by search
		// first add LDAP attributes from full name scheme
		$search_attributes = $attr_fullname[1];
		// add uid and email attributes
		array_push($search_attributes, $attr_uid, $attr_email);

		// -- Prepare symfony LDAP query params
		$search_options = array(
			'filter' => $search_attributes, // attributes returned by LDAP; DN is always returned
			//'attrsOnly' => 0, // attributes_only; 1 - return only attribute types; 0 - return types and values
			'maxItems' => 1, // symfony maxItems; only one (or first)
			//'sizeLimit' => 0, // PHP param but not used in symfony
			//'pageSize' => 0, // symfony page size limit for pagination
			//'timeout' => 0, // timelimit
			//'deref' => 0, // alias handling
			//'scope' => 'sub', // symfony search scope
		);

		// -- Prepare search string
		$search_strings = str_replace(
			'[search]',
			// replace ; for \3b in username. Seach strings are separated with ;
			str_replace(
				';',
				'\3b',
				// escape username for filtering
				$ldap->escape($user_name, '', LDAP_ESCAPE_FILTER)
			),
			$this->params->get('search_strings')
		);

		// -- Try to find user
		try
		{
			// Search for user; return the first entry from the first search result which contains data
			foreach (explode(';', $search_strings) as $key => $search_string)
			{
				// prepare search string
				$search_string = str_replace('\3b', ';', $search_string);

				// prepare search query and execute
				$results = $ldap->query($base_dn, $search_string, $search_options)->execute();

				if (count($results))
				{
					$entry = $results[0];
					continue;
				}
			}
		}
		catch (LdapException $exception)
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_UNKNOWN_ACCESS_DENIED');

			return false;
		}

		// If search wasnt able to find authenticating username
		if (!$entry)
		{
			$response->status = Authentication::STATUS_FAILURE;
			$response->error_message = Text::_('JGLOBAL_AUTH_NOT_CONNECT');

			return false;
		}

		// ----- AUTH IN SEARCH METHOD
		// If search method then user was just found, now need to be authenticated
		if ($bind_method == 'search')
		{
			// Try to bind as authenticating user
			try
			{
				$ldap->bind($entry->getDn(), $credentials['password']);
			}
			catch (ConnectionException $exception)
			{
				$response->status = Authentication::STATUS_FAILURE;
				$response->error_message = Text::_('JGLOBAL_AUTH_INVALID_PASS');

				return false;
			}
		}

		// ----- PREPARE DATA FOR JOOMLA!
		// Prepare data from LDAP attributes for Joomla! user system

		// -- Full name
		// Get full name scheme
		$full_name = $fullname_scheme;

		// Replace LDAP attributes with its values
		if (!empty($attr_fullname))
		{
			foreach ($attr_fullname[1] as $key => $ldap_attr)
			{
				// get value for
				$attr_value = trim($entry->getAttribute($ldap_attr)[0]) ?? '';

				// replace
				$full_name = str_replace($attr_fullname[0][$key], $attr_value, $full_name);

			}
		}

		// remove more than one space
		$full_name = preg_replace('/\s+/', ' ', $full_name);

		// Assign full name
		$response->fullname = $full_name;

		// -- User name (login)
		$response->username = $entry->getAttribute($attr_uid)[0] ?? trim($entry->getAttribute($attr_uid)[0]) ?: trim($user_name);

		// -- Email address
		$response->email = trim($entry->getAttribute($attr_email)[0]) ?? false;


		// ----- FINISH
		// Were good - So say so.
		$response->status        = Authentication::STATUS_SUCCESS;
		$response->error_message = '';

		// store response for futher usage
		$this->response = $response;

		// The connection is no longer needed, destroy the object to close it
		unset($ldap);

	}

	/**
	 * Check / update / set user data after successfull login
	 *
	 * @param   array  $options  Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.0
	 */
	public function onUserAfterLogin($options)
	{
		// Check if this is LDAP authentication; if not then return true (continue)
		if ($options['responseType'] != 'LDAP')
		{
			return true;
		}

		// ----- VARS
		$user   = Factory::getApplication()->getIdentity();
		$update = false;


		// ----- UPDATE USER DATA
		// update user data and save in database

		// check full name
		if ($user->name != $this->response->fullname)
		{
			$user->name = $this->response->fullname;
			$update = true;
		}

		// check email
		if ($user->email != $this->response->email)
		{
			$user->email = $this->response->email;
			$update = true;
		}

		// update if required
		if ($update == true)
		{
			// save in updateOnly method
			$user->save(true);
		}

		return true;
	}


	/**
	 * This method handle AJAX requests, eg. authentication tests
	 *
	 * @return  JSON
	 *
	 * @since   4.0
	 */
	public function onAjaxLdap()
	{
		// Check security token
		Session::checkToken('post') or die();

		// Vars
		$ajax = [
			'status' => '',
			'msg' => '',
		];

		// Create authentication response
		$response = new AuthenticationResponse;

		// get post input
		$jform = Factory::getApplication()->input->get('jform', array(), 'array');

		// create params regisrty
		$this->params = new Registry($jform['params']);

		// Set credentials for test
		$credentials = [
			'username' => $this->params->get('test_username', ''),
			'password' => $this->params->get('test_password', ''),
		];

		// Run onUserAuthenticate method and get response
		$this->onUserAuthenticate($credentials, array(), $response);

		// Set AJAX response
		// Status
		$ajax['status'] = $response->status;

		// Message
		if ($ajax['status'] == 1)
		{
			// success
			$ajax['hdr'] = Text::_('PLG_LDAP_FIELD_TEST_STATE_SUCCESS');
			$ajax['msg'] = $response->fullname .'<br/>';
			$ajax['msg'] .= $response->username .'<br/>';
			$ajax['msg'] .= $response->email .'<br/>';
		}
		else if ($ajax['status'] == 4)
		{
			// error
			$ajax['hdr'] = Text::_('PLG_LDAP_FIELD_TEST_STATE_ERROR');
			$ajax['msg'] = $response->error_message;
		}
		else
		{
			// unknown status
			$ajax['hdr'] = Text::_('PLG_LDAP_FIELD_TEST_STATE_ERROR');
			$ajax['msg'] = Text::_('PLG_LDAP_FIELD_TEST_UNKNOWN_ERROR');
		}

		// To JSON
		echo json_encode($ajax);

		// exit
		jexit();
	}
}
