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
class JGoogleDataPlusComments extends JGoogleData
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
	 * List all of the comments for an activity.
	 *
	 * @param   string   $activityId  The ID of the activity to get comments for.
	 * @param   string   $fields      Used to specify the fields you want returned.
	 * @param   integer  $max         The maximum number of people to include in the response, used for paging.
	 * @param   string   $order       The order in which to sort the list of comments. Acceptable values are "ascending" and "descending".
	 * @param   string   $token       The continuation token, used to page through large result sets. To get the next page of results, set this
	 * 								  parameter to the value of "nextPageToken" from the previous response. This token may be of any length.
	 * @param   string   $alt         Specifies an alternative representation type. Acceptable values are: "json" - Use JSON format (default)
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   3.1.4
	 */
	public function listComments($activityId, $fields = null, $max = 20, $order = null, $token = null, $alt = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'activities/' . $activityId . '/comments';

			// Check if fields is specified.
			if ($fields)
			{
				$url .= '?fields=' . $fields;
			}

			// Check if max is specified.
			if ($max != 20)
			{
				$url .= (strpos($url, '?') === false) ? '?maxResults=' : '&maxResults=';
				$url .= $max;
			}

			// Check if order is specified.
			if ($order)
			{
				$url .= (strpos($url, '?') === false) ? '?orderBy=' : '&orderBy=';
				$url .= $order;
			}

			// Check of token is specified.
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
	 * Get a comment.
	 *
	 * @param   string  $id      The ID of the comment to get.
	 * @param   string  $fields  Used to specify the fields you want returned.
	 *
	 * @return  mixed  Data from Google
	 *
	 * @since   3.1.4
	 */
	public function getComment($id, $fields = null)
	{
		if ($this->isAuthenticated())
		{
			$url = $this->getOption('api.url') . 'comments/' . $id;

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
}
