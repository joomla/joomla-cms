<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Jobs class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       13.1
 */
class JLinkedinJobs extends JLinkedinObject
{
	/**
	 * Method to retrieve detailed information about a job.
	 *
	 * @param   integer  $id      The unique identifier for a job.
	 * @param   string   $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getJob($id, $fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the API base
		$base = '/v1/jobs/' . $id;

		// Set request parameters.
		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to get a list of bookmarked jobs for the current member.
	 *
	 * @param   string  $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getBookmarked($fields = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the API base
		$base = '/v1/people/~/job-bookmarks';

		// Set request parameters.
		$data['format'] = 'json';

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to bookmark a job to the current user's account.
	 *
	 * @param   integer  $id  The unique identifier for a job.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function bookmark($id)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/job-bookmarks';

		// Build xml.
		$xml = '<job-bookmark><job><id>' . $id . '</id></job></job-bookmark>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);

		return $response;
	}

	/**
	 * Method to delete a bookmark.
	 *
	 * @param   integer  $id  The unique identifier for a job.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function deleteBookmark($id)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the success response code.
		$this->oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/~/job-bookmarks/' . $id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'DELETE', $parameters);

		return $response;
	}

	/**
	 * Method to retrieve job suggestions for the current user.
	 *
	 * @param   string   $fields  Request fields beyond the default ones.
	 * @param   integer  $start   Starting location within the result set for paginated returns.
	 * @param   integer  $count   The number of results returned.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function getSuggested($fields = null, $start = 0, $count = 0)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the API base
		$base = '/v1/people/~/suggestions/job-suggestions';

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
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters, $data);

		return json_decode($response->body);
	}

	/**
	 * Method to search across LinkedIn's job postings.
	 *
	 * @param   string   $fields        Request fields beyond the default ones.
	 * @param   string   $keywords      Members who have all the keywords anywhere in their profile.
	 * @param   string   $company_name  Jobs with a matching company name.
	 * @param   string   $job_title     Matches jobs with the same job title.
	 * @param   string   $country_code  Matches members with a location in a specific country. Values are defined in by ISO 3166 standard.
	 * 									Country codes must be in all lower case.
	 * @param   integer  $postal_code   Matches members centered around a Postal Code. Must be combined with the country-code parameter.
	 * 									Not supported for all countries.
	 * @param   integer  $distance      Matches members within a distance from a central point. This is measured in miles.
	 * @param   string   $facets        Facet buckets to return, e.g. location.
	 * @param   array    $facet         Array of facet values to search over. Contains values for company, date-posted, location, job-function,
	 * 									industry, and salary, in exactly this order, null must be specified for an element if no value.
	 * @param   integer  $start         Starting location within the result set for paginated returns.
	 * @param   integer  $count         The number of results returned.
	 * @param   string   $sort          Controls the search result order. There are four options: R (relationship), DA (date-posted-asc),
	 * 									DD (date-posted-desc).
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   13.1
	 */
	public function search($fields = null, $keywords = null, $company_name = null, $job_title = null, $country_code = null, $postal_code = null,
		$distance = null, $facets = null, $facet = null, $start = 0, $count = 0, $sort = null)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key']
		);

		// Set the API base
		$base = '/v1/job-search';

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

		// Check if company-name is specified.
		if ($company_name)
		{
			$data['company-name'] = $company_name;
		}

		// Check if job-title is specified.
		if ($job_title)
		{
			$data['job-title'] = $job_title;
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
						$data['facet'][] = 'company,' . $this->oauth->safeEncode($facet[$i]);
					}
					if ($i == 1)
					{
						$data['facet'][] = 'date-posted,' . $facet[$i];
					}
					if ($i == 2)
					{
						$data['facet'][] = 'location,' . $facet[$i];
					}
					if ($i == 3)
					{
						$data['facet'][] = 'job-function,' . $this->oauth->safeEncode($facet[$i]);
					}
					if ($i == 4)
					{
						$data['facet'][] = 'industry,' . $facet[$i];
					}
					if ($i == 5)
					{
						$data['facet'][] = 'salary,' . $facet[$i];
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
		if ($count > 0)
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

		return json_decode($response->body);
	}
}
