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
	 * @return string  The response body
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

		if ($response->code == 204)
		{
			// The headers contain X-Account-Object-Count and X-Account-Bytes-Used
			return $response->headers;
		}

		return null;
	}

	/**
	 * Creates a request to view a list of the containers in your account
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function listContainers()
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "?format=json";

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->get($url, $headers);

		if ($response->code == 200)
		{
			// The headers contain X-Account-Object-Count and X-Account-Bytes-Used
			return $this->processResponse($response);
		}

		return null;
	}
}
