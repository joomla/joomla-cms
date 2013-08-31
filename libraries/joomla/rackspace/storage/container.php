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
 * Defines the operations on storage container services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceStorageContainer extends JRackspaceStorage
{
	/**
	 * See how many objects are in a container (X-Container-Object-Count)and
	 * the custom metadata you have set on the container (X-Container-Meta-TraitX).
	 *
	 * @param   string  $container  The container name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function viewContainerDetails($container)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Accept-Encoding" => "gzip",
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->head($url, $headers);

		if ($response->code == 204)
		{
			// The headers contain X-Container-Object-Count and X-Container-Meta-TraitX
			return $response->headers;
		}

		return null;
	}

	/**
	 * PUT operations against a storage container are used to create that container.
	 *
	 * @param   string  $container  The container name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function createContainer($container)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		if ($response->code == 201)
		{
			return "The container was successfully created.\n";
		}
		elseif ($response->code == 202)
		{
			return "A container with the same name already exists.\n";
		}

		return $response->body;
	}
}
