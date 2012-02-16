<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Friends class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterFriends extends JTwitterObject
{
	/**
	 * Method to get an array of user IDs the specified user follows.
	 *
	 * @param   mixed    $user        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getFriendIds($user, $string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_integer($user))
		{
			$username = '?user_id=' . $user;
		}
		elseif (is_string($user))
		{
			$username = '?screen_name=' . $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friends/ids.json';

		// Check if string_ids is true
		$stringify_ids = '';
		if ($string_ids)
		{
			$stringify_ids = '&stringify_ids=true';
		}

		// Build the request path.
		$path = $base . $username . $stringify_ids;

		// Send the request.
		return $this->sendRequest($path, 200);
	}

	/**
	 * Method to display detailed friend information between two users.
	 *
	 * @param   mixed  $user_a  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $user_b  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getFriendshipDetails($user_a, $user_b)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user_a
		if (is_integer($user_a))
		{
			$username_a = '?source_id=' . $user_a;
		}
		elseif (is_string($user_a))
		{
			$username_a = '?source_screen_name=' . $user_a;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $user_b
		if (is_integer($user_b))
		{
			$username_b = '&target_id=' . $user_b;
		}
		elseif (is_string($user_b))
		{
			$username_b = '&target_screen_name=' . $user_b;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The second specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friendships/show.json';

		// Build the request path.
		$path = $base . $username_a . $username_b;

		// Send the request.
		return $this->sendRequest($path, 200);
	}

	/**
	 * Method to determine if a friendship exists.
	 *
	 * @param   mixed  $user_a  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $user_b  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getFriendshipExists($user_a, $user_b)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user_a
		if (is_integer($user_a))
		{
			$username_a = '?user_id_a=' . $user_a;
		}
		elseif (is_string($user_a))
		{
			$username_a = '?screen_name_a=' . $user_a;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $user_b
		if (is_integer($user_b))
		{
			$username_b = '&user_id_b=' . $user_b;
		}
		elseif (is_string($user_b))
		{
			$username_b = '&screen_name_b=' . $user_b;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The second specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friendships/exists.json';

		// Build the request path.
		$path = $base . $username_a . $username_b;

		// Send the request.
		return $this->sendRequest($path, 200);
	}

	/**
	 * Method to get an array of user IDs the specified user is followed by.
	 *
	 * @param   mixed    $user        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getFollowerIds($user, $string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_integer($user))
		{
			$username = '?user_id=' . $user;
		}
		elseif (is_string($user))
		{
			$username = '?screen_name=' . $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/followers/ids.json';

		// Check if string_ids is true
		$stringify_ids = '';
		if ($string_ids)
		{
			$stringify_ids = '&stringify_ids=true';
		}

		// Build the request path.
		$path = $base . $username . $stringify_ids;

		// Send the request.
		return $this->sendRequest($path, 200);
	}
}
