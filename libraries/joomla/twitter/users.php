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
 * Twitter API Users class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterUsers extends JTwitterObject
{
	/**
	 * Method to get up to 100 users worth of extended information, specified by either ID, screen name, or combination of the two.
	 *
	 * @param   string   $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string   $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a variety of
	 * 								   metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getUsersLookup($screen_name = null, $id = null, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'lookup');

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
		$path = '/users/lookup.json';

		// Check if string_ids is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to access the profile banner in various sizes for the user with the indicated screen_name.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getUserProfileBanner($user)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'profile_banner');

		// Set the API path
		$path = '/users/profile_banner.json';

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

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method used to search for users
	 *
	 * @param   string   $query     The search query to run against people search.
	 * @param   integer  $page      Specifies the page of results to retrieve.
	 * @param   integer  $count     The number of people to retrieve. Maximum of 20 allowed per page.
	 * @param   boolean  $entities  When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function searchUsers($query, $page = 0, $count = 0, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'search');

		$data['q'] = rawurlencode($query);

		// Check if page is specified.
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if per_page is specified
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Set the API path
		$path = '/users/search.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get extended information of a given user, specified by ID or screen name as per the required id parameter.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getUser($user, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'show/:id');

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
		$path = '/users/show.json';

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get an array of users that the specified user can contribute to.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     Set to true to return IDs as strings, false to return as integers.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getContributees($user, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'contributees');

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
		$path = '/users/contributees.json';

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
	 * Method to get an array of users who can contribute to the specified account.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     Set to true to return IDs as strings, false to return as integers.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getContributors($user, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'contributors');

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
		$path = '/users/contributors.json';

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
	 * Method access to Twitter's suggested user list.
	 *
	 * @param   boolean  $lang  Restricts the suggested categories to the requested language.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSuggestions($lang = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'suggestions');

		// Set the API path
		$path = '/users/suggestions.json';

		$data = array();

		// Check if entities is true
		if ($lang)
		{
			$data['lang'] = $lang;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * method to access the users in a given category of the Twitter suggested user list.
	 *
	 * @param   string   $slug  The short name of list or a category.
	 * @param   boolean  $lang  Restricts the suggested categories to the requested language.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSuggestionsSlug($slug, $lang = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'suggestions/:slug');

		// Set the API path
		$path = '/users/suggestions/' . $slug . '.json';

		$data = array();

		// Check if entities is true
		if ($lang)
		{
			$data['lang'] = $lang;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to access the users in a given category of the Twitter suggested user list and return
	 * their most recent status if they are not a protected user.
	 *
	 * @param   string  $slug  The short name of list or a category.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSuggestionsSlugMembers($slug)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'suggestions/:slug/members');

		// Set the API path
		$path = '/users/suggestions/' . $slug . '/members.json';

		// Send the request.
		return $this->sendRequest($path);
	}
}
