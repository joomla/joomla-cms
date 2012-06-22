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
 * Twitter API Statuses class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterStatuses extends JTwitterObject
{
	/**
	 * Method to get tweets that have been retweeted by a specified user.
	 *
	 * @param   string   $user       The user's screen name.
	 * @param   integer  $since_id   Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $count      Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                               in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean  $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                               about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   integer  $max_id     Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   integer  $page       Specifies the page of results to retrieve.
	 * @param   boolean  $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getRetweetedByUser($user, $since_id = 0, $count = 20, $entities = false, $max_id = 0, $page = 0, $trim_user = false)
	{
		$username = '?screen_name=' . (string) $user;

		// Set the API base
		$base = '/1/statuses/retweeted_by_user.json';

		// Check if a since_id is specified
		$since = '';
		if ($since_id > 0)
		{
			$since = '&since_id=' . (int) $since_id;
		}

		// Set the count string
		$count_param = '&count=' . $count;

		// Check if a max_id is specified
		$max = '';
		if ($max_id > 0)
		{
			$max = '&max_id=' . (int) $max_id;
		}

		// Check if a page is specified
		$page_num = '';
		if ($page > 0)
		{
			$page_num = '&page=' . (int) $page;
		}

		// Check if trim_user is true
		$trim = '';
		if ($trim_user)
		{
			$trim = '&trim_user=true';
		}

		// Check if entities is true
		$inc_entities = '';
		if ($entities)
		{
			$inc_entities = '&include_entities=true';
		}

		// Build the request path.
		$path = $base . $username . $since . $count_param . $max . $page_num . $trim . $inc_entities;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get a single tweet with the given ID.
	 *
	 * @param   integer  $id         The ID of the tweet to retrieve.
	 * @param   boolean  $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 * @param   boolean  $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                               about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getTweetById($id, $trim_user = false, $entities = false)
	{
		$id_string = '?id=' . (int) $id;

		// Set the API base
		$base = '/1/statuses/show.json';

		// Check if trim_user is true
		$trim = '';
		if ($trim_user)
		{
			$trim = '&trim_user=true';
		}

		// Check if entities is true
		$inc_entities = '';
		if ($entities)
		{
			$inc_entities = '&include_entities=true';
		}

		// Build the request path.
		$path = $base . $id_string . $trim . $inc_entities;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to retrieve the latest statuses from the specified user timeline.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                 in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean  $include_rts  When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean  $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                 about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $no_replies   This parameter will prevent replies from appearing in the returned timeline. This parameter is only supported
	 *                                 for JSON and XML responses.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   integer  $page         Specifies the page of results to retrieve.
	 * @param   boolean  $trim_user    When set to true, each tweet returned in a timeline will include a user object including only
	 *                                 the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getUserTimeline($user, $count = 20, $include_rts = true, $entities = false, $no_replies = false, $since_id = 0, $max_id = 0,
		$page = 0, $trim_user = false)
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
		$base = '/1/statuses/user_timeline.json';

		// Check if a since_id is specified
		$since = '';
		if ($since_id > 0)
		{
			$since = '&since_id=' . (int) $since_id;
		}

		// Set the count string
		$count_param = '&count=' . $count;

		// Check if a max_id is specified
		$max = '';
		if ($max_id > 0)
		{
			$max = '&max_id=' . (int) $max_id;
		}

		// Check if a page is specified
		$page_num = '';
		if ($page > 0)
		{
			$page_num = '&page=' . (int) $page;
		}

		// Check if trim_user is true
		$trim = '';
		if ($trim_user)
		{
			$trim = '&trim_user=true';
		}

		// Check if include_rts is true
		$rts = '';
		if ($include_rts)
		{
			$rts = '&include_rts=true';
		}

		// Check if entities is true
		$inc_entities = '';
		if ($entities)
		{
			$inc_entities = '&include_entities=true';
		}

		// Check if no_replies is true
		$ex_replies = '';
		if ($no_replies)
		{
			$ex_replies = '&exclude_replies=true';
		}

		// Build the request path.
		$path = $base . $username . $since . $count_param . $max . $page_num . $trim . $rts . $inc_entities . $ex_replies;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a tweet.
	 * 
	 * @param   JTwitterOAuth  $oauth                  The JTwitterOAuth object.
	 * @param   string         $status                 The text of the tweet.
	 * @param   integer        $in_reply_to_status_id  The ID of an existing status that the update is in reply to.
	 * @param   float          $lat                    The latitude of the location this tweet refers to.
	 * @param   float          $long                   The longitude of the location this tweet refers to.
	 * @param   string         $place_id               A place in the world.
	 * @param   boolean        $display_coordinates    Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean        $trim_user              When set to true, each tweet returned in a timeline will include a user object including only
	 *                                                 the status author's numerical ID.
	 * @param   boolean        $entities               When set to true,  each tweet will include a node called "entities,". This node offers a variety
	 * 												   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * 
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function tweet($oauth, $status, $in_reply_to_status_id = null, $lat = null, $long = null, $place_id = null, $display_coordinates = false,
		$trim_user = false, $entities = false)
	{
		// Set the API base.
		$base = '/1/statuses/update.json';

		// Set POST data.
		$data = array('status' => utf8_encode($status));

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key'),
			'status' => utf8_encode($status)
		);

		// Check if in_reply_to_status_id is specified.
		if ($in_reply_to_status_id)
		{
			$data['in_reply_to_status_id'] = $in_reply_to_status_id;
			$parameters['in_reply_to_status_id'] = $in_reply_to_status_id;
		}

		// Check if lat is specified.
		if ($lat)
		{
			$data['lat'] = $lat;
			$parameters['lat'] = $lat;
		}

		// Check if long is specified.
		if ($long)
		{
			$data['long'] = $long;
			$parameters['long'] = $long;
		}

		// Check if place_id is specified.
		if ($place_id)
		{
			$data['place_id'] = $place_id;
			$parameters['place_id'] = $place_id;
		}

		// Check if display_coordinates is true.
		if ($display_coordinates)
		{
			$data['display_coordinates'] = $display_coordinates;
			$parameters['display_coordinates'] = $display_coordinates;
		}

		// Check if trim_user is true.
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
			$parameters['trim_user'] = $trim_user;
		}

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
			$parameters['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}
}
