<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the operations on CDN account services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceCdnAccount extends JRackspaceCdn
{
	/**
	 * GET operations against the X-CDN-Management-Url for an account are
	 * performed to retrieve a list of existing CDN-enabled containers.
	 *
	 * @param   array  $queryParameters  An array with query parameters:
	 *                                   limit, marker or end_marker.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function listCdnEnabledContainers($queryParameters = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-CDN-Management-Url"] . "?format=json";

		// Add the query parameters
		if ($queryParameters != null)
		{
			foreach ($queryParameters as $parameter => $value)
			{
				$url .= "&" . $parameter . "=" . $value;
			}
		}

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("cdn.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->get($url, $headers);

		if ($response->code == 200)
		{
			return $this->processResponse($response);
		}

		return $this->displayResponseCodeAndHeaders($response);
	}
}
