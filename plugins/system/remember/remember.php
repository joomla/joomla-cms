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
			$series = JApplication::getHash('JLOGIN_REMEMBER');

			$inputCookie = new JInputCookie();
			$cookieValue = $inputCookie->get($series);

			//$crypt = new JCrypt();
			//$cookieValue = $crypt->decrypt($cookieValue);
			if (!empty($cookieValue))
			{

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
				$filter = JFilterInput::getInstance();

				$series64 = base64_encode($series);

				//Find the matching record if it exists
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quotename(array('user_id', 'token', 'series', 'time', 'invalid')))
					->where($db->quoteName('series') . ' = ' . $db->quote($series64) )
					->from($db->quoteName('#__user_keys'));
				$db->setQuery($query);
				$results = $db->loadObjectList();

				$countResults = count($results);

				// If the user has multiple cookies for the same series something is wrong, invalidate them all.
				if ($countResults > 1)
				{
					$this->invalidateCookie($results[0]->user_id);

					return false;
				}

				// We have a cookie but it's not in the database or the cookie is invalid. Possible attack, invalidate every thing.
				if ($countResults == 0 || !$results || $results[0]->invalid != 0)
				{
					//Should this start by throwing an exception?
					// We can only invalidate if there is a user.
					if (!empty($results[0]->user_id))
					{
						$this->invalidateCookie($results[0]->user_id);
						JLog::add('The remember me tokens were invalidated for user ' . $user->username  . ' because there was no matching record ', JLog::WARNING, 'security');

						// Make this stronger ?
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
								$match = password_verify($cookieValue, $results[0]->token);
							}
						}
						else
						{
							$parts	= explode(':', $results[0]->token);
							$crypt	= $parts[0];
							$salt	= @$parts[1];

							$testcrypt = JUserHelper::getCryptedPassword($cookieValue, $salt, 'md5-hex', false);

							if ($crypt == $testcrypt)
							{
								$match = true;
							}
						}
						if (!$match)
						{
							JLog::add('Remember me login failed for user ' . $user->username , JLog::WARNING, 'security');
							$this->invalidateCookie($results[0]->user_id);

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
			$series = JApplication::getHash('JLOGIN_REMEMBER');
			$inputCookie = new JInputCookie();

			// If there is no cookie, bail out
			if (!$inputCookie->get($series))
			{
				$user->set('rememberLogin', true);

			}

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
		$app = JFactory::getApplication();
		$length = $this->params->get('key_length', '20');

		$privateKey = JUserHelper::genRandomPassword($length);
		$cryptedKey = JUserHelper::getCryptedPassword($privateKey, '', 'bcrypt', false);

		$lifetime = time() + ($this->params->get('cookie_lifetime', '60') * 24 * 60 * 60);

		$series = JApplication::getHash('JLOGIN_REMEMBER');
		$series64 = base64_encode($series);

		// Use domain and path set in config for cookie if it exists.
		$cookie_domain = $app->getCfg('cookie_domain', '');
		$cookie_path = $app->getCfg('cookie_path', '/');

		$secure = $app->isSSLConnection();

		// Destroy the old cookie.
		setcookie($series, $privateKey, time() - 42000, $cookie_path, $cookie_domain, $secure, true);

		// And make a new one.
		setcookie($series, $privateKey, $lifetime, $cookie_path, $cookie_domain, $secure, true);

		$user = JFactory::getUser();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if (empty($user->rememberLogin))
		{
			$query->insert($db->quoteName('#__user_keys'));
		}
		else
		{
			$query->update($db->quoteName('#__user_keys'))
				->where($db->quote($user->username) . ' = ' . $db->quoteName('user_id') . ' AND ' .  $db->quote($series64) . ' = ' . $db->quoteName('series'));
		}

		$query->set($db->quoteName('user_id') . ' = ' . $db->quote($user->username))
			->set($db->quoteName('time') . ' = ' . $lifetime)
			->set($db->quoteName('token') . ' = ' . $db->quote($cryptedKey))
			->set($db->quoteName('series') . ' = ' . $db->quote($series64));
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
	public function onUserAfterLogout($parameters, $options)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		// Use domain and path set in config for cookie if it exists.
		$cookie_domain = $app->getCfg('cookie_domain', '');
		$cookie_path = $app->getCfg('cookie_path', '/');
		$series = JApplication::getHash('JLOGIN_REMEMBER');
		$inputCookie = new JInputCookie();

		// If there is no cookie, bail out
		if (!$inputCookie->get($series))
		{
			$cookieName64 = base64_encode($inputCookie->get($series));

			// We need to delete the records from the database also.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__user_keys');
			$query->where($db->quoteName('series') . ' = ' . $db->quote($cookieName64) . ' AND ' . $db->quoteName('user_id') . ' = ' . $user->id );
			$db->setQuery($query);
			$db->execute();
		}

		setcookie(Japplication::getHash('JLOGIN_REMEMBER'), false, time() - 86400, $cookie_path, $cookie_domain);

		return;
	}

	protected function invalidateCookie($userId)
	{
		// Invalidate cookie
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__user_keys'))
		->set($db->quoteName('invalid') . '= ' . 1)
		->where($db->quotename('user_id') . ' = ' . $db->quote($userId));
		$db->setQuery($query);
		$db->execute();

	}

}
