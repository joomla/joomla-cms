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
 * @since       12.3
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
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getFriendIds($user, $string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$parameters['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$parameters['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friends/ids.json';

		// Check if string_ids is true
		if ($string_ids)
		{
			$parameters['stringify_ids'] = $string_ids;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to display detailed friend information between two users.
	 *
	 * @param   mixed  $user_a  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $user_b  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getFriendshipDetails($user_a, $user_b)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user_a
		if (is_numeric($user_a))
		{
			$parameters['source_id'] = $user_a;
		}
		elseif (is_string($user_a))
		{
			$parameters['source_screen_name'] = $user_a;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $user_b
		if (is_numeric($user_b))
		{
			$parameters['target_id'] = $user_b;
		}
		elseif (is_string($user_b))
		{
			$parameters['target_screen_name'] = $user_b;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The second specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friendships/show.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to determine if a friendship exists.
	 *
	 * @param   mixed  $user_a  Either an integer containing the user ID or a string containing the screen name of the first user.
	 * @param   mixed  $user_b  Either an integer containing the user ID or a string containing the screen name of the second user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getFriendshipExists($user_a, $user_b)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user_a
		if (is_numeric($user_a))
		{
			$parameters['user_id_a'] = $user_a;
		}
		elseif (is_string($user_a))
		{
			$parameters['screen_name_a'] = $user_a;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The first specified username is not in the correct format; must use integer or string');
		}

		// Determine which type of data was passed for $user_b
		if (is_numeric($user_b))
		{
			$parameters['user_id_b'] = $user_b;
		}
		elseif (is_string($user_b))
		{
			$parameters['screen_name_b'] = $user_b;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The second specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/friendships/exists.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get an array of user IDs the specified user is followed by.
	 *
	 * @param   mixed    $user        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getFollowerIds($user, $string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$parameters['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$parameters['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/followers/ids.json';

		// Check if string_ids is true
		if ($string_ids)
		{
			$parameters['stringify_ids'] = $string_ids;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to determine pending requests to follow the authenticating user.
	 *
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getFriendshipsIncoming($string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		$data = array();

		// Check if string_ids is true
		if ($string_ids)
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Set the API base
		$base = '/1/friendships/incoming.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to determine every protected user for whom the authenticating user has a pending follow request.
	 *
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getFriendshipsOutgoing($string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		$data = array();

		// Check if string_ids is true
		if ($string_ids)
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Set the API base
		$base = '/1/friendships/outgoing.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Allows the authenticating users to follow the user specified in the ID parameter.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $follow  Enable notifications for the target user.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function follow($user, $follow = false)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set POST data.
		$data = array();

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

		// Set the API base
		$base = '/1/friendships/create.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Allows the authenticating users to unfollow the user specified in the ID parameter.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                              about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function unfollow($user, $entities = false)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set POST data.
		$data = array();

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

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Set the API base
		$base = '/1/friendships/destroy.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the relationship of the authenticating user to the comma separated list of up to 100 screen_names or user_ids provided.
	 *
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string  $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getFriendshipsLookup($screen_name = null, $id = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		$data = array();

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

		// Set the API base
		$base = '/1/friendships/lookup.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
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
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function updateFriendship($user, $device = false, $retweets = false)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set POST data.
		$data = array();

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

		// Check if device is true.
		if ($device)
		{
			$data['device'] = $device;
		}

		// Check if retweets is true.
		if ($retweets)
		{
			$data['retweets'] = $retweets;
		}

		// Set the API base
		$base = '/1/friendships/update.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the user ids that currently authenticated user does not want to see retweets from.
	 *
	 * @param   boolean  $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getFriendshipNoRetweetIds($string_ids = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		$data = array();

		// Check if string_ids is true
		if ($string_ids)
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Set the API base
		$base = '/1/friendships/no_retweet_ids.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
