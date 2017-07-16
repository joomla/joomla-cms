<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for UserHelper
 *
 * @since       3.4
 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
 */
class UserWrapper
{
	/**
	 * Helper wrapper method for addUserToGroup
	 *
	 * @param   integer  $userId   The id of the user.
	 * @param   integer  $groupId  The id of the group.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     UserHelper::addUserToGroup()
	 * @since   3.4
	 * @throws  \RuntimeException
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function addUserToGroup($userId, $groupId)
	{
		return UserHelper::addUserToGroup($userId, $groupId);
	}

	/**
	 * Helper wrapper method for getUserGroups
	 *
	 * @param   integer  $userId  The id of the user.
	 *
	 * @return  array    List of groups
	 *
	 * @see     UserHelper::addUserToGroup()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function getUserGroups($userId)
	{
		return UserHelper::getUserGroups($userId);
	}

	/**
	 * Helper wrapper method for removeUserFromGroup
	 *
	 * @param   integer  $userId   The id of the user.
	 * @param   integer  $groupId  The id of the group.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     UserHelper::removeUserFromGroup()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function removeUserFromGroup($userId, $groupId)
	{
		return UserHelper::removeUserFromGroup($userId, $groupId);
	}

	/**
	 * Helper wrapper method for setUserGroups
	 *
	 * @param   integer  $userId  The id of the user.
	 * @param   array    $groups  An array of group ids to put the user in.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     UserHelper::setUserGroups()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function setUserGroups($userId, $groups)
	{
		return UserHelper::setUserGroups($userId, $groups);
	}

	/**
	 * Helper wrapper method for getProfile
	 *
	 * @param   integer  $userId  The id of the user.
	 *
	 * @return  object
	 *
	 * @see     UserHelper::getProfile()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function getProfile($userId = 0)
	{
		return UserHelper::getProfile($userId);
	}

	/**
	 * Helper wrapper method for activateUser
	 *
	 * @param   string  $activation  Activation string
	 *
	 * @return  boolean  True on success
	 *
	 * @see     UserHelper::activateUser()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function activateUser($activation)
	{
		return UserHelper::activateUser($activation);
	}

	/**
	 * Helper wrapper method for getUserId
	 *
	 * @param   string  $username  The username to search on.
	 *
	 * @return  integer  The user id or 0 if not found.
	 *
	 * @see     UserHelper::getUserId()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function getUserId($username)
	{
		return UserHelper::getUserId($username);
	}

	/**
	 * Helper wrapper method for hashPassword
	 *
	 * @param   string  $password  The plaintext password to encrypt.
	 *
	 * @return  string  The encrypted password.
	 *
	 * @see     UserHelper::hashPassword()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function hashPassword($password)
	{
		return UserHelper::hashPassword($password);
	}

	/**
	 * Helper wrapper method for verifyPassword
	 *
	 * @param   string   $password  The plaintext password to check.
	 * @param   string   $hash      The hash to verify against.
	 * @param   integer  $user_id   ID of the user if the password hash should be updated
	 *
	 * @return  boolean  True if the password and hash match, false otherwise
	 *
	 * @see     UserHelper::verifyPassword()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function verifyPassword($password, $hash, $user_id = 0)
	{
		return UserHelper::verifyPassword($password, $hash, $user_id);
	}

	/**
	 * Helper wrapper method for getCryptedPassword
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
	 * @see     UserHelper::getCryptedPassword()
	 * @since   3.4
	 * @deprecated  4.0
	 */
	public function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false)
	{
		return UserHelper::getCryptedPassword($plaintext, $salt, $encryption, $show_encrypt);
	}

	/**
	 * Helper wrapper method for getSalt
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
	 * @see     UserHelper::getSalt()
	 * @since   3.4
	 * @deprecated  4.0
	 */
	public function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = '')
	{
		return UserHelper::getSalt($encryption, $seed, $plaintext);
	}

	/**
	 * Helper wrapper method for genRandomPassword
	 *
	 * @param   integer  $length  Length of the password to generate
	 *
	 * @return  string  Random Password
	 *
	 * @see     UserHelper::genRandomPassword()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function genRandomPassword($length = 8)
	{
		return UserHelper::genRandomPassword($length);
	}

	/**
	 * Helper wrapper method for invalidateCookie
	 *
	 * @param   string  $userId      User ID for this user
	 * @param   string  $cookieName  Series id (cookie name decoded)
	 *
	 * @return  boolean  True on success
	 *
	 * @see     UserHelper::invalidateCookie()
	 * @since   3.4
	 * @deprecated  4.0
	 */
	public function invalidateCookie($userId, $cookieName)
	{
		return UserHelper::invalidateCookie($userId, $cookieName);
	}

	/**
	 * Helper wrapper method for clearExpiredTokens
	 *
	 * @return  mixed  Database query result
	 *
	 * @see     UserHelper::clearExpiredTokens()
	 * @since   3.4
	 * @deprecated  4.0
	 */
	public function clearExpiredTokens()
	{
		return UserHelper::clearExpiredTokens();
	}

	/**
	 * Helper wrapper method for getRememberCookieData
	 *
	 * @return  mixed  An array of information from an authentication cookie or false if there is no cookie
	 *
	 * @see     UserHelper::getRememberCookieData()
	 * @since   3.4
	 * @deprecated  4.0
	 */
	public function getRememberCookieData()
	{
		return UserHelper::getRememberCookieData();
	}

	/**
	 * Helper wrapper method for getShortHashedUserAgent
	 *
	 * @return  string  A hashed user agent string with version replaced by 'abcd'
	 *
	 * @see     UserHelper::getShortHashedUserAgent()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\User\UserHelper` directly
	 */
	public function getShortHashedUserAgent()
	{
		return UserHelper::getShortHashedUserAgent();
	}
}
