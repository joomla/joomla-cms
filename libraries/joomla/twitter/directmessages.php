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
 * Twitter API Direct Messages class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterDirectmessages extends JTwitterObject
{
	/**
	 * Method to get the most recent direct messages sent to the authenticating user.
	 *
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer  $count        Specifies the number of direct messages to try and retrieve, up to a maximum of 200.
	 * @param   boolean  $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                 about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getDirectMessages($since_id = 0, $max_id =  0, $count = 20, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('direct_messages');

		// Set the API path
		$path = '/direct_messages.json';

		// Check if since_id is specified.
		if ($since_id)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id)
		{
			$data['max_id'] = $max_id;
		}

		// Check if count is specified.
		if ($count)
		{
			$data['count'] = $count;
		}

		// Check if entities is specified.
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is specified.
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to get the most recent direct messages sent by the authenticating user.
	 *
	 * @param   integer  $since_id  Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id    Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer  $count     Specifies the number of direct messages to try and retrieve, up to a maximum of 200.
	 * @param   integer  $page      Specifies the page of results to retrieve.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                              about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSentDirectMessages($since_id = 0, $max_id =  0, $count = 20, $page = 0, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('direct_messages', 'sent');

		// Set the API path
		$path = '/direct_messages/sent.json';

		// Check if since_id is specified.
		if ($since_id)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id)
		{
			$data['max_id'] = $max_id;
		}

		// Check if count is specified.
		if ($count)
		{
			$data['count'] = $count;
		}

		// Check if page is specified.
		if ($page)
		{
			$data['page'] = $page;
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
	 * Method to send a new direct message to the specified user from the authenticating user.
	 *
	 * @param   mixed   $user  Either an integer containing the user ID or a string containing the screen name.
	 * @param   string  $text  The text of your direct message. Be sure to keep the message under 140 characters.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function sendDirectMessages($user, $text)
	{
		// Set the API path
		$path = '/direct_messages/new.json';

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

		$data['text'] = $text;

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to get a single direct message, specified by an id parameter.
	 *
	 * @param   integer  $id  The ID of the direct message.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getDirectMessagesById($id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('direct_messages', 'show');

		// Set the API path
		$path = '/direct_messages/show.json';

		$data['id'] = $id;

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to delete the direct message specified in the required ID parameter.
	 *
	 * @param   integer  $id        The ID of the direct message.
	 * @param   boolean  $entities  When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                              about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function deleteDirectMessages($id, $entities = null)
	{
		// Set the API path
		$path = '/direct_messages/destroy.json';

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
