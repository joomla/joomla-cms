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
 * Twitter API Statuses class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterStatuses extends JTwitterObject
{
	/**
	 * Method to get a single tweet with the given ID.
	 *
	 * @param   integer  $id         The ID of the tweet to retrieve.
	 * @param   boolean  $trimUser   When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 * @param   boolean  $entities   When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                               about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $myRetweet  When set to either true, t or 1, any statuses returned that have been retweeted by the authenticating user will
	 *                               include an additional current_user_retweet node, containing the ID of the source status for the retweet.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getTweetById($id, $trimUser = null, $entities = null, $myRetweet = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'show/:id');

		// Set the API base
		$path = '/statuses/show/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if my_retweet is specified
		if (!is_null($myRetweet))
		{
			$data['include_my_retweet'] = $myRetweet;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to retrieve the latest statuses from the specified user timeline.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                 in the count, so it is always suggested to set $includeRts to true
	 * @param   boolean  $includeRts   When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean  $noReplies    This parameter will prevent replies from appearing in the returned timeline. This parameter is only supported
	 *                                 for JSON and XML responses.
	 * @param   integer  $sinceId      Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $maxId        Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trimUser     When set to true, each tweet returned in a timeline will include a user object including only
	 *                                 the status author's numerical ID.
	 * @param   boolean  $contributor  This parameter enhances the contributors element of the status response to include the screen_name of the
	 *                                 contributor. By default only the user_id of the contributor is included.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getUserTimeline($user, $count = 20, $includeRts = null, $noReplies = null, $sinceId = 0, $maxId = 0, $trimUser = null,
		$contributor = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'user_timeline');

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

		// Set the API base
		$path = '/statuses/user_timeline.json';

		// Set the count string
		$data['count'] = $count;

		// Check if include_rts is specified
		if (!is_null($includeRts))
		{
			$data['include_rts'] = $includeRts;
		}

		// Check if no_replies is specified
		if (!is_null($noReplies))
		{
			$data['exclude_replies'] = $noReplies;
		}

		// Check if a since_id is specified
		if ($sinceId > 0)
		{
			$data['since_id'] = (int) $sinceId;
		}

		// Check if a max_id is specified
		if ($maxId > 0)
		{
			$data['max_id'] = (int) $maxId;
		}

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Check if contributor details is specified
		if (!is_null($contributor))
		{
			$data['contributor_details'] = $contributor;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to post a tweet.
	 *
	 * @param   string   $status              The text of the tweet.
	 * @param   integer  $inReplyToStatusId   The ID of an existing status that the update is in reply to.
	 * @param   float    $lat                 The latitude of the location this tweet refers to.
	 * @param   float    $long                The longitude of the location this tweet refers to.
	 * @param   string   $placeId             A place in the world.
	 * @param   boolean  $displayCoordinates  Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean  $trimUser            When set to true, each tweet returned in a timeline will include a user object including only
	 *                                        the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function tweet($status, $inReplyToStatusId = null, $lat = null, $long = null, $placeId = null, $displayCoordinates = null,
		$trimUser = null)
	{
		// Set the API base.
		$path = '/statuses/update.json';

		// Set POST data.
		$data = array('status' => utf8_encode($status));

		// Check if in_reply_to_status_id is specified.
		if ($inReplyToStatusId)
		{
			$data['in_reply_to_status_id'] = $inReplyToStatusId;
		}

		// Check if lat is specified.
		if ($lat)
		{
			$data['lat'] = $lat;
		}

		// Check if long is specified.
		if ($long)
		{
			$data['long'] = $long;
		}

		// Check if place_id is specified.
		if ($placeId)
		{
			$data['place_id'] = $placeId;
		}

		// Check if display_coordinates is specified.
		if (!is_null($displayCoordinates))
		{
			$data['display_coordinates'] = $displayCoordinates;
		}

		// Check if trim_user is specified.
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to retrieve the most recent mentions for the authenticating user.
	 *
	 * @param   integer  $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                 in the count, so it is always suggested to set $includeRts to true
	 * @param   boolean  $includeRts   When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean  $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                 about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   integer  $sinceId      Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $maxId        Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trimUser     When set to true, each tweet returned in a timeline will include a user object including only
	 *                                 the status author's numerical ID.
	 * @param   string   $contributor  This parameter enhances the contributors element of the status response to include the screen_name
	 *                                 of the contributor.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getMentions($count = 20, $includeRts = null, $entities = null, $sinceId = 0, $maxId = 0,
		$trimUser = null, $contributor = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'mentions_timeline');

		// Set the API base
		$path = '/statuses/mentions_timeline.json';

		// Set the count string
		$data['count'] = $count;

		// Check if include_rts is specified
		if (!is_null($includeRts))
		{
			$data['include_rts'] = $includeRts;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if a since_id is specified
		if ($sinceId > 0)
		{
			$data['since_id'] = (int) $sinceId;
		}

		// Check if a max_id is specified
		if ($maxId > 0)
		{
			$data['max_id'] = (int) $maxId;
		}

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Check if contributor is specified
		if (!is_null($contributor))
		{
			$data['contributor_details'] = $contributor;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get the most recent tweets of the authenticated user that have been retweeted by others.
	 *
	 * @param   integer  $count         Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                  in the count, so it is always suggested to set $includeRts to true
	 * @param   integer  $sinceId       Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   boolean  $entities      When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                  about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $userEntities  The user entities node will be disincluded when set to false.
	 * @param   integer  $maxId         Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trimUser      When set to true, each tweet returned in a timeline will include a user object including only
	 *                                  the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getRetweetsOfMe($count = 20, $sinceId = 0, $entities = null, $userEntities = null, $maxId = 0, $trimUser = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'retweets_of_me');

		// Set the API path
		$path = '/statuses/retweets_of_me.json';

		// Set the count string
		$data['count'] = $count;

		// Check if a since_id is specified
		if ($sinceId > 0)
		{
			$data['since_id'] = (int) $sinceId;
		}

		// Check if a max_id is specified
		if ($maxId > 0)
		{
			$data['max_id'] = (int) $maxId;
		}

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if entities is specified
		if (!is_null($userEntities))
		{
			$data['include_user_entities'] = $userEntities;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to show user objects of up to 100 members who retweeted the status.
	 *
	 * @param   integer  $id            The numerical ID of the desired status.
	 * @param   integer  $count         Specifies the number of retweets to try and retrieve, up to a maximum of 100.
	 * @param   integer  $cursor        Causes the list of IDs to be broken into pages of no more than 100 IDs at a time.
	 *                                  The number of IDs returned is not guaranteed to be 100 as suspended users are
	 *                                  filtered out after connections are queried. If no cursor is provided, a value of
	 *                                  -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringifyIds  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getRetweeters($id, $count = 20, $cursor = null, $stringifyIds = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'retweeters/ids');

		// Set the API path
		$path = '/statuses/retweeters/ids.json';

		// Set the status id.
		$data['id'] = $id;

		// Set the count string
		$data['count'] = $count;

		// Check if cursor is specified
		if (!is_null($cursor))
		{
			$data['cursor'] = $cursor;
		}

		// Check if entities is specified
		if (!is_null($stringifyIds))
		{
			$data['stringify_ids'] = $stringifyIds;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get up to 100 of the first retweets of a given tweet.
	 *
	 * @param   integer  $id        The numerical ID of the desired status.
	 * @param   integer  $count     Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                              in the count, so it is always suggested to set $includeRts to true
	 * @param   boolean  $trimUser  When set to true, each tweet returned in a timeline will include a user object including only
	 *                              the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getRetweetsById($id, $count = 20, $trimUser = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'retweets/:id');

		// Set the API path
		$path = '/statuses/retweets/' . $id . '.json';

		// Set the count string
		$data['count'] = $count;

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to delete the status specified by the required ID parameter.
	 *
	 * @param   integer  $id        The numerical ID of the desired status.
	 * @param   boolean  $trimUser  When set to true, each tweet returned in a timeline will include a user object including only
	 *                              the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function deleteTweet($id, $trimUser = null)
	{
		// Set the API path
		$path = '/statuses/destroy/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to retweet a tweet.
	 *
	 * @param   integer  $id        The numerical ID of the desired status.
	 * @param   boolean  $trimUser  When set to true, each tweet returned in a timeline will include a user object including only
	 *                              the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function retweet($id, $trimUser = null)
	{
		// Set the API path
		$path = '/statuses/retweet/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trimUser))
		{
			$data['trim_user'] = $trimUser;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to post a tweet with media.
	 *
	 * @param   string   $status              The text of the tweet.
	 * @param   string   $media               File to upload
	 * @param   integer  $inReplyToStatusId   The ID of an existing status that the update is in reply to.
	 * @param   float    $lat                 The latitude of the location this tweet refers to.
	 * @param   float    $long                The longitude of the location this tweet refers to.
	 * @param   string   $placeId             A place in the world.
	 * @param   boolean  $displayCoordinates  Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean  $sensitive           Set to true for content which may not be suitable for every audience.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function tweetWithMedia($status, $media, $inReplyToStatusId = null, $lat = null, $long = null, $placeId = null,
		$displayCoordinates = null, $sensitive = null)
	{
		// Set the API request path.
		$path = '/statuses/update_with_media.json';

		// Set POST data.
		$data = array(
			'status' => utf8_encode($status),
			'media[]' => "@{$media}",
		);

		$header = array('Content-Type' => 'multipart/form-data');

		// Check if in_reply_to_status_id is specified.
		if (!is_null($inReplyToStatusId))
		{
			$data['in_reply_to_status_id'] = $inReplyToStatusId;
		}

		// Check if lat is specified.
		if ($lat)
		{
			$data['lat'] = $lat;
		}

		// Check if long is specified.
		if ($long)
		{
			$data['long'] = $long;
		}

		// Check if place_id is specified.
		if ($placeId)
		{
			$data['place_id'] = $placeId;
		}

		// Check if display_coordinates is specified.
		if (!is_null($displayCoordinates))
		{
			$data['display_coordinates'] = $displayCoordinates;
		}

		// Check if sensitive is specified.
		if (!is_null($sensitive))
		{
			$data['possibly_sensitive'] = $sensitive;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data, $header);
	}

	/**
	 * Method to get information allowing the creation of an embedded representation of a Tweet on third party sites.
	 * Note: either the id or url parameters must be specified in a request. It is not necessary to include both.
	 *
	 * @param   integer  $id          The Tweet/status ID to return embed code for.
	 * @param   string   $url         The URL of the Tweet/status to be embedded.
	 * @param   integer  $maxWidth    The maximum width in pixels that the embed should be rendered at. This value is constrained to be
	 *                                between 250 and 550 pixels.
	 * @param   boolean  $hideMedia   Specifies whether the embedded Tweet should automatically expand images which were uploaded via
	 *                                POST statuses/update_with_media.
	 * @param   boolean  $hideThread  Specifies whether the embedded Tweet should automatically show the original message in the case that
	 *                                the embedded Tweet is a reply.
	 * @param   boolean  $omitScript  Specifies whether the embedded Tweet HTML should include a `<script>` element pointing to widgets.js.
	 *                                In cases where a page already includes widgets.js, setting this value to true will prevent a redundant
	 *                                script element from being included.
	 * @param   string   $align       Specifies whether the embedded Tweet should be left aligned, right aligned, or centered in the page.
	 *                                Valid values are left, right, center, and none.
	 * @param   string   $related     A value for the TWT related parameter, as described in Web Intents. This value will be forwarded to all
	 *                                Web Intents calls.
	 * @param   string   $lang        Language code for the rendered embed. This will affect the text and localization of the rendered HTML.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function getOembed($id = null, $url = null, $maxWidth = null, $hideMedia = null, $hideThread = null, $omitScript = null,
		$align = null, $related = null, $lang = null)
	{
		// Check the rate limit for remaining hits.
		$this->checkRateLimit('statuses', 'oembed');

		// Set the API request path.
		$path = '/statuses/oembed.json';

		// Determine which of $id and $url is specified.
		if ($id)
		{
			$data['id'] = $id;
		}
		elseif ($url)
		{
			$data['url'] = rawurlencode($url);
		}
		else
		{
			// We don't have a valid entry.
			throw new RuntimeException('Either the id or url parameters must be specified in a request.');
		}

		// Check if maxwidth is specified.
		if ($maxWidth)
		{
			$data['maxwidth'] = $maxWidth;
		}

		// Check if hide_media is specified.
		if (!is_null($hideMedia))
		{
			$data['hide_media'] = $hideMedia;
		}

		// Check if hide_thread is specified.
		if (!is_null($hideThread))
		{
			$data['hide_thread'] = $hideThread;
		}

		// Check if omit_script is specified.
		if (!is_null($omitScript))
		{
			$data['omit_script'] = $omitScript;
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
		return $this->sendRequest($path, 'GET', $data);
	}
}
