<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Authorisation helper class, provides static methods to perform various tasks relevant
 * to the Joomla user and authorisation classes
 *
 * This class has influences and some method logic from the Horde Auth package
 *
 * @since  11.1
 */
abstract class JUserHelper
{
	/**
	 * Method to add a user to a group.
	 *
	 * @param   integer  $userId   The id of the user.
	 * @param   integer  $groupId  The id of the group.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public static function addUserToGroup($userId, $groupId)
	{
		// Get the user object.
		$user = new JUser((int) $userId);

		// Add the user to the group if necessary.
		if (!in_array($groupId, $user->groups))
		{
			// Get the title of the group.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('id') . ' = ' . (int) $groupId);
			$db->setQuery($query);
			$title = $db->loadResult();

			// If the group does not exist, return an exception.
			if (!$title)
			{
				throw new RuntimeException('Access Usergroup Invalid');
			}

			// Add the group data to the user object.
			$user->groups[$title] = $groupId;

			// Store the user object.
			$user->save();
		}

		// Set the group data for any preloaded user objects.
		$temp         = JUser::getInstance((int) $userId);
		$temp->groups = $user->groups;

		if (JFactory::getSession()->getId())
		{
			// Set the group data for the user object in the session.
			$temp = JFactory::getUser();

			if ($temp->id == $userId)
			{
				$temp->groups = $user->groups;
			}
		}

		return true;
	}

	/**
	 * Method to get a list of groups a user is in.
	 *
	 * @param   integer  $userId  The id of the user.
	 *
	 * @return  array    List of groups
	 *
	 * @since   11.1
	 */
	public static function getUserGroups($userId)
	{
		// Get the user object.
		$user = JUser::getInstance((int) $userId);

		return isset($user->groups) ? $user->groups : array();
	}

	/**
	 * Method to remove a user from a group.
	 *
	 * @param   integer  $userId   The id of the user.
	 * @param   integer  $groupId  The id of the group.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function removeUserFromGroup($userId, $groupId)
	{
		// Get the user object.
		$user = JUser::getInstance((int) $userId);

		// Remove the user from the group if necessary.
		$key = array_search($groupId, $user->groups);

		if ($key !== false)
		{
			// Remove the user from the group.
			unset($user->groups[$key]);

			// Store the user object.
			$user->save();
		}

		// Set the group data for any preloaded user objects.
		$temp = JFactory::getUser((int) $userId);
		$temp->groups = $user->groups;

		// Set the group data for the user object in the session.
		$temp = JFactory::getUser();

		if ($temp->id == $userId)
		{
			$temp->groups = $user->groups;
		}

		return true;
	}

	/**
	 * Method to set the groups for a user.
	 *
	 * @param   integer  $userId  The id of the user.
	 * @param   array    $groups  An array of group ids to put the user in.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function setUserGroups($userId, $groups)
	{
		// Get the user object.
		$user = JUser::getInstance((int) $userId);

		// Set the group ids.
		$groups = ArrayHelper::toInteger($groups);
		$user->groups = $groups;

		// Get the titles for the user groups.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('title'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('id') . ' = ' . implode(' OR ' . $db->quoteName('id') . ' = ', $user->groups));
		$db->setQuery($query);
		$results = $db->loadObjectList();

		// Set the titles for the user groups.
		for ($i = 0, $n = count($results); $i < $n; $i++)
		{
			$user->groups[$results[$i]->id] = $results[$i]->id;
		}

		// Store the user object.
		$user->save();

		if (session_id())
		{
			// Set the group data for any preloaded user objects.
			$temp = JFactory::getUser((int) $userId);
			$temp->groups = $user->groups;

			// Set the group data for the user object in the session.
			$temp = JFactory::getUser();

			if ($temp->id == $userId)
			{
				$temp->groups = $user->groups;
			}
		}

		return true;
	}

	/**
	 * Gets the user profile information
	 *
	 * @param   integer  $userId  The id of the user.
	 *
	 * @return  object
	 *
	 * @since   11.1
	 */
	public static function getProfile($userId = 0)
	{
		if ($userId == 0)
		{
			$user   = JFactory::getUser();
			$userId = $user->id;
		}

		// Get the dispatcher and load the user's plugins.
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('user');

		$data = new JObject;
		$data->id = $userId;

		// Trigger the data preparation event.
		$dispatcher->trigger('onContentPrepareData', array('com_users.profile', &$data));

		return $data;
	}

	/**
	 * Method to activate a user
	 *
	 * @param   string  $activation  Activation string
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public static function activateUser($activation)
	{
		$db = JFactory::getDbo();

		// Let's get the id of the user we want to activate
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('activation') . ' = ' . $db->quote($activation))
			->where($db->quoteName('block') . ' = 1')
			->where($db->quoteName('lastvisitDate') . ' = ' . $db->quote($db->getNullDate()));
		$db->setQuery($query);
		$id = (int) $db->loadResult();

		// Is it a valid user to activate?
		if ($id)
		{
			$user = JUser::getInstance((int) $id);

			$user->set('block', '0');
			$user->set('activation', '');

			// Time to take care of business.... store the user.
			if (!$user->save())
			{
				JLog::add($user->getError(), JLog::WARNING, 'jerror');

				return false;
			}
		}
		else
		{
			JLog::add(JText::_('JLIB_USER_ERROR_UNABLE_TO_FIND_USER'), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Returns userid if a user exists
	 *
	 * @param   string  $username  The username to search on.
	 *
	 * @return  integer  The user id or 0 if not found.
	 *
	 * @since   11.1
	 */
	public static function getUserId($username)
	{
		// Initialise some variables
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = ' . $db->quote($username));
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Hashes a password using the current encryption.
	 *
	 * @param   string  $password  The plaintext password to encrypt.
	 *
	 * @return  string  The encrypted password.
	 *
	 * @since   3.2.1
	 */
	public static function hashPassword($password)
	{
		// JCrypt::hasStrongPasswordSupport() includes a fallback for us in the worst case
		JCrypt::hasStrongPasswordSupport();

		return password_hash($password, PASSWORD_DEFAULT);
	}

	/**
	 * Formats a password using the current encryption. If the user ID is given
	 * and the hash does not fit the current hashing algorithm, it automatically
	 * updates the hash.
	 *
	 * @param   string   $password  The plaintext password to check.
	 * @param   string   $hash      The hash to verify against.
	 * @param   integer  $user_id   ID of the user if the password hash should be updated
	 *
	 * @return  boolean  True if the password and hash match, false otherwise
	 *
	 * @since   3.2.1
	 */
	public static function verifyPassword($password, $hash, $user_id = 0)
	{
		$rehash = false;
		$match = false;

		// If we are using phpass
		if (strpos($hash, '$P$') === 0)
		{
			// Use PHPass's portable hashes with a cost of 10.
			$phpass = new PasswordHash(10, true);

			$match = $phpass->CheckPassword($password, $hash);

			$rehash = true;
		}
		elseif ($hash[0] == '$')
		{
			// JCrypt::hasStrongPasswordSupport() includes a fallback for us in the worst case
			JCrypt::hasStrongPasswordSupport();
			$match = password_verify($password, $hash);

			// Uncomment this line if we actually move to bcrypt.
			$rehash = password_needs_rehash($hash, PASSWORD_DEFAULT);
		}
		elseif (substr($hash, 0, 8) == '{SHA256}')
		{
			// Check the password
			$parts     = explode(':', $hash);
			$crypt     = $parts[0];
			$salt      = @$parts[1];
			$testcrypt = static::getCryptedPassword($password, $salt, 'sha256', true);

			$match = JCrypt::timingSafeCompare($hash, $testcrypt);

			$rehash = true;
		}
		else
		{
			// Check the password
			$parts = explode(':', $hash);
			$crypt = $parts[0];
			$salt  = @$parts[1];

			$rehash = true;

			// Compile the hash to compare
			// If the salt is empty AND there is a ':' in the original hash, we must append ':' at the end
			$testcrypt = md5($password . $salt) . ($salt ? ':' . $salt : (strpos($hash, ':') !== false ? ':' : ''));

			$match = JCrypt::timingSafeCompare($hash, $testcrypt);
		}

		// If we have a match and rehash = true, rehash the password with the current algorithm.
		if ((int) $user_id > 0 && $match && $rehash)
		{
			$user = new JUser($user_id);
			$user->password = static::hashPassword($password);
			$user->save();
		}

		return $match;
	}

	/**
	 * Formats a password using the old encryption methods.
	 *
	 * @param   string   $plaintext     The plaintext password to encrypt.
	 * @param   string   $salt          The salt to use to encrypt the password. []
	 *                                  If not present, a new salt will be
	 *                                  generated.
	 * @param   string   $encryption    The kind of password encryption to use.
	 *                                  Defaults to md5-hex.
	 * @param   boolean  $show_encrypt  Some password systems prepend the kind of
	 *                                  encryption to the crypted password ({SHA},
	 *                                  etc). Defaults to false.
	 *
	 * @return  string  The encrypted password.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public static function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		// Get the salt to use.
		$salt = static::getSalt($encryption, $salt, $plaintext);

		// Encrypt the password.
		switch ($encryption)
		{
			case 'plain':
				return $plaintext;

			case 'sha':
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));

				return ($show_encrypt) ? '{SHA}' . $encrypted : $encrypted;

			case 'crypt':
			case 'crypt-des':
			case 'crypt-md5':
			case 'crypt-blowfish':
				return ($show_encrypt ? '{crypt}' : '') . crypt($plaintext, $salt);

			case 'md5-base64':
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));

				return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;

			case 'ssha':
				$encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext . $salt) . $salt);

				return ($show_encrypt) ? '{SSHA}' . $encrypted : $encrypted;

			case 'smd5':
				$encrypted = base64_encode(mhash(MHASH_MD5, $plaintext . $salt) . $salt);

				return ($show_encrypt) ? '{SMD5}' . $encrypted : $encrypted;

			case 'aprmd5':
				$length = strlen($plaintext);
				$context = $plaintext . '$apr1$' . $salt;
				$binary = static::_bin(md5($plaintext . $salt . $plaintext));

				for ($i = $length; $i > 0; $i -= 16)
				{
					$context .= substr($binary, 0, ($i > 16 ? 16 : $i));
				}

				for ($i = $length; $i > 0; $i >>= 1)
				{
					$context .= ($i & 1) ? chr(0) : $plaintext[0];
				}

				$binary = static::_bin(md5($context));

				for ($i = 0; $i < 1000; $i++)
				{
					$new = ($i & 1) ? $plaintext : substr($binary, 0, 16);

					if ($i % 3)
					{
						$new .= $salt;
					}

					if ($i % 7)
					{
						$new .= $plaintext;
					}

					$new .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
					$binary = static::_bin(md5($new));
				}

				$p = array();

				for ($i = 0; $i < 5; $i++)
				{
					$k = $i + 6;
					$j = $i + 12;

					if ($j == 16)
					{
						$j = 5;
					}

					$p[] = static::_toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])), 5);
				}

				return '$apr1$' . $salt . '$' . implode('', $p) . static::_toAPRMD5(ord($binary[11]), 3);

			case 'sha256':
				$encrypted = ($salt) ? hash('sha256', $plaintext . $salt) . ':' . $salt : hash('sha256', $plaintext);

				return ($show_encrypt) ? '{SHA256}' . $encrypted : '{SHA256}' . $encrypted;

			case 'md5-hex':
			default:
				$encrypted = ($salt) ? md5($plaintext . $salt) : md5($plaintext);

				return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
		}
	}

	/**
	 * Returns a salt for the appropriate kind of password encryption using the old encryption methods.
	 * Optionally takes a seed and a plaintext password, to extract the seed
	 * of an existing password, or for encryption types that use the plaintext
	 * in the generation of the salt.
	 *
	 * @param   string  $encryption  The kind of password encryption to use.
	 *                               Defaults to md5-hex.
	 * @param   string  $seed        The seed to get the salt from (probably a
	 *                               previously generated password). Defaults to
	 *                               generating a new seed.
	 * @param   string  $plaintext   The plaintext password that we're generating
	 *                               a salt for. Defaults to none.
	 *
	 * @return  string  The generated or extracted salt.
	 *
	 * @since   11.1
	 * @deprecated  4.0
	 */
	public static function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '')
	{
		// Encrypt the password.
		switch ($encryption)
		{
			case 'crypt':
			case 'crypt-des':
				if ($seed)
				{
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2);
				}
				else
				{
					return substr(md5(mt_rand()), 0, 2);
				}
				break;

			case 'sha256':
				if ($seed)
				{
					return preg_replace('|^{sha256}|i', '', $seed);
				}
				else
				{
					return static::genRandomPassword(16);
				}
				break;

			case 'crypt-md5':
				if ($seed)
				{
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12);
				}
				else
				{
					return '$1$' . substr(md5(JCrypt::genRandomBytes()), 0, 8) . '$';
				}
				break;

			case 'crypt-blowfish':
				if ($seed)
				{
					return substr(preg_replace('|^{crypt}|i', '', $seed), 0, 30);
				}
				else
				{
					return '$2y$10$' . substr(md5(JCrypt::genRandomBytes()), 0, 22) . '$';
				}
				break;

			case 'ssha':
				if ($seed)
				{
					return substr(preg_replace('|^{SSHA}|', '', $seed), -20);
				}
				else
				{
					return mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(JCrypt::genRandomBytes())), 0, 8), 4);
				}
				break;

			case 'smd5':
				if ($seed)
				{
					return substr(preg_replace('|^{SMD5}|', '', $seed), -16);
				}
				else
				{
					return mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(JCrypt::genRandomBytes())), 0, 8), 4);
				}
				break;

			case 'aprmd5': /* 64 characters that are valid for APRMD5 passwords. */
				$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

				if ($seed)
				{
					return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
				}
				else
				{
					$salt = '';

					for ($i = 0; $i < 8; $i++)
					{
						$salt .= $APRMD5{mt_rand(0, 63)};
					}

					return $salt;
				}
				break;

			default:
				$salt = '';

				if ($seed)
				{
					$salt = $seed;
				}

				return $salt;
				break;
		}
	}

	/**
	 * Generate a random password
	 *
	 * @param   integer  $length  Length of the password to generate
	 *
	 * @return  string  Random Password
	 *
	 * @since   11.1
	 */
	public static function genRandomPassword($length = 8)
	{
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$base = strlen($salt);
		$makepass = '';

		/*
		 * Start with a cryptographic strength random string, then convert it to
		 * a string with the numeric base of the salt.
		 * Shift the base conversion on each character so the character
		 * distribution is even, and randomize the start shift so it's not
		 * predictable.
		 */
		$random = JCrypt::genRandomBytes($length + 1);
		$shift = ord($random[0]);

		for ($i = 1; $i <= $length; ++$i)
		{
			$makepass .= $salt[($shift + ord($random[$i])) % $base];
			$shift += ord($random[$i]);
		}

		return $makepass;
	}

	/**
	 * Converts to allowed 64 characters for APRMD5 passwords.
	 *
	 * @param   string   $value  The value to convert.
	 * @param   integer  $count  The number of characters to convert.
	 *
	 * @return  string  $value converted to the 64 MD5 characters.
	 *
	 * @since   11.1
	 */
	protected static function _toAPRMD5($value, $count)
	{
		/* 64 characters that are valid for APRMD5 passwords. */
		$APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		$aprmd5 = '';
		$count = abs($count);

		while (--$count)
		{
			$aprmd5 .= $APRMD5[$value & 0x3f];
			$value >>= 6;
		}

		return $aprmd5;
	}

	/**
	 * Converts hexadecimal string to binary data.
	 *
	 * @param   string  $hex  Hex data.
	 *
	 * @return  string  Binary data.
	 *
	 * @since   11.1
	 */
	private static function _bin($hex)
	{
		$bin = '';
		$length = strlen($hex);

		for ($i = 0; $i < $length; $i += 2)
		{
			$tmp = sscanf(substr($hex, $i, 2), '%x');
			$bin .= chr(array_shift($tmp));
		}

		return $bin;
	}

	/**
	 * Method to remove a cookie record from the database and the browser
	 *
	 * @param   string  $userId      User ID for this user
	 * @param   string  $cookieName  Series id (cookie name decoded)
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 * @deprecated  4.0  This is handled in the authentication plugin itself. The 'invalid' column in the db should be removed as well
	 */
	public static function invalidateCookie($userId, $cookieName)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Invalidate cookie in the database
		$query
			->update($db->quoteName('#__user_keys'))
			->set($db->quoteName('invalid') . ' = 1')
			->where($db->quotename('user_id') . ' = ' . $db->quote($userId));

		$db->setQuery($query)->execute();

		// Destroy the cookie in the browser.
		$app = JFactory::getApplication();
		$app->input->cookie->set($cookieName, false, time() - 42000, $app->get('cookie_path', '/'), $app->get('cookie_domain'), false, true);

		return true;
	}

	/**
	 * Clear all expired tokens for all users.
	 *
	 * @return  mixed  Database query result
	 *
	 * @since   3.2
	 * @deprecated  4.0  This is handled in the authentication plugin itself
	 */
	public static function clearExpiredTokens()
	{
		$now = time();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->delete('#__user_keys')
		->where($db->quoteName('time') . ' < ' . $db->quote($now));

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method to get the remember me cookie data
	 *
	 * @return  mixed  An array of information from an authentication cookie or false if there is no cookie
	 *
	 * @since   3.2
	 * @deprecated  4.0  This is handled in the authentication plugin itself
	 */
	public static function getRememberCookieData()
	{
		// Create the cookie name
		$cookieName = static::getShortHashedUserAgent();

		// Fetch the cookie value
		$app = JFactory::getApplication();
		$cookieValue = $app->input->cookie->get($cookieName);

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
	 * Method to get a hashed user agent string that does not include browser version.
	 * Used when frequent version changes cause problems.
	 *
	 * @return  string  A hashed user agent string with version replaced by 'abcd'
	 *
	 * @since   3.2
	 */
	public static function getShortHashedUserAgent()
	{
		$ua = JFactory::getApplication()->client;
		$uaString = $ua->userAgent;
		$browserVersion = $ua->browserVersion;
		$uaShort = str_replace($browserVersion, 'abcd', $uaString);

		return md5(JUri::base() . $uaShort);
	}

	/**
	 * Check if there is a super user in the user ids.
	 *
	 * @param   array  $userIds  An array of user IDs on which to operate
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   3.6.5
	 */
	public static function checkSuperUserInUsers(array $userIds)
	{
		foreach ($userIds as $userId)
		{
			foreach (static::getUserGroups($userId) as $userGroupId)
			{
				if (JAccess::checkGroup($userGroupId, 'core.admin'))
				{
					return true;
				}
			}
		}

		return false;
	}
}
