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
	 * Method to get a member's profile.
	 *
	 * @param   JLinkedinOAuth  $oauth     The JLinkedinOAuth object.
	 * @param   string          $id        Member id of the profile you want.
	 * @param   string          $url       The public profile URL.
	 * @param   string          $fields    Request fields beyond the default ones.
	 * @param   string          $type      Choosing public or standard profile.
	 * @param   string          $language  A comma separated list of locales ordered from highest to lowest preference.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
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

	/**
	 * Method to get a list of connections for a user who has granted access to his/her account.
	 *
	 * @param   JLinkedinOAuth  $oauth           The JLinkedinOAuth object.
	 * @param   string          $id              Member id of the profile you want.
	 * @param   string          $url             The public profile URL.
	 * @param   string          $fields          Request fields beyond the default ones.
	 * @param   integer         $start           Starting location within the result set for paginated returns.
	 * @param   integer         $count           The number of results returned.
	 * @param   string          $modified        Values are updated or new.
	 * @param   string          $modified_since  Value as a Unix time stamp of milliseconds since epoch.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getConnections($oauth, $id = null, $url = null, $fields = null, $start = 0, $count = 500, $modified = null, $modified_since = null)
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
			$base .= 'id=' . $id . '/connections';
		}
		else
		{
			if (!$url)
			{
				$base .= '~' . '/connections';
			}
		}

		// Check if profile url is specified.
		if ($url)
		{
			$base .= 'url=' . rawurlencode($url) . '/connections';
		}

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
			$data['modified_since'] = $modified_since;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
