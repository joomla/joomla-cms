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
	 * @return string  The response headers or a message corresponding to the response code
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
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
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
			return "The \"" . $container . "\" container was successfully created.\n";
		}
		elseif ($response->code == 202)
		{
			return "A container with the name \"" . $container . "\" already exists.\n";
		}

		return $response->body;
	}

	/**
	 * DELETE operations against a storage container permanently remove it.
	 * The container must be empty before it can be deleted.
	 *
	 * @param   string  $container  The container name
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function deleteContainer($container)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->delete($url, $headers);

		if ($response->code == 204)
		{
			return "The \"" . $container . "\" container was successfully deleted.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
		}
		elseif ($response->code == 409)
		{
			return "The \"" . $container . "\" container is not empty.\n";
		}

		return null;
	}

	/**
	 * You may set any custom or arbitrary metadata headers as you find useful.
	 * They must, however, take the format X-Container-Meta-XXXX, where XXXX is the name of your custom header.
	 *
	 * @param   string  $container  The container name
	 * @param   array   $metadata   An array of metadata items to be set
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function setOrEditContainerMetadata($container, $metadata)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		foreach ($metadata as $key => $value)
		{
			$headers["X-Container-Meta-" . $key] = $value;
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 204)
		{
			return "The \"" . $container . "\" container metadata were successfully set.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
		}

		return null;
	}

	/**
	 * Remove the specified container metadata.
	 *
	 * @param   string  $container  The container name
	 * @param   array   $metadata   An array of metadata items to be set
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function removeContainerMetadata($container, $metadata)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		foreach ($metadata as $key)
		{
			$headers["X-Remove-Container-Meta-" . $key] = "foo";
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 204)
		{
			return "The \"" . $container . "\" container metadata were successfully removed.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
		}

		return null;
	}

	/**
	 * CORS (Cross Origin Resource Sharing) container headers allow users to
	 * upload files from one website--or origin--to your Cloud Files account.
	 * The three CORS headers set for containers are:
	 * - X-Container-Meta-Access-Control-Allow-Origin
	 * - X-Container-Meta-Access-Control-Max-Age
	 * - X-Container-Meta-Access-Control-Allow-Headers
	 *
	 * @param   string  $container  The container name
	 * @param   array   $options    An array of metadata items to be set
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function corsContainerHeaders($container, $options)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		foreach ($options as $key => $value)
		{
			$headers[$key] = $value;
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 204)
		{
			return "The \"" . $container . "\" container headers were successfully set.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
		}

		return null;
	}

	/**
	 * Lists the objects in a container. The information returned is size
	 * (number of bytes), hash, object name, date & time modified (in GMT),
	 * and content type.
	 *
	 * @param   string  $container   The container name
	 * @param   array   $parameters  An array of metadata items to be set
	 *
	 * @return string  An array with the objects in the container or a message
	 *				   corresponding to the response code.
	 *
	 * @since   ??.?
	 */
	public function listContainerObjects($container, $parameters = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "?format=json";

		foreach ($parameters as $key => $value)
		{
			$url .= "&" . $key . "=" . $value;
		}

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->get($url, $headers);

		if ($response->code == 200)
		{
			return $this->processResponse($response);
		}
		elseif ($response->code == 204)
		{
			return "There are no objects in the \"" . $container . "\" container.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $container . "\" container was not found.\n";
		}

		return null;
	}
}
