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
				$query->select($db->quotename(array('user_id', 'token', 'series', 'timestamp', 'invalid')) )
				//->where('SUBSTRING( ' . $db->quoteName('series') . ', 1, ' . $privateKeyLength . ' )  = ' . $db->quote($privateKey64))
				->where($db->quoteName('time') . ' > ' . $db->quote($nowtime))
				->from($db->quoteName('#__user_keys'));
				$db->setQuery($query);
				$results = $db->loadObjectList();

				$invalid = 0;
				foreach ($results as $result)
				{
					if ($result->invalid)
					{
						 $invalid =1;
						 continue;
					}

					$series = substr($result->series, 0, $privateKeyLength);

					if ($series == $privateKey64)
					{
						$match = $result;
					}
				}

				// We have a cookie but it's not in the database or the cookie is invalid. Possible replay, invalidate every thing.
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
					if (!is_string($str))
					{
						throw new InvalidArgumentException('Decoded cookie is not a string.');
					}

					$cookieData = json_decode($str);
					if (null === $cookieData)
					{
						throw new InvalidArgumentException('JSON could not be decoded.');
					}

					if (!is_object($cookieData))
					{
						throw new InvalidArgumentException('Decoded JSON is not an object.');
					}

					// json_decoded cookie could be any object structure, so make sure the
					// credentials are well structured and only have user and password.
					if (isset($cookieData->username) && is_string($cookieData->username))
					{
						$credentials['username'] = $filter->clean($cookieData->username, 'username');
					}
					else
					{
						throw new InvalidArgumentException('Malformed username.');
					}
*/
					$return = $app->login($credentials, array('silent' => true));

					$user->set('rememberLogin', true);

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

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
		->select('id, password')
		->from('#__users')
		->where('username=' . $db->quote($credentials['username']));

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result)
		{
			$parts	= explode(':', $result->password);
			$crypt	= $parts[0];
			$salt	= @$parts[1];
			$testcrypt = JUserHelper::getCryptedPassword($credentials['password'], $salt);

			if ($crypt == $testcrypt)
			{
				// Bring this in line with the rest of the system
				$user = JUser::getInstance($result->id);
				$response->email = $user->email;
				$response->fullname = $user->name;

					$response->language = $user->getParam('language');

				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
			else
			{
				$response->status = JAuthentication::STATUS_FAILURE;
				$response->error_message = JText::_('JGLOBAL_AUTH_INVALID_COOKIE');
			}
		}
		else
		{
			$response->status = JAuthentication::STATUS_FAILURE;
			$response->error_message = JText::_('JGLOBAL_AUTH_NO_USER');
		}
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 * This is where we set the remember me cookie. We set a new cookie either for a user with no cookies or one
	 * where the user used a cookie to authenticate.
	 *
	 * @param   array  $user                     Holds the user data
	 * @param   JApplication  $app               The application object
	 * @param   integer       $length            Length of the private key
	 * @param   integer       $timeToExpiration  Time limit for remember me cookie in days
	 * @param   array         $options           Array holding options (remember, autoregister, group)
	 *
	 * @return  boolean  True on success
	 * @since   3.1.2
	 */
	public function onUserAfterLogin($user, $app, $length = 16, $timeToExpiration = 30, $options = array())
	{
		$length = (int) $length;
		$privateKey = JCrypt::genRandomBytes($length);

		$key = new JCryptKey('simple', $privateKey, $privateKey);
		$crypt = new JCrypt(new JCryptCipherSimple, $key);
		$rcookie = $crypt->encrypt(sha1($user['username']));
		$lifetime = time() + $timeToExpiration * 24 * 60 * 60;
		$key64 = base64_encode(json_encode($key));

		$series = JApplication::getHash('JLOGIN_REMEMBER');
		$series64 = base64_encode($series);

		// Use domain and path set in config for cookie if it exists.
		$cookie_domain = $app->getCfg('cookie_domain', '');
		$cookie_path = $app->getCfg('cookie_path', '/');

		$secure = $app->isSSLConnection();

		setcookie($series, $rcookie, $lifetime, $cookie_path, $cookie_domain, $secure, true);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$expire = time() + $timeToExpiration * 24 * 60 * 60;
		 ;
		$tuple = $db->quote($user['username']) . ', ' . $expire . ', ' .  $db->quote($key64) . ' , '. $db->quote($series64);

		$user = JFactory::getUser();

		if (empty($user->rememberLogin))
		{
			$query->insert($db->quoteName('#__user_keys'));
		}
		else
		{
			$query->update($db->quoteName('#__user_keys'))
				->where($db->quote($user['username']) . ' = ' . $db->quoteName('user_id') . ' AND ' .  $db->quote($key64) . ' = ' . $db->quoteName('series'));
		}
		$query->values($tuple)
			->columns(array($db->quoteName('user_id'), $db->quoteName('time'), $db->quoteName('token'), $db->quoteName('series')));
		$db->setQuery($query);

		$db->execute();

	}

}
