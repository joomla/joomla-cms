<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Google+ data class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/google` package via Composer instead
 */
class JGoogleDataPlusActivities extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   Registry     $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   3.1.4
	 */
	public function __construct(Registry $options = null, JGoogleAuth $auth = null)
	{
	parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://www.googleapis.com/auth/plus.me');
		}
	}

	/**
	 * List all of the activities in the specified collection for a particular user.
	 *
	 * @param   string   $userId      The ID of the user to get activities for. The special value "me" can be used to indicate the authenticated user.
	 * @param   string   $collection  The collection of activities to list. Acceptable values are: "public".
	 * @param   string   $fields      Used to specify the fields you want returned.
	 * @param   integer  $max         The maximum number of people to include in the response, used for paging.
	 * @param   string   $token       The continuation token, used to page through large result sets. To get the next page of results, set this
	 *								  parameter to the value of "nextPageToken" from the previous response. This token may be of any length.
	 * @param   string   $alt         Specifies an alternative representation type. Acceptable values are: "json" - Use JSON format (default)
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   3.1.4
	 */
	public function listActivities($userId, $collection, $fields = null, $max = 10, $token = null, $alt = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'people/' . $userId . '/activities/' . $collection;

			// Check if fields is specified.
			if ($fields)
			{
				$url .= '?fields=' . $fields;
			}

			// Check if max is specified.
			if ($max != 10)
			{
				$url .= (strpos($url, '?') === false) ? '?maxResults=' : '&maxResults=';
				$url .= $max;
			}

			// Check if token is specified.
			if ($token)
			{
				$url .= (strpos($url, '?') === false) ? '?pageToken=' : '&pageToken=';
				$url .= $token;
			}

			// Check if alt is specified.
			if ($alt)
			{
				$url .= (strpos($url, '?') === false) ? '?alt=' : '&alt=';
				$url .= $alt;
			}

			$jdata = $this->auth->query($url);

			return json_decode($jdata->body, true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get an activity.
	 *
	 * @param   string  $id      The ID of the activity to get.
	 * @param   string  $fields  Used to specify the fields you want returned.
	 * @param   string  $alt     Specifies an alternative representation type. Acceptable values are: "json" - Use JSON format (default)
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   3.1.4
	 */
	public function getActivity($id, $fields = null, $alt = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'activities/' . $id;

			// Check if fields is specified.
			if ($fields)
			{
				$url .= '?fields=' . $fields;
			}

			// Check if alt is specified.
			if ($alt)
			{
				$url .= (strpos($url, '?') === false) ? '?alt=' : '&alt=';
				$url .= $alt;
			}

			$jdata = $this->auth->query($url);

			return json_decode($jdata->body, true);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Search all public activities.
	 *
	 * @param   string   $query     Full-text search query string.
	 * @param   string   $fields    Used to specify the fields you want returned.
	 * @param   string   $language  Specify the preferred language to search with. https://developers.google.com/+/api/search#available-languages
	 * @param   integer  $max       The maximum number of people to include in the response, used for paging.
	 * @param   string   $order     Specifies how to order search results. Acceptable values are "best" and "recent".
	 * @param   string   $token     The continuation token, used to page through large result sets. To get the next page of results, set this
	 * 								parameter to the value of "nextPageToken" from the previous response. This token may be of any length.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   3.1.4
	 */
	public function search($query, $fields = null, $language = null, $max = 10, $order = null, $token = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'activities?query=' . urlencode($query);

			// Check if fields is specified.
			if ($fields)
			{
				$url .= '&fields=' . $fields;
			}

			// Check if language is specified.
			if ($language)
			{
				$url .= '&language=' . $language;
			}

			// Check if max is specified.
			if ($max != 10)
			{
				$url .= '&maxResults=' . $max;
			}

			// Check if order is specified.
			if ($order)
			{
				$url .= '&orderBy=' . $order;
			}

			// Check of token is specified.
			if ($token)
			{
				$url .= '&pageToken=' . $token;
			}

			$jdata = $this->auth->query($url);

			return json_decode($jdata->body, true);
		}
		else
		{
			return false;
		}
	}
}
