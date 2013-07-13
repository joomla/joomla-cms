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

	/*
	 * Remember me method to run onAfterInitialise
	 *
	 * @throws  InvalidArgumentException
	 */
	public function onAfterInitialise()
	{

		$user = JFactory::getUser();

		// Check for a cookie
		if ($user->get('guest') == 1)
		{
			$app = JFactory::getApplication();

			// No remember me for admin
			if ($app->isAdmin())
			{
				return;
			}

			JLoader::register('JAuthentication', JPATH_PLATFORM . '/joomla/user/authentication.php');

			// Create the cookie name and data
			$rememberArray = $this->getRememberCookieData();

			if (!empty($rememberArray) && !empty($rememberArray[1]) && !empty($rememberArray[2]))
			{
				$this->getCookieConfig();

				// We're going to clear out expired tokens very time someone logs in with remember me.
				$nowtime = time();

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->delete('#__user_keys');
				$query->where($db->quoteName('time') . ' < ' . $db->quote($nowtime));
				$db->setQuery($query);
				$db->execute();

				$query->clear();

				$credentials = array();

				//Find the matching record if it exists
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quotename(array('user_id', 'token', 'series', 'time', 'invalid')))
					->where($db->quoteName('series') . ' = ' . $db->quote(base64_encode($rememberArray[1])))
					->where($db->quoteName('uastring') . ' = ' . $db->quote($rememberArray[2]))
					->from($db->quoteName('#__user_keys'));
				$db->setQuery($query);
				$results = $db->loadObjectList();

				$countResults = count($results);

				// We have a cookie but it's not in the database or the cookie is invalid. Possible attack, invalidate every thing.
				if ($countResults == 0 || !$results || $results[0]->invalid != 0)
				{
					//Should this start by throwing an exception?
					// We can only invalidate if there is a user.
					if (!empty($results[0]->user_id))
					{
						$this->invalidateCookie($results[0]->user_id, $rememberArray[2], $this->cookie_path, $this->cookie_domain);
						JLog::add('The remember me tokens were invalidated for user ' . $user->username  . ' because there was no matching record ', JLog::WARNING, 'security');

						// Possibly e-mail user and admin here.
						return false;
					}
				}

				JLoader::register('JAuthentication', JPATH_ROOT . '/libraries/joomla/user/authentication.php');

				if ($countResults == 1)
				{
						// Now we have a user with one cookie with a valid series and a corresponding record in the database.
						// Now check the key
						if (substr($results[0]->token, 0, 4) == '$2y$')
						{

							if (JUserHelper::hasStrongPasswords())
							{
								$match = password_verify($rememberArray[0], $results[0]->token);
							}
						}
						else
						{
							$parts	= explode(':', $results[0]->token);
							$crypt	= $parts[0];
							$salt	= @$parts[1];

							$testcrypt = JUserHelper::getCryptedPassword($rememberArray[0], $salt, 'md5-hex', false);

							if ($crypt == $testcrypt)
							{
								$match = true;
							}
						}

						if (!$match)
						{
							JLog::add('Remember me login failed for user ' . $user->username , JLog::WARNING, 'security');
							$this->invalidateCookie($results[0]->user_id, $cookieValue, $this->cookie_path, $this->cookie_domain);

							return false;
						}

					// Set up the credentials array
					$credentials['username'] = $results[0]->user_id;

					$return = $app->login($credentials, array('silent' => true));
				}
			}
		}
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

		$response->type = 'Remember';

		// Get a database object and make sure there really is a user with this name
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('id, username, password')
			->from('#__users')
			->where('username=' . $db->quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			// Bring this in line with the rest of the system
			$user = JUser::getInstance($result->id);
			$series = $this->getShortHashedUserAgent();
			$inputCookie = new JInputCookie();

			// If there is no cookie, bail out
			if (!$inputCookie->get($series))
			{
				return;
			}

			$user->set('rememberLogin', true);

			$response->username = $result->username;

			$response->email = $user->email;
			$response->fullname = $user->name;
			$response->password = $result->password;
			$response->language = $user->getParam('language');

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

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('series'))
				->from($db->quoteName('#__user_keys'))
				->where($db->quoteName('series') . ' = ' . $db->quote(base64_encode($series)));
			$db->setQuery($query);
			$results = $db->loadResult();
			$query->clear();

			if (is_null($results))
				{
					$unique = true;
			}
		}
		while ($unique == false);

		$user = JFactory::getUser();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// If a user logs in with non remember login and remember me checked we will delete any invalid entries so that
		// they can use remember once again.
		if ($options['responseType'] != 'Remember')
		{

			$query->delete('#__user_keys');
			$query->where($db->quoteName('uastring') . ' = ' . $db->quote($cookieName))
				->where($db->quoteName('user_id') . ' = ' . $db->quote($user->username));
			$db->setQuery($query);
			$db->execute();
			$query->clear();
		}

		$cookieValue =  $privateKey . '.' . $series . '.' . $cookieName;

		// Use domain and path set in config for cookie if it exists.
		$this->getCookieConfig();

		// Destroy the old cookie.
		setcookie($cookieName, false, time() - 42000, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		// And make a new one.
		setcookie($cookieName, $cookieValue, $this->lifetime, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		if (empty($user->rememberLogin))
		{
			// For users doing login from Joomla or other systems
			$query->insert($db->quoteName('#__user_keys'));
		}
		else
		{
			$query->update($db->quoteName('#__user_keys'))
				->where($db->quote($user->username) . ' = ' . $db->quoteName('user_id')
					. ' AND ' .  $db->quote(base64_encode($rememberArray[1])) . ' = ' . $db->quoteName('series')
					. ' AND ' . $db->quote($cookieName) . ' = ' . $db->quoteName('uastring'));
		}

		$query->set($db->quoteName('user_id') . ' = ' . $db->quote($user->username))
			->set($db->quoteName('time') . ' = ' . $this->lifetime)
			->set($db->quoteName('token') . ' = ' . $db->quote($cryptedKey))
			->set($db->quoteName('series') . ' = ' . $db->quote(base64_encode($series)))
			->set($db->quoteName('invalid') . ' = 0')
			->set($db->quoteName('uastring') . ' = ' . $db->quote($cookieName));
		$db->setQuery($query);

		$db->execute();

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
		if (empty($rememberArray) || empty($rememberArray[2]))
		{
			return true;
		}

		// Use domain and path set in config for cookie if it exists.
		$this->getCookieConfig();

		// Remove the record from the database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$db->setQuery($query);
		$query->delete('#__user_keys')
			->where($db->quoteName('uastring') . ' = ' . $db->quote($rememberArray[2]))
			->where($db->quoteName('series') . ' = ' . $db->quote(base64_encode($rememberArray[1])))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($options['username']));
		$db->setQuery($query);
		$db->execute();
		$query->clear();

		// Destroy the cookie
		setcookie($rememberArray[2], false, time() - 42000, $this->cookie_path, $this->cookie_domain, $this->secure, true);

		return;
	}

	/**
	 * Method to remove a cookie record from the database and the browser
	 *
	 * @param   string   $username       Username for this user
	 * @param   string   $series         Series id (cookie name decoded)
	 * @param   string   $cookie_path    Cookie path from configuration
	 * @param   string   $cookie_domain  Cookie domain from configuration
	 * @param   boolean  $secure         Use https only if true
	 * @param   boolean  $httponly       If true cookie is only accessible from http.
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 * @see JInput::setCookie for more details
	 */
	protected function invalidateCookie($username, $cookieName, $cookie_path, $cookie_domain, $secure = false, $httponly = true)
	{
		// Invalidate cookie in the database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__user_keys'))
		->set($db->quoteName('invalid') . '= ' . 1)
		->where($db->quotename('user_id') . ' = ' . $db->quote($username));
		$db->setQuery($query);
		$db->execute();

		// Destroy the  cookie.
		setcookie($cookieName, false, time() - 42000, $cookie_path, $cookie_domain, $secure, true);
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
		$app = JFactory::getApplication();
		$this->cookie_domain = $app->getCfg('cookie_domain', '');
		$this->cookie_path = $app->getCfg('cookie_path', '/');
		$this->lifetime = time() + ($this->params->get('cookie_lifetime', '60') * 24 * 60 * 60);
		$this->secure = $app->isSSLConnection();

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

		return md5(JPATH_BASE . $uaShort);
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

		$inputCookie = new JInputCookie();
		$cookieValue = $inputCookie->get($cookieName);

		if (!empty($cookieValue))
		{
			return explode('.', $cookieValue);
		}
		else
		{
			return false;
		}
	}
}
