<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla Authentication plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Authentication.cookie
 * @since       3.2
 * @note        Code based on http://jaspan.com/improved_persistent_login_cookie_best_practice
 *              and http://fishbowl.pastiche.org/2004/01/19/persistent_login_cookie_best_practice/
 */
class PlgAuthenticationCookie extends JPlugin
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

//		JLoader::register('JAuthentication', JPATH_LIBRARIES . '/joomla/user/authentication.php');

		$response->type = 'Cookie';

		// Get cookie name
		$cookieName = JUserHelper::getShortHashedUserAgent();

		// Check for a cookie
		$rememberArray = JUserHelper::getRememberCookieData();

		if (!$rememberArray)
		{
			return false;
		}
/* Alternative to JUserHelper::getRememberCookieData()
		if (!$this->app->input->cookie->get($cookieName))
		{
			return;
		} */

		// Check for valid cookie value
		if (count($rememberArray) != 3)
		{
			// Destroy the cookie in the browser.
			$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->app->get('cookie_path'), $this->app->get('cookie_domain'));
			JLog::add('Invalid cookie detected.', JLog::WARNING, 'error');

			return false;
		}

		list($privateKey, $series, $uastring) = $rememberArray;
//		list($user, $series, $token) = $rememberArray;

		// Remove expired tokens
		$query = $this->db->getQuery(true)
			->delete('#__user_keys')
			->where($this->db->quoteName('time') . ' < ' . $this->db->quote(time()));
		$this->db->setQuery($query)->execute();

		// Find the matching record if it exists.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('user_id', 'token', 'series', 'time')))
			->from($this->db->quoteName('#__user_keys'))
			->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
			->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($uastring))
			->order($this->db->quoteName('time') . ' DESC');

		$results = $this->db->setQuery($query)->loadObjectList();

		if (count($results) !== 1)
		{
			$response->status = JAuthentication::STATUS_FAILURE;

			return;
		}

		// We have a user with one cookie with a valid series and a corresponding record in the database.
		else
		{
			if ($results[0]->token != $privateKey)
			{
				// This is a real attack! Either the series was guessed correctly or a cookie was stolen and used twice (once by attacker and once by victim). 
				// Delete all tokens for this user!
				$query = $this->db->getQuery(true)
					->delete('#__user_keys')
					->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($results[0]->user_id));
				$this->db->setQuery($query)->execute();

				// Issue warning by email to user and/or admin?

				// Move language string to this plugin
				JLog::add(JText::sprintf('PLG_SYSTEM_REMEMBER_ERROR_LOG_LOGIN_FAILED', $results[0]->user_id), JLog::WARNING, 'security');

				$response->status  = JAuthentication::STATUS_FAILURE;

				return false;
			}
		}

		// Set cookie params.
		if (!empty($options['lifetime']) && !empty($options['length']) && !empty($options['secure']))
		{
			$response->lifetime = $options['lifetime'];
			$response->length = $options['length'];
			$response->secure = $options['secure'];
		}

		// Make sure there really is a user with this name and get the data for the session.
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('id', 'username', 'password')))
			->from($this->db->quoteName('#__users'))
			->where($this->db->quoteName('username') . ' = ' . $this->db->quote($results[0]->user_id));

		$result = $this->db->setQuery($query)->loadObject();

		if ($result)
		{
			// Bring this in line with the rest of the system
			$user = JUser::getInstance($result->id);

			// Set response data.
			$response->username = $result->username;
			$response->email    = $user->email;
			$response->fullname = $user->name;
			$response->password = $result->password;
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

	/**
	 * We set the authentication cookie only after login is successfullly finished.
	 * We set a new cookie either for a user with no cookies or one
	 * where the user used a cookie to authenticate.
	 *
	 * @param   array  options  Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function onUserAfterLogin($options)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		// Return if no Cookie based login.
		if (!isset($options['responseType']) || ($options['responseType'] != 'Cookie' && empty($options['remember'])))
		{
			return true;
		}

		// Logged in using a cookie
		if (isset($options['responseType']) && $options['responseType'] == 'Cookie')
		{
		}

		// Remember checkbox is set
		if (!empty($options['remember']))
		{
		}

		// We get the parameter values differently for cookie and non-cookie logins.
		$cookieLifetime	= empty($options['lifetime']) ? $this->app->rememberCookieLifetime : $options['lifetime'];
		$length			= empty($options['length']) ? $this->app->rememberCookieLength : $options['length'];
		$secure			= empty($options['secure']) ? $this->app->rememberCookieSecure : $options['secure'];

		// We need the old data to match against the current database
		$rememberArray = JUserHelper::getRememberCookieData();

		$privateKey = JUserHelper::genRandomPassword($length);

		// We are going to concatenate with . so we need to remove it from the strings.
		$privateKey = str_replace('.', '', $privateKey);

		$cryptedKey = JUserHelper::getCryptedPassword($privateKey, '', 'bcrypt', false);

		$cookieName = JUserHelper::getShortHashedUserAgent();

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

		// If a user logs in with non cookie login and remember me checked we will
		// delete any invalid entries so that he or she can use remember once again.
		if ($options['responseType'] !== 'Cookie')
		{
			$query = $this->db->getQuery(true)
				->delete('#__user_keys')
				->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['user']->username));

			$this->db->setQuery($query)->execute();
		}

		$cookieValue = $cryptedKey . '.' . $series . '.' . $cookieName;

		// Destroy the old cookie.
		$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->app->get('cookie_path'), $this->app->get('cookie_domain'));

		// And make a new one.
		$this->app->input->cookie->set(
			$cookieName, $cookieValue, time() + $cookieLifetime, $this->app->get('cookie_path'), $this->app->get('cookie_domain'), $secure
		);

		$query = $this->db->getQuery(true);

		if (empty($options['user']->cookieLogin) || $options['responseType'] != 'Cookie')
		{
			// For users doing login from Joomla or other systems
			$query->insert($this->db->quoteName('#__user_keys'));
		}
		else
		{
			$query
				->update($this->db->quoteName('#__user_keys'))
				->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['user']->username))
				->where($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($rememberArray[1])))
				->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName));
		}

		$query
			->set($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['user']->username))
			->set($this->db->quoteName('time') . ' = ' . (time() + $cookieLifetime))
			->set($this->db->quoteName('token') . ' = ' . $this->db->quote($cryptedKey))
			->set($this->db->quoteName('series') . ' = ' . $this->db->quote(base64_encode($series)))
			->set($this->db->quoteName('invalid') . ' = 0')
			->set($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName));

		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * This is where we delete any authentication cookie when a user logs out
	 *
	 * @param   array  $options  Array holding options (length, timeToExpiration)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	public function onUserAfterLogout($options)
	{
		// No remember me for admin
		if ($this->app->isAdmin())
		{
			return false;
		}

		$rememberArray = JUserHelper::getRememberCookieData();

		// There are no cookies to delete.
		if ($rememberArray === false)
		{
			return true;
		}

		list($privateKey, $series, $cookieName) = $rememberArray;

		// Remove the record from the database
		$query = $this->db->getQuery(true);

		$query
			->delete('#__user_keys')
			->where($this->db->quoteName('uastring') . ' = ' . $this->db->quote($cookieName))
			->where($this->db->quoteName('user_id') . ' = ' . $this->db->quote($options['username']));

		$this->db->setQuery($query)->execute();

		// Destroy the cookie
		$this->app->input->cookie->set($cookieName, false, time() - 42000, $this->app->get('cookie_path'), $this->app->get('cookie_domain'));

		return true;
	}
}
