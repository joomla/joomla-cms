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
 * Twitter API Search class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.3
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
	 * @param   integer  $page         The page number (starting at 1) to return, up to a max of roughly 1500 results (based on rpp * page).
	 * @param   string   $result_type  Specifies what type of search results you would prefer to receive. The current default is "mixed."
	 * @param   integer  $rpp          The number of tweets to return per page, up to a max of 100.
	 * @param   boolean  $show_user    When true, prepends ":" to the beginning of the tweet. This is useful for readers that do not display Atom's
	 * 								   author field.
	 * @param   string   $until        Returns tweets generated before the given date. Date should be formatted as YYYY-MM-DD.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								   variety of metadata about the tweet in a discrete structure, including: urls, media and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function search($query, $callback = null, $geocode = null, $lang = null, $locale = null, $page = 0, $result_type = null,
		$rpp = 0, $show_user = false, $until = null, $since_id = 0, $max_id = 0, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = 'http://search.twitter.com/search.json';

		// Set query parameter.
		$parameters['q'] = rawurlencode($query);

		// Check if callback is specified.
		if ($callback)
		{
			$parameters['callback'] = $callback;
		}

		// Check if geocode is specified.
		if ($geocode)
		{
			$parameters['geocode'] = $geocode;
		}

		// Check if lang is specified.
		if ($lang)
		{
			$parameters['lang'] = $lang;
		}

		// Check if locale is specified.
		if ($locale)
		{
			$parameters['locale'] = $locale;
		}

		// Check if page is specified.
		if ($page > 0)
		{
			$parameters['page'] = $page;
		}

		// Check if result_type is specified.
		if ($result_type)
		{
			$parameters['result_type'] = $result_type;
		}

		// Check if rpp is specified.
		if ($rpp > 0)
		{
			$parameters['rpp'] = $rpp;
		}

		// Check if show_user is true.
		if ($show_user)
		{
			$parameters['show_user'] = $show_user;
		}

		// Check if until is specified.
		if ($until)
		{
			$parameters['until'] = $until;
		}

		// Check if since_id is specified.
		if ($since_id > 0)
		{
			$parameters['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id > 0)
		{
			$parameters['max_id'] = $max_id;
		}

		// Check if entities is true.
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get the authenticated user's saved search queries.
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getSavedSearches($oauth)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/saved_searches.json';

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters);
		return json_decode($response->body);
	}

	/**
	 * Method to get the information for the saved search represented by the given id.
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 * @param   integer        $id     The ID of the saved search.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getSavedSearchesById($oauth, $id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/saved_searches/' . $id . '.json';

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters);
		return json_decode($response->body);
	}

	/**
	 * Method to create a new saved search for the authenticated user.
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 * @param   string         $query  The query of the search the user would like to save.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function createSavedSearch($oauth, $query)
	{
		// Set the API base
		$base = '/1/saved_searches/create.json';

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set POST request data
		$data['query'] = rawurlencode($query);

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to delete a saved search for the authenticating user.
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 * @param   integer        $id     The ID of the saved search.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function deleteSavedSearch($oauth, $id)
	{
		// Set the API base
		$base = '/1/saved_searches/destroy/' . $id . '.json';

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters);
		return json_decode($response->body);
	}
}
