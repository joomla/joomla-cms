<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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
	 * @param   mixed    $user        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $cursor      Causes the list of connections to be broken into pages of no more than 5000 IDs at a time.
	 * 								  The number of IDs returned is not guaranteed to be 5000 as suspended users are filtered out
	 * 								  after connections are queried. If no cursor is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 * @param   integer  $count       Specifies the number of IDs attempt retrieval of, up to a maximum of 5,000 per distinct request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendIds($user, $cursor = null, $string_ids = null, $count = 0)
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
		if ($string_ids)
		{
			$data['stringify_ids'] = $string_ids;
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
	 * @param   mixed  $user_a  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $user_b  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendshipDetails($user_a, $user_b)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'show');

		// Determine which type of data was passed for $user_a
		if (is_numeric($user_a))
		{
			$data['source_id'] = $user_a;
		}
		elseif (is_string($user_a))
		{
			$data['source_screen_name'] = $user_a;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $user_b
		if (is_numeric($user_b))
		{
			$data['target_id'] = $user_b;
		}
		elseif (is_string($user_b))
		{
			$data['target_screen_name'] = $user_b;
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
	 * @param   mixed    $user        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $cursor      Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 								  is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 								  is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 * @param   integer  $count       Specifies the number of IDs attempt retrieval of, up to a maximum of 5,000 per distinct request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFollowerIds($user, $cursor = null, $string_ids = null, $count = 0)
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
		if (!is_null($string_ids))
		{
			$data['stringify_ids'] = $string_ids;
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
	 * @param   integer  $cursor      Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 								  is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 								  is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipsIncoming($cursor = null, $string_ids = null)
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
		if (!is_null($string_ids))
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Set the API path
		$path = '/friendships/incoming.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to determine every protected user for whom the authenticating user has a pending follow request.
	 *
	 * @param   integer  $cursor      Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 								  is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 								  is provided, a value of -1 will be assumed, which is the first "page."
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipsOutgoing($cursor = null, $string_ids = null)
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
		if (!is_null($string_ids))
		{
			$data['stringify_ids'] = $string_ids;
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
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string  $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getFriendshipsLookup($screen_name = null, $id = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'lookup');

		// Set user IDs and screen names.
		if ($id)
		{
			$data['user_id'] = $id;
		}

		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}

		if ($id == null && $screen_name == null)
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
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFriendshipNoRetweetIds($string_ids = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('friendships', 'no_retweets/ids');

		$data = array();

		// Check if string_ids is specified
		if (!is_null($string_ids))
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Set the API path
		$path = '/friendships/no_retweets/ids.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}
}
