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
 * Twitter API Users class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getUsersLookup($screen_name = null, $id = null, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set user IDs and screen names.
		if ($id)
		{
			$parameters['user_id'] = $id;
		}
		if ($screen_name)
		{
			$parameters['screen_name'] = $screen_name;
		}
		if ($id == null && $screen_name == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify either a comma separated list of screen names, user IDs, or a combination of the two');
		}

		// Set the API base
		$base = '/1/users/lookup.json';

		// Check if string_ids is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'post', $parameters);
	}

	/**
	 * Method to access the profile image in various sizes for the user with the indicated screen_name.
	 *
	 * @param   string  $screen_name  The screen name of the user for whom to return results for.
	 * 								  Helpful for disambiguating when a valid screen name is also a user ID.
	 * @param   string  $size         Specifies the size of image to fetch. Not specifying a size will give the default, normal size of 48px by 48px.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getUserProfileImage($screen_name, $size = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/users/lookup.json';

		$parameters['screen_name'] = $screen_name;

		// Check if string_ids is true
		if ($size)
		{
			$parameters['size'] = $size;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method used to search for users
	 *
	 * @param   JTwitterOAuth  $oauth     The JTwitterOAuth object.
	 * @param   string         $query     The search query to run against people search.
	 * @param   integer        $page      Specifies the page of results to retrieve.
	 * @param   integer        $per_page  The number of people to retrieve. Maxiumum of 20 allowed per page.
	 * @param   boolean        $entities  When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 									  variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function searchUsers($oauth, $query, $page = 0, $per_page = 0, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		$data['q'] = rawurlencode($query);

		// Check if page is specified.
		if ($page > 0 )
		{
			$data['page'] = $page;
		}

		// Check if per_page is specified
		if ($per_page > 0)
		{
			$data['per_page'] = $per_page;
		}

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Set the API base
		$base = '/1/users/search.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);

		// Check Feature Rate Limit.
		$response_headers = $response->headers;
		if ($response_headers['X-FeatureRateLimit-Remaining'] == 0)
		{
			// The IP has exceeded the Twitter API media rate limit
			throw new RuntimeException('This server has exceed the Twitter API media rate limit for the given period.  The limit will reset in '
						. $response_headers['X-FeatureRateLimit-Reset'] . 'seconds.'
			);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to get extended information of a given user, specified by ID or screen name as per the required id parameter.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getUser($user, $entities = true)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_integer($user))
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
		$base = '/1/users/show.json';

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getContributees($user, $entities = true, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_integer($user))
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
		$base = '/1/users/contributees.json';

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status)
		{
			$parameters['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
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
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getContributors($user, $entities = true, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_integer($user))
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
		$base = '/1/users/contributors.json';

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status)
		{
			$parameters['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method access to Twitter's suggested user list.
	 *
	 * @param   boolean  $lang  Restricts the suggested categories to the requested language.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getSuggestions($lang = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/users/suggestions.json';

		$parameters = array();

		// Check if entities is true
		if ($lang)
		{
			$parameters['lang'] = $lang;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * method to access the users in a given category of the Twitter suggested user list.
	 *
	 * @param   string   $slug  The short name of list or a category.
	 * @param   boolean  $lang  Restricts the suggested categories to the requested language.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getSuggestionsSlug($slug, $lang = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/users/suggestions/' . $slug . '.json';

		$parameters = array();

		// Check if entities is true
		if ($lang)
		{
			$parameters['lang'] = $lang;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to access the users in a given category of the Twitter suggested user list and return
	 * their most recent status if they are not a protected user.
	 *
	 * @param   string  $slug  The short name of list or a category.
	 * @param   string  $lang  Restricts the suggested categories to the requested language.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getSuggestionsSlugMembers($slug, $lang = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/users/suggestions/' . $slug . '/members.json';

		// Send the request.
		return $this->sendRequest($base, 'get', array());
	}
}
