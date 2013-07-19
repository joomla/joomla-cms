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
 * @note  Code improvements inspired by
 * http://jaspan.com/improved_persistent_login_cookie_best_practice
 * http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
 */
class PlgSystemRemember extends JPlugin
{
	/**
	 * @var    JApplication
	 *
	 * @since  3.1.2
	 */
	protected $app;

	/**
	 * @var    JApplication
	 *
	 * @since  3.1.2
	 */
	protected $db;

	/**
	 * @var    Domain for the cookie;
	 *
	 * @since  3.1.2
	 */
	protected $cookie_domain;

	/**
	 * @var    Path for the cookie.
	 *
	 * @since  3.1.2
	 */
	protected $cookie_path;

	/**
	 * @var    Whether to set as secure or not.
	 *
	 * @since  3.1.2
	 */
	protected $secure;

	/**
	 * @var    Cookie lifetime.
	 *
	 * @since  3.1.2
	 */
	protected $lifetime;

	/**
	 * Constructor. We use it to set the app and db properties.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   11.1
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->app = JFactory::getApplication();
		$this->db = JFactory::getDbo();
	}

	/*
	 * Remember me method to run onAfterInitialise
	 *
	 * @throws  InvalidArgumentException
	 */
	public function onAfterInitialise()
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return;
		}

		$user = JFactory::getUser();

		// Check for a cookie
		if ($user->get('guest') == 1)
		{
			// Create the cookie name and data
			$rememberArray = $this->getRememberCookieData();

			if ($rememberArray !== false)
			{
				list($privateKey, $series, $uastring) = $rememberArray;

				$this->getCookieConfig();
				$this->clearExpiredTokens();

				// Find the matching record if it exists
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName(array('user_id', 'token', 'series', 'time', 'invalid')))
					->from($this->db->quoteName('#__user_keys'))
					->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
					->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($uastring))
					->order($this->db->quoteName('time') . ' DESC');

				$results = $this->db->setQuery($query)->loadObjectList();

				$countResults = count($results);

				// We have a cookie that's not in the database, or it's invalid. This is a possible attack, so invalidate everything.
				if ($countResults === 0 || $results[0]->invalid != 0)
				{
					// We can only invalidate if there is a user.
					if (!empty($results[0]->user_id))
					{
						$this->invalidateCookie($results[0]->user_id, $uastring);
						JLog::add('The remember me tokens were invalidated for user ' . $user->username  . ' because there was no matching record ', JLog::WARNING, 'security');

						// Possibly e-mail user and admin here.
						return false;
					}
				}

				// We have a user with one cookie with a valid series and a corresponding record in the database.
				if ($countResults === 1)
				{
					if (substr($results[0]->token, 0, 4) === '$2y$')
					{
						if (JUserHelper::hasStrongPasswords())
						{
							$match = password_verify($privateKey, $results[0]->token);
						}
					}
					else
					{
						$parts	= explode(':', $results[0]->token);
						$crypt	= isset($parts[0]) ? $parts[0] : null;
						$salt	= isset($parts[1]) ? $parts[1] : null;

						$testcrypt = JUserHelper::getCryptedPassword($series, $salt, 'md5-hex', false);

						// We should probably add the timing safe compare here.
						if ($crypt === $testcrypt)
						{
							$match = true;
						}
					}

					if (!$match)
					{
						$this->invalidateCookie($results[0]->user_id, $uastring);
						JLog::add('Remember me login failed for user ' . $user->username , JLog::WARNING, 'security');

						return false;
					}

					// Set up the credentials array to pass to onUserAuthenticate
					$credentials = array(
						'username' => $results[0]->user_id
					);

					return $this->app->login($credentials, array('silent' => true));
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
	 * @since   3.1.2
	 */
	public function onUserAuthenticate($credentials, $options, &$response)
	{
		JLoader::register('JAuthentication', JPATH_LIBRARIES . '/joomla/user/authentication.php');

		$response->type = 'Remember';

		// Get a database object and make sure there really is a user with this name
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'username', 'password')))
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('username') . ' = ' . $this->db->quote($credentials['username']));

		$result = $this->db->setQuery($query)->loadObject();

		if ($result)
		{
			// Bring this in line with the rest of the system
			$user = JUser::getInstance($result->id);
			$series = $this->getShortHashedUserAgent();

			// If there is no cookie, bail out
			if (!$this->app->input->cookie->get($series))
			{
				return;
			}

			$user->set('rememberLogin', true);

			// Set response data.
			$response->username = $result->username;
			$response->email = $user->email;
			$response->fullname = $user->name;
			$response->password = $result->password;
			$response->language = $user->getParam('language');

			// Set response status.
			$response->status = JAuthentication::STATUS_SUCCESS;
			$response->error_message = '';
		}
		else
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}

	/**
	 * This is where we set the remember me cookie. We set a new cookie either for a user with no cookies or one
	 * where the user used a cookie to authenticate.
	 *
	 * @param   array  options     Array holding options
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 */
	public function onUserAfterLogin($options)
	{
		// No remember me for the admin user.
		$user = JFactory::getUser();

		if ($user->get('isRoot'))
		{
			return;
		}

		// We need the old data to match against the current database
		$rememberArray = $this->getRememberCookieData();
		$length = $this->params->get('key_length', '20');

		$privateKey = JUserHelper::genRandomPassword($length);

		// We are going to concatenate with . so we need to remove it from the strings.
		$privateKey = str_replace('.', '', $privateKey);

		$cryptedKey = JUserHelper::getCryptedPassword($privateKey, '', 'bcrypt', false);

		$cookieName = $this->getShortHashedUserAgent();

		// Create an identifier and make sure that it is unique.
		$unique = false;

		do
		{
			// Unique identifier for the device-user
			$series = JUserHelper::genRandomPassword(20);

			// We are going to concatenate with . so we need to remove it from the strings.
			$series = str_replace('.', '', $series);

			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('series'))
				->from($this->db->quoteName('#__user_keys'))
				->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)));

			$results = $this->db->setQuery($query)->loadResult();

			if (is_null($results))
			{
				$unique = true;
			}
		}
		while ($unique === false);

		// If a user logs in with non remember login and remember me checked we will
		// delete any invalid entries so that they can use remember once again.
		if ($options['responseType'] !== 'Remember')
		{
			$query = $this->db->getQuery(true)
				->delete('#__user_keys')
				->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->username));

			$this->db->setQuery($query)->execute();
		}

		$cookieValue = $privateKey . '.' . $series . '.' . $cookieName;

		// Use domain and path set in config for cookie if it exists.
		$this->getCookieConfig();

		// Destroy the old cookie.
		$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		// And make a new one.
		$this->app->input->cookie->set($cookieName, $cookieValue, $this->lifetime, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		$query = $this->db->getQuery(true);

		if (empty($user->rememberLogin))
		{
			// For users doing login from Joomla or other systems
			$query->insert($this->db->quoteName('#__user_keys'));
		}
		else
		{
			$query
				->update($this->db->quoteName('#__user_keys'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->username))
				->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($rememberArray[1])))
				->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName));
		}

		$query
			->set($this->db->quoteName('user_id') . ' = ' . $this->db->quote($user->username))
			->set($this->db->quoteName('time') . ' = ' . $this->lifetime)
			->set($this->db->quoteName('token') . ' = ' . $this->db->quote($cryptedKey))
			->set($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
			->set($this->db->quoteName('invalid') . ' = 0')
			->set($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName));

		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * This is where we delete remember the remember me cookie when a user logs out
	 *
	 * @param   array  options     Array holding options (length, timeToExpiration,)
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 */
	public function onUserAfterLogout($options)
	{
		$rememberArray = $this->getRememberCookieData();

		// There are no cookies to delete.
		if ($rememberArray === false)
		{
			return true;
		}

		list($privateKey, $series, $cookieName) = $rememberArray;

		// Use domain and path set in config for cookie if it exists.
		$this->getCookieConfig();

		// Remove the record from the database
		$query = $this->db->getQuery(true);

		$query
			->delete('#__user_keys')
			->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
			->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['username']));

		$this->db->setQuery($query)->execute();

		// Destroy the cookie
		$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		return true;
	}

	/**
	 * Method to remove a cookie record from the database and the browser
	 *
	 * @param   string   $userId      User ID for this user
	 * @param   string   $cookieName  Series id (cookie name decoded)
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 * @see JInput::setCookie for more details
	 */
	protected function invalidateCookie($userId, $cookieName)
	{
		// Invalidate cookie in the database
		$query = $this->db->getQuery(true);

		$query
			->update($this->db->quoteName('#__user_keys'))
			->set($this->db->quoteName('invalid') . '= 1')
			->where($this->db->quotename('user_id') . ' = ' . $this->db->quote($userId));

		$this->db->setQuery($query)->execute();

		// Destroy the cookie in the browser.
		$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, $this->secure, true);
	}
	/*
	 * Method to get the cookie configuration data
	 *
	 * @return  boolean
	 *
	 * @since   3.1.2
	 */
	protected function getCookieConfig()
	{
		// Use domain and path set in config for cookie if it exists.
		$this->cookie_domain = $this->app->getCfg('cookie_domain', '');
		$this->cookie_path = $this->app->getCfg('cookie_path', '/');
		$this->lifetime = time() + ($this->params->get('cookie_lifetime', '60') * 24 * 60 * 60);
		$this->secure = $this->app->isSSLConnection();

		return true;
	}

	/*
	 * Method to get a hashed user agent string that does not include browser version.
	 * Used when frequent version changes cause problems.
	 *
	 * @return  string  A hashed user agent string with version replaced by 'abcd'
	 *
	 * @since   3.1.2
	 */
	public function getShortHashedUserAgent()
	{
		$ua = new JApplicationWebClient;
		$uaString = $ua->userAgent;
		$browserVersion = $ua->browserVersion;
		$uaShort = str_replace($browserVersion, 'abcd', $uaString);

		return md5(JUri::base() . $uaShort);
	}

	/*
	 * Method to get the remember me cookie data
	*
	* @return  mixed  An array of information from the remember me cookie or false if there is no cookie
	*
	* @since   3.1.2
	*/
	protected function getRememberCookieData()
	{
		// Create the cookie name
		$cookieName = $this->getShortHashedUserAgent();

		// Fetch the cookie value
		$cookieValue = $this->app->input->cookie->get($cookieName);

		if (!empty($cookieValue))
		{
			return explode('.', $cookieValue);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Clear all expired tokens for all users.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	protected function clearExpiredTokens()
	{
		$now = time();

		$query = $this->db->getQuery(true)
			->delete('#__user_keys')
			->where($this->db->quoteName('time') . ' < ' . $this->db->quote($now));

		$this->db->setQuery($query)->execute();
	}
}
