<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google+ data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleDataPlusPeople extends JGoogleData
{
	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object
	 * @param   JGoogleAuth  $auth     Google data http client object
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		parent::__construct($options, $auth);

		if (isset($this->auth) && !$this->auth->getOption('scope'))
		{
			$this->auth->setOption('scope', 'https://www.googleapis.com/auth/plus.me');
		}
	}

	/**
	 * Get a person's profile.
	 *
	 * @param   string  $id      The ID of the person to get the profile for. The special value "me" can be used to indicate the authenticated user.
	 * @param   string  $fields  Used to specify the fields you want returned.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 */
	public function getPeople($id, $fields = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'people/' . $id;

			// Check if fields is specified.
			if ($fields)
			{
				$url .= '?fields=' . $fields;
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
	 * Search all public profiles.
	 *
	 * @param   string   $query     Specify a query string for full text search of public text in all profiles.
	 * @param   string   $fields    Used to specify the fields you want returned.
	 * @param   string   $language  Specify the preferred language to search with. https://developers.google.com/+/api/search#available-languages
	 * @param   integer  $max       The maximum number of people to include in the response, used for paging.
	 * @param   string   $token     The continuation token, used to page through large result sets. To get the next page of results, set this
	 * 								parameter to the value of "nextPageToken" from the previous response. This token may be of any length.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 */
	public function search($query, $fields = null, $language = null, $max = 10, $token = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'people?query=' . urlencode($query);

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

	/**
	 * List all of the people in the specified collection for a particular activity.
	 *
	 * @param   string   $activityId  The ID of the activity to get the list of people for.
	 * @param   string   $collection  The collection of people to list. Acceptable values are "plusoners" and "resharers".
	 * @param   string   $fields      Used to specify the fields you want returned.
	 * @param   integer  $max         The maximum number of people to include in the response, used for paging.
	 * @param   string   $token       The continuation token, used to page through large result sets. To get the next page of results, set this
	 * 								  parameter to the value of "nextPageToken" from the previous response. This token may be of any length.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   1234
	 */
	public function listByActivity($activityId, $collection, $fields = null, $max = 10, $token = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'activities/' . $activityId . '/people/' . $collection;

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

			// Check of token is specified.
			if ($token)
			{
				$url .= (strpos($url, '?') === false) ? '?pageToken=' : '&pageToken=';
				$url .= $token;
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
