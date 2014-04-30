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
 * Defines the operations on storage account services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceStorageAccount extends JRackspaceStorage
{
	/**
	 * Creates a request to view the account information
	 *
	 * @return string  The response headers
	 *
	 * @since   ??.?
	 */
	public function viewAccountDetails()
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"];

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->head($url, $headers);

		return $this->displayResponseCodeAndHeaders($response);
	}

	/**
	 * Creates a request to view a list of the containers in your account
	 *
	 * @param   array  $queryParameters  An array with query parameters:
	 *                                   limit, marker or end_marker.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function listContainers($queryParameters = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "?format=json";

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
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->get($url, $headers);

		return $this->displayResponseCodeAndHeaders($response);
	}
}
