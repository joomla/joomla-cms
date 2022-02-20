<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API People class for the Joomla Platform.
 *
 * @since  3.2.0
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
	 * @since   3.2.0
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
	 * @param   string   $fields         Request fields beyond the default ones.
	 * @param   integer  $start          Starting location within the result set for paginated returns.
	 * @param   integer  $count          The number of results returned.
	 * @param   string   $modified       Values are updated or new.
	 * @param   string   $modifiedSince  Value as a Unix time stamp of milliseconds since epoch.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function getConnections($fields = null, $start = 0, $count = 500, $modified = null, $modifiedSince = null)
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
		if ($modifiedSince)
		{
			$data['modified-since'] = $modifiedSince;
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
	 * @param   string   $fields          Request fields beyond the default ones. provide 'api-standard-profile-request'
	 * 	                                  field for out of network profiles.
	 * @param   string   $keywords        Members who have all the keywords anywhere in their profile.
	 * @param   string   $firstName       Members with a matching first name. Matches must be exact.
	 * @param   string   $lastName        Members with a matching last name. Matches must be exactly.
	 * @param   string   $companyName     Members who have a matching company name on their profile.
	 * @param   boolean  $currentCompany  A value of true matches members who currently work at the company specified in the company-name
	 *                                    parameter.
	 * @param   string   $title           Matches members with that title on their profile.
	 * @param   boolean  $currentTitle    A value of true matches members whose title is currently the one specified in the title-name parameter.
	 * @param   string   $schoolName      Members who have a matching school name on their profile.
	 * @param   string   $currentSchool   A value of true matches members who currently attend the school specified in the school-name parameter.
	 * @param   string   $countryCode     Matches members with a location in a specific country. Values are defined in by ISO 3166 standard.
	 *                                    Country codes must be in all lower case.
	 * @param   integer  $postalCode      Matches members centered around a Postal Code. Must be combined with the country-code parameter.
	 *                                    Not supported for all countries.
	 * @param   integer  $distance        Matches members within a distance from a central point. This is measured in miles.
	 * @param   string   $facets          Facet buckets to return, e.g. location.
	 * @param   array    $facet           Array of facet values to search over. Contains values for location, industry, network, language,
	 *                                    current-company, past-company and school, in exactly this order,
	 *                                    null must be specified for an element if no value.
	 * @param   integer  $start           Starting location within the result set for paginated returns.
	 * @param   integer  $count           The number of results returned.
	 * @param   string   $sort            Controls the search result order. There are four options: connections, recommenders,
	 * 									  distance and relevance.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.2.0
	 */
	public function search($fields = null, $keywords = null, $firstName = null, $lastName = null, $companyName = null,
		$currentCompany = null, $title = null, $currentTitle = null, $schoolName = null, $currentSchool = null, $countryCode = null,
		$postalCode = null, $distance = null, $facets = null, $facet = null, $start = 0, $count = 10, $sort = null)
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
		if ($firstName)
		{
			$data['first-name'] = $firstName;
		}

		// Check if last_name is specified.
		if ($lastName)
		{
			$data['last-name'] = $lastName;
		}

		// Check if company-name is specified.
		if ($companyName)
		{
			$data['company-name'] = $companyName;
		}

		// Check if current_company is specified.
		if ($currentCompany)
		{
			$data['current-company'] = $currentCompany;
		}

		// Check if title is specified.
		if ($title)
		{
			$data['title'] = $title;
		}

		// Check if current_title is specified.
		if ($currentTitle)
		{
			$data['current-title'] = $currentTitle;
		}

		// Check if school_name is specified.
		if ($schoolName)
		{
			$data['school-name'] = $schoolName;
		}

		// Check if current_school is specified.
		if ($currentSchool)
		{
			$data['current-school'] = $currentSchool;
		}

		// Check if country_code is specified.
		if ($countryCode)
		{
			$data['country-code'] = $countryCode;
		}

		// Check if postal_code is specified.
		if ($postalCode)
		{
			$data['postal-code'] = $postalCode;
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

			for ($i = 0, $iMax = count($facet); $i < $iMax; $i++)
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
