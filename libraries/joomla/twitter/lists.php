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
 * Twitter API Lists class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterLists extends JTwitterObject
{
	/**
	 * Method to get all lists the authenticating or specified user subscribes to, including their own.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getAllLists($user)
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
		$base = '/1/lists/all.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get tweet timeline for members of the specified list
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer  $per_page     Specifies the number of results to retrieve per page.
	 * @param   integer  $page         Specifies the page of results to retrieve.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $include_rts  When set to either true, t or 1, the list timeline will contain native retweets (if they exist) in addition
	 * 								   to the standard stream of tweets.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getListStatuses($list, $owner = null, $since_id = 0, $max_id = 0, $per_page = 0, $page = 0, $entities = false, $include_rts = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/statuses.json';

		// Check if since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = $max_id;
		}

		// Check if per_page is specified
		if ($per_page > 0)
		{
			$data['per_page'] = $per_page;
		}

		// Check if page is specified
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if include_rts is true
		if ($include_rts > 0)
		{
			$data['include_rts'] = $include_rts;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $data);

	}

	/**
	 * Method to get the lists the specified user has been added to.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $filter  When set to true, t or 1, will return just lists the authenticating user owns, and the user represented
	 * 							  by user_id or screen_name is a member of.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getListMemberships($user, $filter = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

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

		// Check if filter is true.
		if ($filter)
		{
			$data['filter_to_owned_lists'] = $filter;
		}

		// Set the API base
		$base = '/1/lists/memberships.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $data);
	}

	/**
	 * Method to get the subscribers of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getListSubscribers($list, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/subscribers.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $data);

	}

	/**
	 * Method to remove multiple members from a list, by specifying a comma-separated list of member ids or screen names.
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string         $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string         $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function deleteListMembers($oauth, $list, $user_id = null, $screen_name = null, $owner = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username for owner is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify either a comma separated list of screen names, user IDs, or a combination of the two');
		}

		// Set the API base
		$base = '/1/lists/members/destroy_all.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to subscribe the authenticated user to the specified list.
	 *
	 * @param   JTwitterOauth  $oauth  The JTwitterOauth object.
	 * @param   mixed          $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function subscribe($oauth, $list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username for owner is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/subscribers/create.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to check if the specified user is a member of the specified list.
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $user         Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   boolean        $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a
	 * 										 variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function isListMember($oauth, $list, $user, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

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

		// Set the API base
		$base = '/1/lists/members/show.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to check if the specified user is a subscriber of the specified list.
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $user         Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   boolean        $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a
	 * 										 variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function isListSubscriber($oauth, $list, $user, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

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

		// Set the API base
		$base = '/1/lists/subscribers/show.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to unsubscribe the authenticated user from the specified list.
	 *
	 * @param   JTwitterOauth  $oauth  The JTwitterOauth object.
	 * @param   mixed          $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function unsubscribe($oauth, $list, $owner = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/subscribers/destroy.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to add multiple members to a list, by specifying a comma-separated list of member ids or screen names.
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string         $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string         $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function addListMembers($oauth, $list, $user_id = null, $screen_name = null, $owner = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if ($user_id == null && $screen_name == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify either a comma separated list of screen names, user IDs, or a combination of the two');
		}

		// Set the API base
		$base = '/1/lists/members/create_all.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the members of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getListMembers($list, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/members.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $data);

	}

	/**
	 * Method to get the specified list.
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getListById($list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/show.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $data);
	}

	/**
	 * Method to get lists of the specified (or authenticated) user.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getLists($user)
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
		$base = '/1/lists.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get a collection of the lists the specified user is subscribed to, 20 lists per page by default. Does not include the user's own lists.
	 *
	 * @param   mixed    $user   Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count  The amount of results to return per page. Defaults to 20. Maximum of 1,000 when using cursors.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getSubscriptions($user, $count = 0)
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

		// Check if count is specified.
		if ($count > 0)
		{
			$parameters['count'] = $count;
		}

		// Set the API base
		$base = '/1/lists/subscriptions.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to update the specified list
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   string         $name         The name of the list.
	 * @param   string         $mode         Whether your list is public or private. Values can be public or private. If no mode is
	 * 										 specified the list will be public.
	 * @param   string         $description  The description to give the list.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function updateList($oauth, $list, $owner = null, $name = null, $mode = null, $description = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Check if name is specified.
		if ($name)
		{
			$data['name'] = $name;
		}

		// Check if mode is specified.
		if ($mode)
		{
			$data['mode'] = $mode;
		}

		// Check if description is specified.
		if ($description)
		{
			$data['description'] = $description;
		}

		// Set the API base
		$base = '/1/lists/update.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to create a new list for the authenticated user.
	 *
	 * @param   JTwitterOauth  $oauth        The JTwitterOauth object.
	 * @param   string         $name         The name of the list.
	 * @param   string         $mode         Whether your list is public or private. Values can be public or private. If no mode is
	 * 										 specified the list will be public.
	 * @param   string         $description  The description to give the list.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function createList($oauth, $name, $mode = null, $description = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Check if name is specified.
		if ($name)
		{
			$data['name'] = $name;
		}

		// Check if mode is specified.
		if ($mode)
		{
			$data['mode'] = $mode;
		}

		// Check if description is specified.
		if ($description)
		{
			$data['description'] = $description;
		}

		// Set the API base
		$base = '/1/lists/create.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to delete a specified list.
	 *
	 * @param   JTwitterOauth  $oauth  The JTwitterOauth object.
	 * @param   mixed          $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function deleteList($oauth, $list, $owner = null)
	{
		$token = $oauth->getToken();
		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				throw new RuntimeException('The specified username for owner is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/destroy.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}
}
