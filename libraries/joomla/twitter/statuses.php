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
		$parameters['screen_name'] = (string) $user;

		// Set the API base
		$base = '/1/statuses/retweeted_by_user.json';

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$parameters['since_id'] = (int) $since_id;
		}

		// Set the count string
		$parameters['count'] = $count;

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$parameters['max_id'] = (int) $max_id;
		}

		// Check if a page is specified
		if ($page > 0)
		{
			$parameters['page'] = (int) $page;
		}

		// Check if trim_user is true
		if ($trim_user)
		{
			$parameters['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get a single tweet with the given ID.
	 *
	 * @param   integer  $id          The ID of the tweet to retrieve.
	 * @param   boolean  $trim_user   When set to true, each tweet returned in a timeline will include a user object including only
	 *                                the status author's numerical ID.
	 * @param   boolean  $entities    When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $my_retweet  When set to either true, t or 1, any statuses returned that have been retweeted by the authenticating user will
	 * 								  include an additional current_user_retweet node, containing the ID of the source status for the retweet.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getTweetById($id, $trim_user = false, $entities = false, $my_retweet = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/show/' . $id . '.json';

		$parameters = array();

		// Check if trim_user is true
		if ($trim_user)
		{
			$parameters['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Check if my_retweet is true
		if ($my_retweet)
		{
			$parameters['incluce_my_retweet'] = $my_retweet;
		}

		// Build the request path.
		$path = $base;

		// Send the request.
		return $this->sendRequest($path, 'get', $parameters);
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

		$parameters = array();

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
		$base = '/1/statuses/user_timeline.json';

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$parameters['since_id'] = (int) $since_id;
		}

		// Set the count string
		$parameters['count'] = $count;

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$parameters['max_id'] = (int) $max_id;
		}

		// Check if a page is specified
		if ($page > 0)
		{
			$parameters['page'] = (int) $page;
		}

		// Check if trim_user is true
		if ($trim_user)
		{
			$parameters['trim_user'] = $trim_user;
		}

		// Check if include_rts is true
		if ($include_rts)
		{
			$parameters['include_rts'] = $include_rts;
		}

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Check if no_replies is true
		if ($no_replies)
		{
			$parameters['exclude_replies'] = $no_replies;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
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
		$parameters = array('oauth_token' => $oauth->getToken('key'));

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

	/**
	 * Method to retrieve the most recent mentions for the authenticating user.
	 *
	 * @param   JTwitterOAuth  $oauth        The JTwitterOAuth object.
	 * @param   integer        $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                       in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean        $include_rts  When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean        $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                       about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   integer        $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer        $max_id       Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   integer        $page         Specifies the page of results to retrieve.
	 * @param   boolean        $trim_user    When set to true, each tweet returned in a timeline will include a user object including only
	 *                                       the status author's numerical ID.
	 * @param   string         $contributor  This parameter enhances the contributors element of the status response to include the screen_name 
	 *                                       of the contributor.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getMentions($oauth, $count = 20, $include_rts = true, $entities = false, $since_id = 0, $max_id = 0,
		$page = 0, $trim_user = false, $contributor = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/mentions.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = (int) $since_id;
		}

		// Set the count string
		$data['count'] = $count;

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = (int) $max_id;
		}

		// Check if a page is specified
		if ($page > 0)
		{
			$data['page'] = (int) $page;
		}

		// Check if trim_user is true
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if include_rts is true
		if ($include_rts)
		{
			$data['include_rts'] = $include_rts;
		}

		// Check if entities is true
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Check if contributor is true
		if ($contributor)
		{
			$data['contributor_details'] = $contributor;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get the most recent retweets posted by users the specified user follows. 
	 *
	 * @param   mixed    $user       Either an integer containing the user ID or a string containing the screen name.
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
	public function getRetweetedToUser($user, $since_id = 0, $count = 20, $entities = false, $max_id = 0, $page = 0, $trim_user = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$parameters = array();

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
		$base = '/1/statuses/retweeted_by_user.json';

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$parameters['since_id'] = (int) $since_id;
		}

		// Set the count string
		$parameters['count'] = $count;

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$parameters['max_id'] = (int) $max_id;
		}

		// Check if a page is specified
		if ($page > 0)
		{
			$parameters['page'] = (int) $page;
		}

		// Check if trim_user is true
		if ($trim_user)
		{
			$parameters['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get the most recent tweets of the authenticated user that have been retweeted by others.
	 * 
	 * @param   JTwitterOAuth  $oauth      The JTwitterOAuth object.
	 * @param   integer        $since_id   Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer        $count      Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                     in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean        $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                     about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   integer        $max_id     Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   integer        $page       Specifies the page of results to retrieve.
	 * @param   boolean        $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                                     the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getRetweetsOfMe($oauth, $since_id = 0, $count = 20, $entities = false, $max_id = 0, $page = 0, $trim_user = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/retweets_of_me.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = (int) $since_id;
		}

		// Set the count string
		$data['count'] = $count;

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = (int) $max_id;
		}

		// Check if a page is specified
		if ($page > 0)
		{
			$data['page'] = (int) $page;
		}

		// Check if trim_user is true
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to show user objects of up to 100 members who retweeted the status.
	 * 
	 * @param   integer  $id     The numerical ID of the desired status.
	 * @param   integer  $count  Specifies the number of retweets to try and retrieve, up to a maximum of 100.
	 * @param   integer  $page   Specifies the page of results to retrieve.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getRetweetedBy($id, $count = 20, $page = 0)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/' . $id . '/retweeted_by.json';

		// Set the count string
		$parameters['count'] = $count;

		// Check if a page is specified
		if ($page > 0)
		{
			$parameters['page'] = (int) $page;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to show user ids of up to 100 members who retweeted the status.
	 * 
	 * @param   JTwitterOAuth  $oauth       The JTwitterOAuth object.
	 * @param   integer        $id          The numerical ID of the desired status.
	 * @param   integer        $count       Specifies the number of retweets to try and retrieve, up to a maximum of 100.
	 * @param   integer        $page        Specifies the page of results to retrieve.
	 * @param   boolean        $string_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getRetweetedByIds($oauth, $id, $count = 20, $page = 0, $string_ids = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/' . $id . '/retweeted_by.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Set the count string
		$data['count'] = $count;

		// Check if a page is specified
		if ($page > 0)
		{
			$data['page'] = (int) $page;
		}

		// Check if string_ids is true
		if ($string_ids)
		{
			$data['stringify_ids'] = $string_ids;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get up to 100 of the first retweets of a given tweet.
	 * 
	 * @param   JTwitterOAuth  $oauth      The JTwitterOAuth object.
	 * @param   integer        $id         The numerical ID of the desired status.
	 * @param   integer        $count      Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                     in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean        $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                     about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                                     the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getRetweetsById($oauth, $id, $count = 20, $entities = false, $trim_user = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/retweets/' . $id . '.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Set the count string
		$data['count'] = $count;

		// Check if trim_user is true
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to delete the status specified by the required ID parameter.
	 * 
	 * @param   JTwitterOAuth  $oauth      The JTwitterOAuth object.
	 * @param   integer        $id         The numerical ID of the desired status.
	 * @param   boolean        $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                     about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                                     the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function deleteTweet($oauth, $id, $entities = false, $trim_user = false)
	{
		// Set the API base
		$base = '/1/statuses/destroy/' . $id . '.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		$data = null;

		// Check if trim_user is true
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to retweet a tweet.
	 * 
	 * @param   JTwitterOAuth  $oauth      The JTwitterOAuth object.
	 * @param   integer        $id         The numerical ID of the desired status.
	 * @param   boolean        $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                     about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                                     the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function retweet($oauth, $id, $entities = false, $trim_user = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/statuses/retweet/' . $id . '.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		$data = null;

		// Check if trim_user is true
		if ($trim_user)
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is true
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to post a tweet with media.
	 * 
	 * @param   JTwitterOAuth  $oauth                  The JTwitterOAuth object.
	 * @param   string         $status                 The text of the tweet.
	 * @param   array          $media                  Files to upload
	 * @param   integer        $in_reply_to_status_id  The ID of an existing status that the update is in reply to.
	 * @param   float          $lat                    The latitude of the location this tweet refers to.
	 * @param   float          $long                   The longitude of the location this tweet refers to.
	 * @param   string         $place_id               A place in the world.
	 * @param   boolean        $display_coordinates    Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean        $sensitive              Set to true for content which may not be suitable for every audience.
	 * 
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function tweetWithMedia($oauth, $status, $media, $in_reply_to_status_id = null, $lat = null, $long = null, $place_id = null,
		$display_coordinates = false, $sensitive = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API request path.
		$path = 'https://upload.twitter.com/1/statuses/update_with_media.json';

		// Set POST data.
		$data = array(
			'media[]' => "@{$media}",
			'status' => utf8_encode($status)
		);

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		$header = array('Content-Type' => 'multipart/form-data', 'Expect' => '');

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

		// Check if sensitive is true.
		if ($sensitive)
		{
			$data['possibly_sensitive'] = $sensitive;
			$parameters['possibly_sensitive'] = $sensitive;
		}

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data, $header);

		// Check Media Rate Limit.
		$response_headers = $response->headers;
		if ($response_headers['X-MediaRateLimit-Remaining'] == 0)
		{
			// The IP has exceeded the Twitter API media rate limit
			throw new RuntimeException('This server has exceed the Twitter API media rate limit for the given period.  The limit will reset in '
						. $response_headers['X-MediaRateLimit-Reset'] . 'seconds.'
			);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to get information allowing the creation of an embedded representation of a Tweet on third party sites.
	 * Note: either the id or url parameters must be specified in a request. It is not necessary to include both.
	 * 
	 * @param   integer  $id           The Tweet/status ID to return embed code for.
	 * @param   string   $url          The URL of the Tweet/status to be embedded.
	 * @param   integer  $maxwidth     The maximum width in pixels that the embed should be rendered at. This value is constrained to be
	 * 									between 250 and 550 pixels.
	 * @param   boolean  $hide_media   Specifies whether the embedded Tweet should automatically expand images which were uploaded via
	 * 									POST statuses/update_with_media.
	 * @param   boolean  $hide_thread  Specifies whether the embedded Tweet should automatically show the original message in the case that
	 * 									the embedded Tweet is a reply.
	 * @param   boolean  $omit_script  Specifies whether the embedded Tweet HTML should include a <script> element pointing to widgets.js. In cases where
	 * 									a page already includes widgets.js, setting this value to true will prevent a redundant script element from being included.
	 * @param   string   $align        Specifies whether the embedded Tweet should be left aligned, right aligned, or centered in the page.
	 * 									Valid values are left, right, center, and none.
	 * @param   string   $related      A value for the TWT related parameter, as described in Web Intents. This value will be forwarded to all
	 * 									Web Intents calls.
	 * @param   string   $lang         Language code for the rendered embed. This will affect the text and localization of the rendered HTML.
	 * 
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getOembed($id = null, $url = null, $maxwidth = null, $hide_media = false, $hide_thread = false, $omit_script = false,
		$align = null, $related = null, $lang = null)
	{
		// Check the rate limit for remaining hits.
		$this->checkRateLimit();

		// Set the API request path.
		$base = '/1/statuses/oembed.json';

		// Determine which of $id and $url is specified.
		if ($id)
		{
			$parameters['id'] = $id;
		}
		elseif ($url)
		{
			$parameters['url'] = rawurlencode($url);
		}
		else
		{
			// We don't have a valid entry.
			throw new RuntimeException('Either the id or url parameters must be specified in a request.');
		}

		// Check if maxwidth is specified.
		if ($maxwidth)
		{
			$data['maxwidth'] = $maxwidth;
		}

		// Check if hide_media is true.
		if ($hide_media)
		{
			$data['hide_media'] = $hide_media;
		}

		// Check if hide_thread is true.
		if ($hide_thread)
		{
			$data['hide_thread'] = $hide_thread;
		}

		// Check if omit_script is true.
		if ($omit_script)
		{
			$data['omit_script'] = $omit_script;
		}

		// Check if align is specified.
		if ($align)
		{
			$data['align'] = $align;
		}

		// Check if related is specified.
		if ($related)
		{
			$data['related'] = $related;
		}

		// Check if lang is specified.
		if ($lang)
		{
			$data['lang'] = $lang;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}
}
