<?php
/**
 * @package     Joomla.Platform
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * Array of cached groups by user.
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected static $groupsByUser = array();


	/**
	 * Method for clearing static caches.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public static function clearStatics()
	{
		self::$groupsByUser = array();
	}


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
	 * Method to return a list of user groups mapped to a user. The returned list can optionally hold
	 * only the groups explicitly mapped to the user or all groups both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param   integer  $userId     Id of the user for which to get the list of groups.
	 * @param   boolean  $recursive  True to include inherited user groups.
	 *
	 * @return  array    List of user group ids to which the user is mapped.
	 *
	 * @since   3.6
	 */
	public static function getGroupsByUser($userId, $recursive = true)
	{
		// Creates a simple unique string for each parameter combination:
		$storeId = $userId . ':' . (int) $recursive;

		if (!isset(self::$groupsByUser[$storeId]))
		{
			// TODO: Uncouple this from JComponentHelper and allow for a configuration setting or value injection.
			if (class_exists('JComponentHelper'))
			{
				$guestUsergroup = JComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
			}
			else
			{
				$guestUsergroup = 1;
			}

			if (!$recursive)
			{
				// Guest user (if only the actually assigned group is requested)
				if (empty($userId))
				{
					$result = array($guestUsergroup);
				}
				else
				{
					$result = self::getUserGroups($userId);
				}
			}
			// Registered user and guest if all groups are requested
			else
			{
				$db = JFactory::getDbo();

				// Build the database query to get the rules for the asset.
				$query = $db->getQuery(true)
					->select('b.id');

				if (empty($userId))
				{
					$query->from('#__usergroups AS a')
						->where('a.id = ' . (int) $guestUsergroup);
				}
				else
				{
					$query->from('#__user_usergroup_map AS map')
						->where('map.user_id = ' . (int) $userId)
						->join('LEFT', '#__usergroups AS a ON a.id = map.group_id');
				}

				// If we want groups cascading up to the root we need a self-join.
				$query->join('LEFT', '#__usergroups AS b ON b.lft <= a.lft AND b.rgt >= a.rgt');

				// Execute the query and load the rules from the result.
				$db->setQuery($query);
				$result = $db->loadColumn();

				// Clean up any NULL or duplicate values, just in case
				ArrayHelper::toInteger($result);

				if (empty($result))
				{
					$result = array('1');
				}
				else
				{
					$result = array_unique($result);
				}
			}

			self::$groupsByUser[$storeId] = $result;
		}

		return self::$groupsByUser[$storeId];
	}

	/**
	 * Gets the parent groups that a leaf group belongs to in its branch back to the root of the tree
	 * (including the leaf group id).
	 *
	 * @param   mixed  $groupId  An integer or array of integers representing the identities to check.
	 *
	 * @return  mixed  True if allowed, false for an explicit deny, null for an implicit deny.
	 *
	 * @since   11.1
	 */
	public static function getGroupPath($groupId)
	{
		// Load all the groups to improve performance on intensive groups checks
		$groups = JHelperUsergroups::getInstance()->getAll();

		if (!isset($groups[$groupId]))
		{
			return array();
		}

		return $groups[$groupId]->path;
	}

	/**
	 * Method to return the title of a user group
	 *
	 * @param   integer  $groupId  Id of the group for which to get the title of.
	 *
	 * @return  string  Tthe title of the group
	 *
	 * @since   3.6
	 */
	public static function getGroupTitle($groupId)
	{
		// Fetch the group title from the database
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title')
			->from('#__usergroups')
			->where('id = ' . $db->quote($groupId));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to return a list of user Ids contained in a Group
	 *
	 * @param   integer  $groupId    The group Id
	 * @param   boolean  $recursive  Recursively include all child groups (optional)
	 *
	 * @return  array
	 *
	 * @since   3.6
	 */
	public static function getUsersByGroup($groupId, $recursive = false)
	{
		// Get a database object.
		$db = JFactory::getDbo();

		$test = $recursive ? '>=' : '=';

		// First find the users contained in the group
		$query = $db->getQuery(true)
			->select('DISTINCT(user_id)')
			->from('#__usergroups as ug1')
			->join('INNER', '#__usergroups AS ug2 ON ug2.lft' . $test . 'ug1.lft AND ug1.rgt' . $test . 'ug2.rgt')
			->join('INNER', '#__user_usergroup_map AS m ON ug2.id=m.group_id')
			->where('ug1.id=' . $db->quote($groupId));

		$db->setQuery($query);

		$result = $db->loadColumn();

		// Clean up any NULL values, just in case
		ArrayHelper::toInteger($result);

		return $result;
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

		// Set the group data for any preloaded user objects.
		$temp = JFactory::getUser((int) $userId);
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
		JPluginHelper::importPlugin('user');

		$data = new JObject;
		$data->id = $userId;

		// Trigger the data preparation event.
		JFactory::getApplication()->triggerEvent('onContentPrepareData', array('com_users.profile', &$data));

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
			$match = password_verify($password, $hash);

			// Uncomment this line if we actually move to bcrypt.
			$rehash = password_needs_rehash($hash, PASSWORD_DEFAULT);
		}
		elseif (substr($hash, 0, 8) == '{SHA256}')
		{
			// Check the password
			$parts     = explode(':', $hash);
			$salt      = @$parts[1];

			$testcrypt = '{SHA256}' . hash('sha256', $password . $salt) . ':' . $salt;

			$match = JCrypt::timingSafeCompare($hash, $testcrypt);

			$rehash = true;
		}
		else
		{
			// Check the password
			$parts = explode(':', $hash);
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
		$salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
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
