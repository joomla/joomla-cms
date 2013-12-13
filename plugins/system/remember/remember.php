<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Remember Me Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 * @since       1.5
 * @note        Code improvements inspired by http://jaspan.com/improved_persistent_login_cookie_best_practice
 *              and http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
 */
class PlgSystemRemember extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.2
	 */
	protected $db;

	/**
	 * Domain for the cookie.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $cookie_domain;

	/**
	 * Path for the cookie.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $cookie_path;

	/**
	 * Whether to set as secure or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $secure = false;

	/**
	 * Cookie lifetime in days.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $lifetime;

	/**
	 * Length of random string.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $length;

	/**
	 * Constructor. We use it to set the app and db properties.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   3.2
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Use domain and path set in config for cookie if it exists.
		$this->cookie_domain = $this->app->get('cookie_domain', '');
		$this->cookie_path = $this->app->get('cookie_path', '/');
		$this->lifetime = time() + ($this->params->get('cookie_lifetime', '60') * 24 * 60 * 60);
		$this->secure = $this->app->isSSLConnection();
		$this->length = $this->params->get('key_length', '16');
	}

	/**
	 * Remember me method to run onAfterInitialise
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 * @throws  InvalidArgumentException
	 */
	public function onAfterInitialise()
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		$user = JFactory::getUser();

		// Check for a cookie
		if ($user->get('guest') == 1)
		{
			$cookieName = $this->getShortHashedUserAgent();
			$cookieValue = (string)$this->app->input->cookie->get($cookieName);
			
			if ($cookieValue != '')
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__users'))
					->where($this->db->quoteName('rememberme') . ' = ' . $this->db->quote($cookieValue));
				
				$this->db->setQuery($query);
				$id = (int)$this->db->loadResult();
				
				if ($id > 0)
				{
					// Set up the credentials array to pass to onUserAuthenticate
					$credentials = array(
						'username' => $id,
					);

					return $result = $this->app->login($credentials, array('silent' => true, 'lifetime' => $this->lifetime, 'secure' => $this->secure, 'length' => $this->length));
				}
				else
				{
					$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, false, true);
				}
			}
		}	

		return false;
	}
	
	/**
	 * This method should handle any authentication and report back to the subject
	 *
	 * @param   array   $credentials  Array holding the user credentials
	 * @param   array   $options      Array of extra options
	 * @param   object  &$response    Authentication response object
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		//RememberMe Login should only get the username and nothing else
		if (!(count($credentials) == 1 && isset($credentials['username'])) && !$this->app->input->cookie->get($cookieName))
		{
			return false;
		}

		$response->type = 'RememberMe';
		
		$cookieName = $this->getShortHashedUserAgent();
		$cookieValue = (string)$this->app->input->cookie->get($cookieName);
			
		if ($cookieValue != '')
		{
			$query = $this->db->getQuery(true);
			$query->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__users'))
				->where($this->db->quoteName('rememberme') . ' = ' . $this->db->quote($cookieValue));
				
			$this->db->setQuery($query);
			$id = (int)$this->db->loadResult();
				
			if ($id == $credentials['username'])
			{
				// Bring this in line with the rest of the system
				$user = JUser::getInstance($id);
				
				// Set response data.
				$response->username = $user->username;
				$response->email    = $user->email;
				$response->fullname = $user->name;
				$response->password = $user->password;
				$response->language = $user->getParam('language');

				// Set response status.
				$response->status        = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				$response->status        = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
			}
		}
	}
	
	public function onUserAfterLogin($options)
	{
		if($this->app->isSite() && isset($options['remember']) && $options['remember'])
		{
			$user = JFactory::getUser();
			
			if($user->rememberme == '')
			{
				$found = false;
				do
				{
					$key = JUserHelper::genRandomPassword(32);
					$query = $this->db->getQuery(true);
					$query->select($this->db->quoteName('id'))
						->from($this->db->quoteName('#__users'))
						->where($this->db->quoteName('rememberme') . ' = ' . $this->db->quote($key));
					$this->db->setQuery($query);
					$found = (bool) $this->db->loadResult();
				}
				while($found);

				$query->clear();
				$query->update($this->db->quoteName('#__users'))
					->set($this->db->quoteName('rememberme') . ' = ' . $this->db->quote($key))
					->where($this->db->quoteName('id') . ' = ' . $this->db->quote($user->id));
				
				$this->db->setQuery($query)->execute();
				
				$user->rememberme = $key;
			}

			$this->app->input->cookie->set($this->getShortHashedUserAgent(), $user->rememberme, $this->lifetime, $this->cookie_path, $this->cookie_domain, $this->secure);
		}
	}
	
	public function onUserLogout($parameters, $options)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return;
		}
		
		$cookieName = $this->getShortHashedUserAgent();
		$cookieValue = (string)$this->app->input->cookie->get($cookieName);
		
		if ($cookieValue != '')
		{
			$query = $this->db->getQuery(true);
			$query->update($this->db->quoteName('#__users'))
				->set($this->db->quoteName('rememberme') . ' = ' . $this->db->quote(''))
				->where($this->db->quoteName('id') . ' = ' . $this->db->quote($parameters['id']));
				
			$this->db->setQuery($query)->execute();
			
			$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, false, true);
		}
		
		return true;
	}
	
	protected function getShortHashedUserAgent()
	{
		$ua = JFactory::getApplication()->client;
		$uaString = $ua->userAgent;
		$browserVersion = $ua->browserVersion;
		$uaShort = str_replace($browserVersion, 'abcd', $uaString);

		return md5(JUri::base() . $uaShort);
	}
}
