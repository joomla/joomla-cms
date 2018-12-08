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
 * Twitter API Search class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
class JTwittersearch extends JTwitterObject
{
	/**
	 * Method to get tweets that match a specified query.
	 *
	 * @param   string   $query        Search query. Should be URL encoded. Queries will be limited by complexity.
	 * @param   string   $callback     If supplied, the response will use the JSONP format with a callback of the given name
	 * @param   string   $geocode      Returns tweets by users located within a given radius of the given latitude/longitude. The parameter value is
	 * 								   specified by "latitude,longitude,radius", where radius units must be specified as either "mi" (miles) or "km" (kilometers).
	 * @param   string   $lang         Restricts tweets to the given language, given by an ISO 639-1 code.
	 * @param   string   $locale       Specify the language of the query you are sending (only ja is currently effective). This is intended for
	 * 								   language-specific clients and the default should work in the majority of cases.
	 * @param   string   $result_type  Specifies what type of search results you would prefer to receive. The current default is "mixed."
	 * @param   integer  $count        The number of tweets to return per page, up to a maximum of 100. Defaults to 15.
	 * @param   string   $until        Returns tweets generated before the given date. Date should be formatted as YYYY-MM-DD.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discrete structure, including: urls, media and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function search($query, $callback = null, $geocode = null, $lang = null, $locale = null, $result_type = null, $count = 15,
		$until = null, $since_id = 0, $max_id = 0, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('search', 'tweets');

		// Set the API path
		$path = '/search/tweets.json';

		// Set query parameter.
		$data['q'] = rawurlencode($query);

		// Check if callback is specified.
		if ($callback)
		{
			$data['callback'] = $callback;
		}

		// Check if geocode is specified.
		if ($geocode)
		{
			$data['geocode'] = $geocode;
		}

		// Check if lang is specified.
		if ($lang)
		{
			$data['lang'] = $lang;
		}

		// Check if locale is specified.
		if ($locale)
		{
			$data['locale'] = $locale;
		}

		// Check if result_type is specified.
		if ($result_type)
		{
			$data['result_type'] = $result_type;
		}

		// Check if count is specified.
		if ($count != 15)
		{
			$data['count'] = $count;
		}

		// Check if until is specified.
		if ($until)
		{
			$data['until'] = $until;
		}

		// Check if since_id is specified.
		if ($since_id > 0)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id > 0)
		{
			$data['max_id'] = $max_id;
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
	 * Method to get the authenticated user's saved search queries.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSavedSearches()
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('saved_searches', 'list');

		// Set the API path
		$path = '/saved_searches/list.json';

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the information for the saved search represented by the given id.
	 *
	 * @param   integer  $id  The ID of the saved search.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function getSavedSearchesById($id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('saved_searches', 'show/:id');

		// Set the API path
		$path = '/saved_searches/show/' . $id . '.json';

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to create a new saved search for the authenticated user.
	 *
	 * @param   string  $query  The query of the search the user would like to save.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function createSavedSearch($query)
	{
		// Set the API path
		$path = '/saved_searches/create.json';

		// Set POST request data
		$data['query'] = rawurlencode($query);

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}

	/**
	 * Method to delete a saved search for the authenticating user.
	 *
	 * @param   integer  $id  The ID of the saved search.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 */
	public function deleteSavedSearch($id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('saved_searches', 'destroy/:id');

		// Set the API path
		$path = '/saved_searches/destroy/' . $id . '.json';

		// Send the request.
		return $this->sendRequest($path, 'POST');
	}
}
