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
 * Linkedin API People class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinPeople extends JLinkedinObject
{
	/**
	 * Method to get the profile of the current user
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getProfile($oauth, $id = null, $url = null, $fields = null, $type = 'standard', $language = null)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/';

		$data['format'] = 'json';

		// Check if a member id is specified.
		if ($id)
		{
			$base .= 'id=' . $id;
		}
		else
		{
			if (!$url)
			{
				$base .= '~';
			}
		}

		// Check if profile url is specified.
		if ($url)
		{
			$base .= 'url=' . rawurlencode($url);

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
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data, $header);
		return json_decode($response->body);
	}

	public function getConnections($oauth)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/people/~/connections';

		$data['format'] = 'json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
