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
 * Twitter API Places & Geo class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterPlaces extends JTwitterObject
{
	/**
	 * Method to get all the information about a known place.
	 *
	 * @param   string  $id  A place in the world. These IDs can be retrieved using getGeocode.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getPlace($id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/id/' . $id . '.json';

		// Send the request.
		return $this->sendRequest($base);
	}

	/**
	 * Method to get up to 20 places that can be used as a place_id when updating a status.
	 *
	 * @param   float    $lat          The latitude to search around.
	 * @param   float    $long         The longitude to search around.
	 * @param   string   $accuracy     A hint on the "region" in which to search. If a number, then this is a radius in meters,
	 * 								   but it can also take a string that is suffixed with ft to specify feet.
	 * @param   string   $granularity  This is the minimal granularity of place types to return and must be one of: poi, neighborhood,
	 * 								   city, admin or country.
	 * @param   integer  $max_results  A hint as to the number of results to return.
	 * @param   string   $callback     If supplied, the response will use the JSONP format with a callback of the given name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getGeocode($lat, $long, $accuracy = null, $granularity = null, $max_results = 0, $callback = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/reverse_geocode.json';

		// Set the request parameters
		$parameters['lat'] = $lat;
		$parameters['long'] = $long;

		// Check if accuracy is specified
		if ($accuracy)
		{
			$parameters['accuracy'] = $accuracy;
		}

		// Check if granularity is specified
		if ($granularity)
		{
			$parameters['granularity'] = $granularity;
		}

		// Check if max_results is specified
		if ($max_results)
		{
			$parameters['max_results'] = $max_results;
		}

		// Check if callback is specified
		if ($callback)
		{
			$parameters['callback'] = $callback;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to search for places that can be attached to a statuses/update.
	 *
	 * @param   float    $lat          The latitude to search around.
	 * @param   float    $long         The longitude to search around.
	 * @param   string   $query        Free-form text to match against while executing a geo-based query, best suited for finding nearby
	 * 								   locations by name.
	 * @param   string   $ip           An IP address.
	 * @param   string   $granularity  This is the minimal granularity of place types to return and must be one of: poi, neighborhood, city,
	 * 								   admin or country.
	 * @param   string   $accuracy     A hint on the "region" in which to search. If a number, then this is a radius in meters, but it can
	 * 								   also take a string that is suffixed with ft to specify feet.
	 * @param   integer  $max_results  A hint as to the number of results to return.
	 * @param   string   $within       This is the place_id which you would like to restrict the search results to.
	 * @param   string   $attribute    This parameter searches for places which have this given street address.
	 * @param   string   $callback     If supplied, the response will use the JSONP format with a callback of the given name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function search($lat = null, $long = null, $query = null, $ip = null, $granularity = null, $accuracy = null, $max_results = 0,
		$within = null, $attribute = null, $callback = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/search.json';

		// At least one of the following parameters must be provided: lat, long, ip, or query.
		if ($lat == null && $long == null && $ip == null && $query == null)
		{
			throw new RuntimeException('At least one of the following parameters must be provided: lat, long, ip, or query.');
		}

		// Check if lat is specified.
		if ($lat)
		{
			$parameters['lat'] = $lat;
		}

		// Check if long is specified.
		if ($long)
		{
			$parameters['long'] = $long;
		}

		// Check if query is specified.
		if ($query)
		{
			$parameters['query'] = rawurlencode($query);
		}

		// Check if ip is specified.
		if ($ip)
		{
			$parameters['ip'] = $ip;
		}

		// Check if granularity is specified
		if ($granularity)
		{
			$parameters['granularity'] = $granularity;
		}

		// Check if accuracy is specified
		if ($accuracy)
		{
			$parameters['accuracy'] = $accuracy;
		}

		// Check if max_results is specified
		if ($max_results)
		{
			$parameters['max_results'] = $max_results;
		}

		// Check if within is specified
		if ($within)
		{
			$parameters['contained_within'] = $within;
		}

		// Check if attribute is specified
		if ($attribute)
		{
			$parameters['attribute:street_address'] = rawurlencode($attribute);
		}

		// Check if callback is specified
		if ($callback)
		{
			$parameters['callback'] = $callback;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to locate places near the given coordinates which are similar in name.
	 *
	 * @param   float   $lat        The latitude to search around.
	 * @param   float   $long       The longitude to search around.
	 * @param   string  $name       The name a place is known as.
	 * @param   string  $within     This is the place_id which you would like to restrict the search results to.
	 * @param   string  $attribute  This parameter searches for places which have this given street address.
	 * @param   string  $callback   If supplied, the response will use the JSONP format with a callback of the given name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getSimilarPlaces($lat, $long, $name, $within = null, $attribute = null, $callback = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/similar_places.json';

		$parameters['lat'] = $lat;
		$parameters['long'] = $long;
		$parameters['name'] = rawurlencode($name);

		// Check if within is specified
		if ($within)
		{
			$parameters['contained_within'] = $within;
		}

		// Check if attribute is specified
		if ($attribute)
		{
			$parameters['attribute:street_address'] = rawurlencode($attribute);
		}

		// Check if callback is specified
		if ($callback)
		{
			$parameters['callback'] = $callback;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to create a new place object at the given latitude and longitude.
	 *
	 * @param   float   $lat        The latitude to search around.
	 * @param   float   $long       The longitude to search around.
	 * @param   string  $name       The name a place is known as.
	 * @param   string  $geo_token  The token found in the response from geo/similar_places.
	 * @param   string  $within     This is the place_id which you would like to restrict the search results to.
	 * @param   string  $attribute  This parameter searches for places which have this given street address.
	 * @param   string  $callback   If supplied, the response will use the JSONP format with a callback of the given name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function createPlace($lat, $long, $name, $geo_token, $within, $attribute = null, $callback = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array('oauth_token' => $token['key']);

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);
		$data['token'] = $geo_token;
		$data['contained_within'] = $within;

		// Check if attribute is specified
		if ($attribute)
		{
			$data['attribute:street_address'] = rawurlencode($attribute);
		}

		// Check if callback is specified
		if ($callback)
		{
			$data['callback'] = $callback;
		}

		// Set the API base
		$base = '/1/geo/place.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}
}
