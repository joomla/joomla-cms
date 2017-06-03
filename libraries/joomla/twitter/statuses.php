<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Statuses class for the Joomla Platform.
 *
 * @since       12.3
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterStatuses extends JTwitterObject
{
	/**
	 * Method to get a single tweet with the given ID.
	 *
	 * @param   integer  $id          The ID of the tweet to retrieve.
	 * @param   boolean  $trim_user   When set to true, each tweet returned in a timeline will include a user object including only
	 *                                the status author's numerical ID.
	 * @param   boolean  $entities    When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $my_retweet  When set to either true, t or 1, any statuses returned that have been retweeted by the authenticating user will
	 *                                include an additional current_user_retweet node, containing the ID of the source status for the retweet.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getTweetById($id, $trim_user = null, $entities = null, $my_retweet = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'show/:id');

		// Set the API base
		$path = '/statuses/show/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if my_retweet is specified
		if (!is_null($my_retweet))
		{
			$data['include_my_retweet'] = $my_retweet;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to retrieve the latest statuses from the specified user timeline.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                 in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean  $include_rts  When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean  $no_replies   This parameter will prevent replies from appearing in the returned timeline. This parameter is only supported
	 *                                 for JSON and XML responses.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trim_user    When set to true, each tweet returned in a timeline will include a user object including only
	 *                                 the status author's numerical ID.
	 * @param   boolean  $contributor  This parameter enhances the contributors element of the status response to include the screen_name of the
	 *                                 contributor. By default only the user_id of the contributor is included.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getUserTimeline($user, $count = 20, $include_rts = null, $no_replies = null, $since_id = 0, $max_id = 0, $trim_user = null,
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
		if (!is_null($include_rts))
		{
			$data['include_rts'] = $include_rts;
		}

		// Check if no_replies is specified
		if (!is_null($no_replies))
		{
			$data['exclude_replies'] = $no_replies;
		}

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = (int) $since_id;
		}

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = (int) $max_id;
		}

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
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
	 * @param   string   $status                 The text of the tweet.
	 * @param   integer  $in_reply_to_status_id  The ID of an existing status that the update is in reply to.
	 * @param   float    $lat                    The latitude of the location this tweet refers to.
	 * @param   float    $long                   The longitude of the location this tweet refers to.
	 * @param   string   $place_id               A place in the world.
	 * @param   boolean  $display_coordinates    Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean  $trim_user              When set to true, each tweet returned in a timeline will include a user object including only
	 *                                           the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function tweet($status, $in_reply_to_status_id = null, $lat = null, $long = null, $place_id = null, $display_coordinates = null,
		$trim_user = null)
	{
		// Set the API base.
		$path = '/statuses/update.json';

		// Set POST data.
		$data = array('status' => utf8_encode($status));

		// Check if in_reply_to_status_id is specified.
		if ($in_reply_to_status_id)
		{
			$data['in_reply_to_status_id'] = $in_reply_to_status_id;
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
		if ($place_id)
		{
			$data['place_id'] = $place_id;
		}

		// Check if display_coordinates is specified.
		if (!is_null($display_coordinates))
		{
			$data['display_coordinates'] = $display_coordinates;
		}

		// Check if trim_user is specified.
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to retrieve the most recent mentions for the authenticating user.
	 *
	 * @param   integer  $count        Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                 in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean  $include_rts  When set to true, the timeline will contain native retweets in addition to the standard stream of tweets.
	 * @param   boolean  $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                 about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trim_user    When set to true, each tweet returned in a timeline will include a user object including only
	 *                                 the status author's numerical ID.
	 * @param   string   $contributor  This parameter enhances the contributors element of the status response to include the screen_name
	 *                                 of the contributor.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getMentions($count = 20, $include_rts = null, $entities = null, $since_id = 0, $max_id = 0,
		$trim_user = null, $contributor = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'mentions_timeline');

		// Set the API base
		$path = '/statuses/mentions_timeline.json';

		// Set the count string
		$data['count'] = $count;

		// Check if include_rts is specified
		if (!is_null($include_rts))
		{
			$data['include_rts'] = $include_rts;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = (int) $since_id;
		}

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = (int) $max_id;
		}

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
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
	 * @param   integer  $count          Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                                   in the count, so it is always suggested to set $include_rts to true
	 * @param   integer  $since_id       Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   boolean  $entities       When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                   about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $user_entities  The user entities node will be disincluded when set to false.
	 * @param   integer  $max_id         Returns results with an ID less than (that is, older than) the specified ID.
	 * @param   boolean  $trim_user      When set to true, each tweet returned in a timeline will include a user object including only
	 *                                   the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getRetweetsOfMe($count = 20, $since_id = 0, $entities = null, $user_entities = null, $max_id = 0, $trim_user = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'retweets_of_me');

		// Set the API path
		$path = '/statuses/retweets_of_me.json';

		// Set the count string
		$data['count'] = $count;

		// Check if a since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = (int) $since_id;
		}

		// Check if a max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = (int) $max_id;
		}

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if entities is specified
		if (!is_null($user_entities))
		{
			$data['include_user_entities'] = $user_entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to show user objects of up to 100 members who retweeted the status.
	 *
	 * @param   integer  $id             The numerical ID of the desired status.
	 * @param   integer  $count          Specifies the number of retweets to try and retrieve, up to a maximum of 100.
	 * @param   integer  $cursor         Causes the list of IDs to be broken into pages of no more than 100 IDs at a time.
	 *                                   The number of IDs returned is not guaranteed to be 100 as suspended users are
	 *                                   filtered out after connections are queried. If no cursor is provided, a value of
	 *                                   -1 will be assumed, which is the first "page."
	 * @param   boolean  $stringify_ids  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getRetweeters($id, $count = 20, $cursor = null, $stringify_ids = null)
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
		if (!is_null($stringify_ids))
		{
			$data['stringify_ids'] = $stringify_ids;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get up to 100 of the first retweets of a given tweet.
	 *
	 * @param   integer  $id         The numerical ID of the desired status.
	 * @param   integer  $count      Specifies the number of tweets to try and retrieve, up to a maximum of 200.  Retweets are always included
	 *                               in the count, so it is always suggested to set $include_rts to true
	 * @param   boolean  $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getRetweetsById($id, $count = 20, $trim_user = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('statuses', 'retweets/:id');

		// Set the API path
		$path = '/statuses/retweets/' . $id . '.json';

		// Set the count string
		$data['count'] = $count;

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to delete the status specified by the required ID parameter.
	 *
	 * @param   integer  $id         The numerical ID of the desired status.
	 * @param   boolean  $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function deleteTweet($id, $trim_user = null)
	{
		// Set the API path
		$path = '/statuses/destroy/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to retweet a tweet.
	 *
	 * @param   integer  $id         The numerical ID of the desired status.
	 * @param   boolean  $trim_user  When set to true, each tweet returned in a timeline will include a user object including only
	 *                               the status author's numerical ID.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function retweet($id, $trim_user = null)
	{
		// Set the API path
		$path = '/statuses/retweet/' . $id . '.json';

		$data = array();

		// Check if trim_user is specified
		if (!is_null($trim_user))
		{
			$data['trim_user'] = $trim_user;
		}

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to post a tweet with media.
	 *
	 * @param   string   $status                 The text of the tweet.
	 * @param   string   $media                  File to upload
	 * @param   integer  $in_reply_to_status_id  The ID of an existing status that the update is in reply to.
	 * @param   float    $lat                    The latitude of the location this tweet refers to.
	 * @param   float    $long                   The longitude of the location this tweet refers to.
	 * @param   string   $place_id               A place in the world.
	 * @param   boolean  $display_coordinates    Whether or not to put a pin on the exact coordinates a tweet has been sent from.
	 * @param   boolean  $sensitive              Set to true for content which may not be suitable for every audience.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function tweetWithMedia($status, $media, $in_reply_to_status_id = null, $lat = null, $long = null, $place_id = null,
		$display_coordinates = null, $sensitive = null)
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
		if (!is_null($in_reply_to_status_id))
		{
			$data['in_reply_to_status_id'] = $in_reply_to_status_id;
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
		if ($place_id)
		{
			$data['place_id'] = $place_id;
		}

		// Check if display_coordinates is specified.
		if (!is_null($display_coordinates))
		{
			$data['display_coordinates'] = $display_coordinates;
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
	 * @param   integer  $id           The Tweet/status ID to return embed code for.
	 * @param   string   $url          The URL of the Tweet/status to be embedded.
	 * @param   integer  $maxwidth     The maximum width in pixels that the embed should be rendered at. This value is constrained to be
	 *                                 between 250 and 550 pixels.
	 * @param   boolean  $hide_media   Specifies whether the embedded Tweet should automatically expand images which were uploaded via
	 *                                 POST statuses/update_with_media.
	 * @param   boolean  $hide_thread  Specifies whether the embedded Tweet should automatically show the original message in the case that
	 *                                 the embedded Tweet is a reply.
	 * @param   boolean  $omit_script  Specifies whether the embedded Tweet HTML should include a `<script>` element pointing to widgets.js.
	 *                                 In cases where a page already includes widgets.js, setting this value to true will prevent a redundant
	 *                                 script element from being included.
	 * @param   string   $align        Specifies whether the embedded Tweet should be left aligned, right aligned, or centered in the page.
	 *                                 Valid values are left, right, center, and none.
	 * @param   string   $related      A value for the TWT related parameter, as described in Web Intents. This value will be forwarded to all
	 *                                 Web Intents calls.
	 * @param   string   $lang         Language code for the rendered embed. This will affect the text and localization of the rendered HTML.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getOembed($id = null, $url = null, $maxwidth = null, $hide_media = null, $hide_thread = null, $omit_script = null,
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
		if ($maxwidth)
		{
			$data['maxwidth'] = $maxwidth;
		}

		// Check if hide_media is specified.
		if (!is_null($hide_media))
		{
			$data['hide_media'] = $hide_media;
		}

		// Check if hide_thread is specified.
		if (!is_null($hide_thread))
		{
			$data['hide_thread'] = $hide_thread;
		}

		// Check if omit_script is specified.
		if (!is_null($omit_script))
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
		return $this->sendRequest($path, 'GET', $data);
	}
}
