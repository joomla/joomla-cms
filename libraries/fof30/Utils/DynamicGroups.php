<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

/**
 * Dynamic user to user group assignment.
 *
 * This class allows you to add / remove the currently logged in user to a user group without writing the information to
 * the database. This is useful when you want to allow core and third party code to allow or prohibit display of
 * information and / or taking actions based on a condition controlled in your code.
 */
class DynamicGroups
{
	/**
	 * Add the current user to a user group just for this page load.
	 *
	 * @param   int  $groupID  The group ID to add the current user into.
	 *
	 * @return  void
	 */
	public static function addGroup($groupID)
	{
		self::addRemoveGroup($groupID, true);
		self::cleanUpUserObjectCache();
	}

	/**
	 * Remove the current user from a user group just for this page load.
	 *
	 * @param   int  $groupID  The group ID to remove the current user from.
	 *
	 * @return  void
	 */
	public static function removeGroup($groupID)
	{
		self::addRemoveGroup($groupID, false);
		self::cleanUpUserObjectCache();
	}

	/**
	 * Internal function to add or remove the current user from a user group just for this page load.
	 *
	 * @param   int   $groupID  The group ID to add / remove the current user from.
	 * @param   bool  $add      Add (true) or remove (false) the user?
	 *
	 * @return  void
	 */
	protected static function addRemoveGroup($groupID, $add)
	{
		// Get a fake container (we need it for its platform interface)
		$container = Container::getInstance('com_FOOBAR');

		/**
		 * Make sure that Joomla has retrieved the user's groups from the database.
		 *
		 * By going through the User object's getAuthorisedGroups we force Joomla to go through Access::getGroupsByUser
		 * which retrieves the information from the database and caches it into the Access helper class.
		 */
		$container->platform->getUser()->getAuthorisedGroups();
		$container->platform->getUser($container->platform->getUser()->id)->getAuthorisedGroups();

		/**
		 * Now we can get a Reflection object into Joomla's Access helper class and manipulate its groupsByUser cache.
		 */
		$className = class_exists('Joomla\\CMS\\Access\\Access') ? 'Joomla\\CMS\\Access\\Access' : 'JAccess';

		try
		{
			$reflectedAccess = new ReflectionClass($className);
		}
		catch (ReflectionException $e)
		{
			// This should never happen!
			$container->platform->logDebug('Cannot locate the Joomla\\CMS\\Access\\Access or JAccess class. Is your Joomla installation broken or too old / too new?');

			return;
		}

		$groupsByUser = $reflectedAccess->getProperty('groupsByUser');
		$groupsByUser->setAccessible(true);
		$rawGroupsByUser = $groupsByUser->getValue();

		/**
		 * Next up, we need to manipulate the keys of the cache which contain user to user group assignments.
		 *
		 * $rawGroupsByUser (JAccess::$groupsByUser) stored the group ownership as userID:recursive e.g. 0:1 for the
		 * default user, recursive. We need to deal with four keys: 0:1, 0:0, myID:1 and myID:0
		 */
		$user = $container->platform->getUser();
		$keys = ['0:1', '0:0', $user->id . ':1', $user->id . ':0'];

		foreach ($keys as $key)
		{
			if (!array_key_exists($key, $rawGroupsByUser))
			{
				continue;
			}

			$groups = $rawGroupsByUser[$key];

			if ($add)
			{
				if (in_array($groupID, $groups))
				{
					continue;
				}

				$groups[] = $groupID;
			}
			else
			{
				if (!in_array($groupID, $groups))
				{
					continue;
				}

				$removeKey = array_search($groupID, $groups);
				unset($groups[$removeKey]);
			}

			$rawGroupsByUser[$key] = $groups;
		}

		// We can commit our changes back to the cache property and make it publicly inaccessible again.
		$groupsByUser->setValue(null, $rawGroupsByUser);
		$groupsByUser->setAccessible(false);

		/**
		 * We are not done. Caching user groups is only one aspect of Joomla access management. Joomla also caches the
		 * identities, i.e. the user group assignment per user, in a different cache. We need to reset it to for our
		 * user.
		 *
		 * Do note that we CAN NOT use clearStatics since that also clears the user group assignment which we assigned
		 * dynamically. Therefore calling it would destroy our work so far.
		 */
		$refProperty = $reflectedAccess->getProperty('identities');
		$refProperty->setAccessible(true);
		$identities = $refProperty->getValue();

		$keys = [$user->id, 0];

		foreach ($keys as $key)
		{
			if (!array_key_exists($key, $identities))
			{
				continue;
			}

			unset($identities[$key]);
		}

		$refProperty->setValue(null, $identities);
		$refProperty->setAccessible(false);
	}

	/**
	 * Clean up the current user's authenticated groups cache.
	 *
	 * @return  void
	 */
	protected static function cleanUpUserObjectCache()
	{
		// Get a fake container (we need it for its platform interface)
		$container = Container::getInstance('com_FOOBAR');

		$user          = $container->platform->getUser();
		$reflectedUser = new ReflectionObject($user);

		// Clear the user group cache
		$refProperty = $reflectedUser->getProperty('_authGroups');
		$refProperty->setAccessible(true);
		$refProperty->setValue($user, []);
		$refProperty->setAccessible(false);

		// Clear the view access level cache
		$refProperty = $reflectedUser->getProperty('_authLevels');
		$refProperty->setAccessible(true);
		$refProperty->setValue($user, []);
		$refProperty->setAccessible(false);

		// Clear the authenticated actions cache. I haven't seen it used anywhere but it's there, so...
		$refProperty = $reflectedUser->getProperty('_authActions');
		$refProperty->setAccessible(true);
		$refProperty->setValue($user, []);
		$refProperty->setAccessible(false);
	}
}
