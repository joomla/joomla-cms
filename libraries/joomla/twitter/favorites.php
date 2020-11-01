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
 * Twitter API Favorites class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterFavorites extends JTwitterObject
{
	/**
	 * Method to get the most recent favorite statuses for the authenticating or specified user.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count     Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                              in the count, so it is always suggested to set $includeRts to true
	 * @param   integer  $sinceId   Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $maxId     Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety
	 * 								of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getFavorites($user = null, $count = 20, $sinceId = 0, $maxId = 0, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('favorites', 'list');

		// Set the API path.
		$path = '/favorites/list.json';

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
		if ($sinceId > 0)
		{
			$data['since_id'] = $sinceId;
		}

		// Check if max_id is specified.
		if ($maxId > 0)
		{
			$data['max_id'] = $maxId;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
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
	 * @since   3.1.4
	 */
	public function createFavorites($id, $entities = null)
	{
		// Set the API path.
		$path = '/favorites/create.json';

		$data['id'] = $id;

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to un-favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param   integer  $id        The numerical ID of the desired status.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety
	 * 								of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function deleteFavorites($id, $entities = null)
	{
		// Set the API path.
		$path = '/favorites/destroy.json';

		$data['id'] = $id;

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}
}
