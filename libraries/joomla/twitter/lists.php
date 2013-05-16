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
	 * @param   mixed    $user     Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $reverse  Set this to true if you would like owned lists to be returned first. See description
	 * 					 above for information on how this parameter works.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getLists($user, $reverse = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'list');

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

		// Check if reverse is specified.
		if (!is_null($reverse))
		{
			$data['reverse'] = $reverse;
		}

		// Set the API path
		$path = '/lists/list.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get tweet timeline for members of the specified list
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer  $count        Specifies the number of results to retrieve per "page."
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
	public function getStatuses($list, $owner = null, $since_id = 0, $max_id = 0, $count = 0, $entities = null, $include_rts = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'statuses');

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

		// Set the API path
		$path = '/lists/statuses.json';

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

		// Check if count is specified
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if include_rts is specified
		if (!is_null($include_rts))
		{
			$data['include_rts'] = $include_rts;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);

	}

	/**
	 * Method to get the subscribers of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $cursor       Breaks the results into pages. A single page contains 20 lists. Provide a value of -1 to begin paging.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getSubscribers($list, $owner = null, $cursor = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'subscribers');

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

		// Set the API path
		$path = '/lists/subscribers.json';

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);

	}

	/**
	 * Method to remove multiple members from a list, by specifying a comma-separated list of member ids or screen names.
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function deleteMembers($list, $user_id = null, $screen_name = null, $owner = null)
	{
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

		// Set the API path
		$path = '/lists/members/destroy_all.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to subscribe the authenticated user to the specified list.
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function subscribe($list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'subscribers/create');

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

		// Set the API path
		$path = '/lists/subscribers/create.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to check if the specified user is a member of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function isMember($list, $user, $owner = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'members/show');

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

		// Set the API path
		$path = '/lists/members/show.json';

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to check if the specified user is a subscriber of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function isSubscriber($list, $user, $owner = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'subscribers/show');

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

		// Set the API path
		$path = '/lists/subscribers/show.json';

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to unsubscribe the authenticated user from the specified list.
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function unsubscribe($list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'subscribers/destroy');

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

		// Set the API path
		$path = '/lists/subscribers/destroy.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to add multiple members to a list, by specifying a comma-separated list of member ids or screen names.
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function addMembers($list, $user_id = null, $screen_name = null, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'members/create_all');

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

		// Set the API path
		$path = '/lists/members/create_all.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
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
	public function getMembers($list, $owner = null, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'members');

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

		// Set the API path
		$path = '/lists/members.json';

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);

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
		$this->checkRateLimit('lists', 'show');

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

		// Set the API path
		$path = '/lists/show.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get a collection of the lists the specified user is subscribed to, 20 lists per page by default. Does not include the user's own lists.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count   The amount of results to return per page. Defaults to 20. Maximum of 1,000 when using cursors.
	 * @param   integer  $cursor  Breaks the results into pages. Provide a value of -1 to begin paging.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getSubscriptions($user, $count = 0, $cursor = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'subscriptions');

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

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if cursor is specified.
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Set the API path
		$path = '/lists/subscriptions.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to update the specified list
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   string  $name         The name of the list.
	 * @param   string  $mode         Whether your list is public or private. Values can be public or private. If no mode is
	 * 								  specified the list will be public.
	 * @param   string  $description  The description to give the list.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function update($list, $owner = null, $name = null, $mode = null, $description = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'update');

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

		// Set the API path
		$path = '/lists/update.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to create a new list for the authenticated user.
	 *
	 * @param   string  $name         The name of the list.
	 * @param   string  $mode         Whether your list is public or private. Values can be public or private. If no mode is
	 * 								  specified the list will be public.
	 * @param   string  $description  The description to give the list.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function create($name, $mode = null, $description = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'create');

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

		// Set the API path
		$path = '/lists/create.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to delete a specified list.
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function delete($list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('lists', 'destroy');

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

		// Set the API path
		$path = '/lists/destroy.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}
}
