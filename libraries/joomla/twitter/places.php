<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Places & Geo class for the Joomla Platform.
 *
 * @since       12.3
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
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
		$this->checkRateLimit('geo', 'id/:place_id');

		// Set the API path
		$path = '/geo/id/' . $id . '.json';

		// Send the request.
		return $this->sendRequest($path);
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
		$this->checkRateLimit('geo', 'reverse_geocode');

		// Set the API path
		$path = '/geo/reverse_geocode.json';

		// Set the request parameters
		$data['lat'] = $lat;
		$data['long'] = $long;

		// Check if accuracy is specified
		if ($accuracy)
		{
			$data['accuracy'] = $accuracy;
		}

		// Check if granularity is specified
		if ($granularity)
		{
			$data['granularity'] = $granularity;
		}

		// Check if max_results is specified
		if ($max_results)
		{
			$data['max_results'] = $max_results;
		}

		// Check if callback is specified
		if ($callback)
		{
			$data['callback'] = $callback;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
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
		$this->checkRateLimit('geo', 'search');

		// Set the API path
		$path = '/geo/search.json';

		// At least one of the following parameters must be provided: lat, long, ip, or query.
		if ($lat == null && $long == null && $ip == null && $query == null)
		{
			throw new RuntimeException('At least one of the following parameters must be provided: lat, long, ip, or query.');
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

		// Check if query is specified.
		if ($query)
		{
			$data['query'] = rawurlencode($query);
		}

		// Check if ip is specified.
		if ($ip)
		{
			$data['ip'] = $ip;
		}

		// Check if granularity is specified
		if ($granularity)
		{
			$data['granularity'] = $granularity;
		}

		// Check if accuracy is specified
		if ($accuracy)
		{
			$data['accuracy'] = $accuracy;
		}

		// Check if max_results is specified
		if ($max_results)
		{
			$data['max_results'] = $max_results;
		}

		// Check if within is specified
		if ($within)
		{
			$data['contained_within'] = $within;
		}

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

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
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
		$this->checkRateLimit('geo', 'similar_places');

		// Set the API path
		$path = '/geo/similar_places.json';

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);

		// Check if within is specified
		if ($within)
		{
			$data['contained_within'] = $within;
		}

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

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
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
		$this->checkRateLimit('geo', 'place');

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

		// Set the API path
		$path = '/geo/place.json';

		// Send the request.
		return $this->sendRequest($path, 'POST', $data);
	}
}
