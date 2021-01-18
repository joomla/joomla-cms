<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Friends class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterFriends extends JTwitterObject
{
	/**
	 * Method to get an array of user IDs the specified user follows.
	 *
	 * @param   mixed    $user       Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $cursor     Causes the list of connections to be broken into pages of no more than 5000 IDs at a time.
	 * 							     The number of IDs returned is not guaranteed to be 5000 as suspended users are filtered out
	 * 								 after connections are queried. If no cursor is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringIds  Set to true to return IDs as strings, false to return as integers.
	 * @param   integer  $count      Specifies the number of IDs attempt retrieval of, up to a maximum of 5,000 per distinct request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendIds($user, $cursor = null, $stringIds = null, $count = 0)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friends', 'ids');

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if string_ids is true
		if ($stringIds)
		{
			$data['stringify_ids'] = $stringIds;
		}

		// Check if count is specified
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Set the API path
		$path = '/friends/ids.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to display detailed friend information between two users.
	 *
	 * @param   mixed  $userA  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $userB  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendshipDetails($userA, $userB)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'show');

		// Determine which type of data was passed for $userA
		if (is_numeric($userA))
		{
			$data['source_id'] = $userA;
		}
		elseif (is_string($userA))
		{
			$data['source_screen_name'] = $userA;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $userB
		if (is_numeric($userB))
		{
			$data['target_id'] = $userB;
		}
		elseif (is_string($userB))
		{
			$data['target_screen_name'] = $userB;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The second specified username is not in the correct format; must use integer or string');
		}

		// Set the API path
		$path = '/friendships/show.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get an array of user IDs the specified user is followed by.
	 *
	 * @param   mixed    $user       Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $cursor     Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 *                               is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 								 is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringIds  Set to true to return IDs as strings, false to return as integers.
	 * @param   integer  $count      Specifies the number of IDs attempt retrieval of, up to a maximum of 5,000 per distinct request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFollowerIds($user, $cursor = null, $stringIds = null, $count = 0)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('followers', 'ids');

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API path
		$path = '/followers/ids.json';

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if string_ids is specified
		if (!is_null($stringIds))
		{
			$data['stringify_ids'] = $stringIds;
		}

		// Check if count is specified
		if (!is_null($count))
		{
			$data['count'] = $count;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to determine pending requests to follow the authenticating user.
	 *
	 * @param   integer  $cursor     Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 								 is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 								 is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringIds  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipsIncoming($cursor = null, $stringIds = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'incoming');

		$data = array();

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if string_ids is specified
		if (!is_null($stringIds))
		{
			$data['stringify_ids'] = $stringIds;
		}

		// Set the API path
		$path = '/friendships/incoming.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to determine every protected user for whom the authenticating user has a pending follow request.
	 *
	 * @param   integer  $cursor     Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 								 is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 *                               is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringIds  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipsOutgoing($cursor = null, $stringIds = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'outgoing');

		$data = array();

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if string_ids is specified
		if (!is_null($stringIds))
		{
			$data['stringify_ids'] = $stringIds;
		}

		// Set the API path
		$path = '/friendships/outgoing.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Allows the authenticating users to follow the user specified in the ID parameter.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $follow  Enable notifications for the target user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function follow($user, $follow = false)
	{
		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Check if follow is true
		if ($follow)
		{
			$data['follow'] = $follow;
		}

		// Set the API path
		$path = '/friendships/create.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Allows the authenticating users to unfollow the user specified in the ID parameter.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function unfollow($user)
	{
		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API path
		$path = '/friendships/destroy.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to get the relationship of the authenticating user to the comma separated list of up to 100 screen_names or user_ids provided.
	 *
	 * @param   string  $screenName  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string  $id          A comma separated list of user IDs, up to 100 are allowed in a single request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendshipsLookup($screenName = null, $id = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'lookup');

		// Set user IDs and screen names.
		if ($id)
		{
			$data['user_id'] = $id;
		}

		if ($screenName)
		{
			$data['screen_name'] = $screenName;
		}

		if ($id == null && $screenName == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify either a comma separated list of screen names, user IDs, or a combination of the two');
		}

		// Set the API path
		$path = '/friendships/lookup.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Allows one to enable or disable retweets and device notifications from the specified user.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $device    Enable/disable device notifications from the target user.
	 * @param   boolean  $retweets  Enable/disable retweets from the target user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function updateFriendship($user, $device = null, $retweets = null)
	{
		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Check if device is specified.
		if (!is_null($device))
		{
			$data['device'] = $device;
		}

		// Check if retweets is specified.
		if (!is_null($retweets))
		{
			$data['retweets'] = $retweets;
		}

		// Set the API path
		$path = '/friendships/update.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to get the user ids that currently authenticated user does not want to see retweets from.
	 *
	 * @param   boolean  $stringIds  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipNoRetweetIds($stringIds = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'no_retweets/ids');

		$data = array();

		// Check if string_ids is specified
		if (!is_null($stringIds))
		{
			$data['stringify_ids'] = $stringIds;
		}

		// Set the API path
		$path = '/friendships/no_retweets/ids.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}
}
