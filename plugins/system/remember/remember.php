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
		$app = JFactory::getApplication();

		// No remember me for admin
		if ($app->isAdmin())
		{
			return;
		}

		$user = JFactory::getUser();

		// Check for a cookie
		if ($user->get('guest'))
		{
			$hash = JApplication::getHash('JLOGIN_REMEMBER');

			$inputCookie = new JInputCookie();

			if ($str = $inputCookie->get($hash))
			{
				$credentials = array();
				$filter = JFilterInput::getInstance();

				$privateKey = JApplication::getHash('JLOGIN_REMEMBER');
				$privateKey64 = base64_encode($privateKey);

				$privateKeyLength = strlen($privateKey);

				$nowtime = time();

				//Find the matching record if it exists
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quotename(array('user_id', 'token', 'series', 'timestamp', 'invalid')))
					->where($db->quoteName('time') . ' > ' . $db->quote($nowtime))
					->from($db->quoteName('#__user_keys'));
				$db->setQuery($query);
				$results = $db->loadObjectList();

				$invalid = 0;
				foreach ($results as $result)
				{
					if ($result->invalid == 1)
					{
						 $invalid = 1;
						 continue;
					}

					$series = substr($result->series, 0, $privateKeyLength);
					$privateKey64 = substr($privateKey64, 0, $privateKeyLength);

					if ($series == $privateKey64)
					{
						$match = $result;
						// Now check the key
						$keyCheck = new JCryptKey('simple', $result->token, $result->token);
						$cryptCheck = new JCrypt(new JCryptCipherSimple, $keyCheck);
						$cryptedToken = $cryptCheck->encrypt(sha1($user->username));
					}

				}

				// We have a cookie but it's not in the database or the cookie is invalid. Possible attack, invalidate every thing.
				if (!$results || !isset($match) || $invalid != 0)
				{
					//Should this start by throwing an exception?
					// We can only invalidate if there is a user.
					if (!empty($results->user_id))
					{
						$db = JFactory::getDbo();
						$query = $db->getQuery(true);

						$query->update($db->quoteName('#__user_keys'))
						->values(1)
						->columns($db->quoteName('invalid'))
						->where($db->quotename('user_id') . '=' . $db->quote($results->user_id));
						$db->setQuery($query);
						$db->execute();

						JLog::add('The remember me tokens were invalidated for user ' . $user['username']  . ' for the following reason: ' . $e->getMessage(), JLog::WARNING, 'security');
						// Make this stronger ?
						return false;
					}
				}


/*
					// We probably should add back some sanity checks but not as many as before
					if (!is_string($str))
					{
						throw new InvalidArgumentException('Decoded cookie is not a string.');
					}

*/
					$credentials['username'] = $result->user_id;
					$return = $app->login($credentials, array('silent' => true));
/*
					// We've used this cookie so destroy it.
					$cookie_domain = $app->getCfg('cookie_domain', '');
					$cookie_path = $app->getCfg('cookie_path', '/');

					// Destroy the cookie
					setcookie($hash, false, time() - 86400, $cookie_path, $cookie_domain);
*/
					if (!$return)
					{
						throw new Exception('Log-in failed.');
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
	 * @param   array  options     Array holding options (length, timeToExpiration,)
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 */
	public function onUserAfterLogin($options)
	{

		$app = JFactory::getApplication();
		$length = (int) $options['length'];
		$privateKey = JCrypt::genRandomBytes(70);

		$user = JFactory::getUser();
		$key = new JCryptKey('simple', $privateKey, $privateKey);
		$crypt = new JCrypt(new JCryptCipherSimple, $key);
		$rcookie = $crypt->encrypt(sha1($user->username));
		$lifetime = time() + $options['timeToExpiration'] * 24 * 60 * 60;
		$key64 = base64_encode($privateKey);

		$series = JApplication::getHash('JLOGIN_REMEMBER');
		$series64 = base64_encode($series);

		// Use domain and path set in config for cookie if it exists.
		$cookie_domain = $app->getCfg('cookie_domain', '');
		$cookie_path = $app->getCfg('cookie_path', '/');

		$secure = $app->isSSLConnection();

		// We've used this cookie so destroy it.
		//setcookie($series, false, time() - 86400, $cookie_path, $cookie_domain);

		//And make a new one
		setcookie($series, $rcookie, $lifetime, $cookie_path, $cookie_domain, $secure, true);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$expire = time() + $options['timeToExpiration'] * 24 * 60 * 60;

		$tuple = $db->quote($user->username) . ', ' . $expire . ', ' .  $db->quote($key64) . ' , '. $db->quote($series64);

		$user = JFactory::getUser();

		if (empty($user->rememberLogin))
		{
			$query->insert($db->quoteName('#__user_keys'));
		}
		else
		{
			$query->update($db->quoteName('#__user_keys'))
				->where($db->quote($user->username) . ' = ' . $db->quoteName('user_id') . ' AND ' .  $db->quote($key64) . ' = ' . $db->quoteName('token'));
		}
		$query->values($tuple)
			->columns(array($db->quoteName('user_id'), $db->quoteName('time'), $db->quoteName('token'), $db->quoteName('series')));
		$db->setQuery($query);

		$db->execute();

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
	{var_dump($options);die;
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		// Use domain and path set in config for cookie if it exists.
		$cookie_domain = $app->getCfg('cookie_domain', '');
		$cookie_path = $app->getCfg('cookie_path', '/');
		$cookieName64 = base64_encode($options['cookieName']);
		$cookieNameLength = strlen($cookieName64);
		$series = substr($result->series, 0, $cookieNameLength);

		// We need to delete the records from the database also.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__user_keys');
		$query->where($db->quoteName('series') . ' = ' . $db->quote($cookieName64) . ' AND ' . $db->quoteName('user_id') . ' = ' . $user->id );
		$db->setQuery($query);
		$db->execute();


		setcookie(Japplication::getHash('JLOGIN_REMEMBER'), false, time() - 86400, $cookie_path, $cookie_domain);

		return;
	}
}
