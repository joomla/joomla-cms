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
 * Twitter API Favorites class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterFavorites extends JTwitterObject
{
	/**
	 * Method to get the most recent favorite statuses for the authenticating or specified user.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count     Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                              in the count, so it is always suggested to set $include_rts to true
	 * @param   integer  $since_id  Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id    Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   integer  $page      Specifies the page of results to retrieve.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety
	 * 								of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getFavorites($user = null, $count = 20, $since_id = 0, $max_id = 0, $page = 0, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base.
		$base = '/1/favorites.json';

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}

		// Set the count string
		$data['count'] = $count;

		// Check if since_id is specified.
		if ($since_id > 0)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id > 0)
		{
			$data['max_id'] = $max_id;
		}

		// Check if page is specified.
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to favorite the status specified in the ID parameter as the authenticating user
	 *
	 * @param   integer  $id        The numerical ID of the desired status.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety
	 * 								of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function createFavorites($id, $entities = false)
	{
		// Set the API base.
		$base = '/1/favorites/create/' . $id . '.json';

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Check if entities is true.
		$data = array();
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to un-favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param   integer   $id  The numerical ID of the desired status.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function deleteFavorites($id)
	{
		// Set the API base.
		$base = '/1/favorites/destroy/' . $id . '.json';

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters);
		return json_decode($response->body);
	}
}
