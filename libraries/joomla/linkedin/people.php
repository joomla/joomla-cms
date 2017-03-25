<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API People class for the Joomla Platform.
 *
 * @since  13.1
 */
class JLinkedinPeople extends JLinkedinObject
{
	/**
	 * Method to get a member's profile.
	 *
	 * @param   string  $id        Member id of the profile you want.
	 * @param   string  $url       The public profile URL.
	 * @param   string  $fields    Request fields beyond the default ones.
	 * @param   string  $type      Choosing public or standard profile.
	 * @param   string  $language  A comma separated list of locales ordered from highest to lowest preference.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getProfile($id = null, $url = null, $fields = null, $type = 'standard', $language = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/';

		$data['format'] = 'json';

		// Check if a member id is specified.
		if ($id)
		{
			$base .= 'id=' . $id;
		}
		elseif (!$url)
		{
			$base .= '~';
		}

		// Check if profile url is specified.
		if ($url)
		{
			$base .= 'url=' . $this->oauth->safeEncode($url);

			// Choose public profile
			if (!strcmp($type, 'public'))
			{
				$base .= ':public';
			}
		}

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if language is specified.
		$header = array();

		if ($language)
		{
			$header = array('Accept-Language' => $language);
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data, $header);

		return json_decode($response->body);
	}

	/**
	 * Method to get a list of connections for a user who has granted access to his/her account.
	 *
	 * @param   string   $fields          Request fields beyond the default ones.
	 * @param   integer  $start           Starting location within the result set for paginated returns.
	 * @param   integer  $count           The number of results returned.
	 * @param   string   $modified        Values are updated or new.
	 * @param   string   $modified_since  Value as a Unix time stamp of milliseconds since epoch.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getConnections($fields = null, $start = 0, $count = 500, $modified = null, $modified_since = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people/~/connections';

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}
		// Check if count is specified.
		if ($count != 500)
		{
			$data['count'] = $count;
		}

		// Check if modified is specified.
		if ($modified)
		{
			$data['modified'] = $modified;
		}

		// Check if modified_since is specified.
		if ($modified_since)
		{
			$data['modified-since'] = $modified_since;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to get information about people.
	 *
	 * @param   string   $fields           Request fields beyond the default ones. provide 'api-standard-profile-request'
	 * 									   field for out of network profiles.
	 * @param   string   $keywords         Members who have all the keywords anywhere in their profile.
	 * @param   string   $first_name       Members with a matching first name. Matches must be exact.
	 * @param   string   $last_name        Members with a matching last name. Matches must be exactly.
	 * @param   string   $company_name     Members who have a matching company name on their profile.
	 * @param   boolean  $current_company  A value of true matches members who currently work at the company specified in the company-name
	 * 									   parameter.
	 * @param   string   $title            Matches members with that title on their profile.
	 * @param   boolean  $current_title    A value of true matches members whose title is currently the one specified in the title-name parameter.
	 * @param   string   $school_name      Members who have a matching school name on their profile.
	 * @param   string   $current_school   A value of true matches members who currently attend the school specified in the school-name parameter.
	 * @param   string   $country_code     Matches members with a location in a specific country. Values are defined in by ISO 3166 standard.
	 * 									   Country codes must be in all lower case.
	 * @param   integer  $postal_code      Matches members centered around a Postal Code. Must be combined with the country-code parameter.
	 * 									   Not supported for all countries.
	 * @param   integer  $distance         Matches members within a distance from a central point. This is measured in miles.
	 * @param   string   $facets           Facet buckets to return, e.g. location.
	 * @param   array    $facet            Array of facet values to search over. Contains values for location, industry, network, language,
	 * 									   current-company, past-company and school, in exactly this order, null must be specified for an element if no value.
	 * @param   integer  $start            Starting location within the result set for paginated returns.
	 * @param   integer  $count            The number of results returned.
	 * @param   string   $sort             Controls the search result order. There are four options: connections, recommenders,
	 * 									   distance and relevance.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function search($fields = null, $keywords = null, $first_name = null, $last_name = null, $company_name = null,
		$current_company = null, $title = null, $current_title = null, $school_name = null, $current_school = null, $country_code = null,
		$postal_code = null, $distance = null, $facets = null, $facet = null, $start = 0, $count = 10, $sort = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = '/v1/people-search';

		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Check if keywords is specified.
		if ($keywords)
		{
			$data['keywords'] = $keywords;
		}

		// Check if first_name is specified.
		if ($first_name)
		{
			$data['first-name'] = $first_name;
		}

		// Check if last_name is specified.
		if ($last_name)
		{
			$data['last-name'] = $last_name;
		}

		// Check if company-name is specified.
		if ($company_name)
		{
			$data['company-name'] = $company_name;
		}

		// Check if current_company is specified.
		if ($current_company)
		{
			$data['current-company'] = $current_company;
		}

		// Check if title is specified.
		if ($title)
		{
			$data['title'] = $title;
		}

		// Check if current_title is specified.
		if ($current_title)
		{
			$data['current-title'] = $current_title;
		}

		// Check if school_name is specified.
		if ($school_name)
		{
			$data['school-name'] = $school_name;
		}

		// Check if current_school is specified.
		if ($current_school)
		{
			$data['current-school'] = $current_school;
		}

		// Check if country_code is specified.
		if ($country_code)
		{
			$data['country-code'] = $country_code;
		}

		// Check if postal_code is specified.
		if ($postal_code)
		{
			$data['postal-code'] = $postal_code;
		}

		// Check if distance is specified.
		if ($distance)
		{
			$data['distance'] = $distance;
		}

		// Check if facets is specified.
		if ($facets)
		{
			$data['facets'] = $facets;
		}

		// Check if facet is specified.
		if ($facet)
		{
			$data['facet'] = array();

			for ($i = 0; $i < count($facet); $i++)
			{
				if ($facet[$i])
				{
					if ($i == 0)
					{
						$data['facet'][] = 'location,' . $facet[$i];
					}

					if ($i == 1)
					{
						$data['facet'][] = 'industry,' . $facet[$i];
					}

					if ($i == 2)
					{
						$data['facet'][] = 'network,' . $facet[$i];
					}

					if ($i == 3)
					{
						$data['facet'][] = 'language,' . $facet[$i];
					}

					if ($i == 4)
					{
						$data['facet'][] = 'current-company,' . $facet[$i];
					}

					if ($i == 5)
					{
						$data['facet'][] = 'past-company,' . $facet[$i];
					}

					if ($i == 6)
					{
						$data['facet'][] = 'school,' . $facet[$i];
					}
				}
			}
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Check if count is specified.
		if ($count != 10)
		{
			$data['count'] = $count;
		}

		// Check if sort is specified.
		if ($sort)
		{
			$data['sort'] = $sort;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		if (strpos($fields, 'api-standard-profile-request') === false)
		{
			return json_decode($response->body);
		}

		// Get header name.
		$name = explode('"name": "', $response->body);
		$name = explode('"', $name[1]);
		$name = $name[0];

		// Get header value.
		$value = explode('"value": "', $response->body);
		$value = explode('"', $value[1]);
		$value = $value[0];

		// Get request url.
		$url = explode('"url": "', $response->body);
		$url = explode('"', $url[1]);
		$url = $url[0];

		// Build header for out of network profile.
		$header[$name] = $value;

		// Send the request.
		$response = $this->oauth->oauthRequest($url, 'GET', $parameters, $data, $header);

		return json_decode($response->body);
	}
}
