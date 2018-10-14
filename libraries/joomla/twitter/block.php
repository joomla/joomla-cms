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
 * Twitter API Block class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwitterBlock extends JTwitterObject
{
	/**
	 * Method to get the user ids the authenticating user is blocking.
	 *
	 * @param   boolean  $stringify_ids  Provide this option to have ids returned as strings instead.
	 * @param   integer  $cursor         Causes the list of IDs to be broken into pages of no more than 5000 IDs at a time. The number of IDs returned
	 * 									 is not guaranteed to be 5000 as suspended users are filtered out after connections are queried. If no cursor
	 * 									 is provided, a value of -1 will be assumed, which is the first "page."
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getBlocking($stringify_ids = null, $cursor = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('blocks', 'ids');

		$data = array();

		// Check if stringify_ids is specified
		if (!is_null($stringify_ids))
		{
			$data['stringify_ids'] = $stringify_ids;
		}

		// Check if cursor is specified
		if (!is_null($stringify_ids))
		{
			$data['cursor'] = $cursor;
		}

		// Set the API path
		$path = '/blocks/ids.json';

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}

	/**
	 * Method to block the specified user from following the authenticating user.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function block($user, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('blocks', 'create');

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

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_statuses is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API path
		$path = '/blocks/create.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to unblock the specified user from following the authenticating user.
	 *
	 * @param   mixed    $user         Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function unblock($user, $entities = null, $skip_status = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('blocks', 'destroy');

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

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_statuses is specified
		if (!is_null($skip_status))
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API path
		$path = '/blocks/destroy.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}
}
