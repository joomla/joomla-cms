<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Jobs class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinJobs extends JLinkedinObject
{
	/**
	 * Method to retrieve detailed information about a job.
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   integer         $id      The unique identifier for a job.
	 * @param   string          $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getJob($oauth, $id, $fields = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to get a list of bookmarked jobs for the current member.
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   string          $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getBookmarked($oauth, $fields = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to bookmark a job to the current user's account.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   integer         $id     The unique identifier for a job.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function bookmark($oauth, $id)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 201);

		// Set the API base
		$base = '/v1/people/~/job-bookmarks';

		// Build xml.
		$xml = '<job-bookmark><job><id>' . $id . '</id></job></job-bookmark>';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $xml, $header);
		return $response;
	}

	/**
	 * Method to delete a bookmark.
	 *
	 * @param   JLinkedinOAuth  $oauth  The JLinkedinOAuth object.
	 * @param   integer         $id     The unique identifier for a job.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function deleteBookmark($oauth, $id)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the success response code.
		$oauth->setOption('success_code', 204);

		// Set the API base
		$base = '/v1/people/~/job-bookmarks/' . $id;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'DELETE', $parameters);
		return $response;
	}
}
