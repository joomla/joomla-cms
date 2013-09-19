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

		if ($response->code == 404)
		{
			throw new DomainException(
				"The \"" . $container . "\" container was not found.\n",
				$response->code
			);
		}

		return $this->displayResponseCodeAndHeaders($response);
	}

	/**
	 * PUT operations against a storage container are used to create that container.
	 *
	 * @param   string  $container  The container name
	 *
	 * @return string  A message corresponding to the response code
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

		return $this->displayResponseCodeAndHeaders($response);
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

		if ($response->code == 404)
		{
			throw new DomainException(
				"The \"" . $container . "\" container was not found.\n",
				$response->code
			);
		}
		elseif ($response->code == 409)
		{
			throw new DomainException(
				"The \"" . $container . "\" container is not empty.\n",
				$response->code
			);
		}

		return $this->displayResponseCodeAndHeaders($response);
	}

	/**
	 * You may set any custom or arbitrary metadata headers as you find useful.
	 * They must, however, take the format X-Container-Meta-XXXX, where XXXX
	 * is the name of your custom header.
	 * For example, this can be used to set the container quotas or access log:
	 * - X-Container-Meta-Quota-Bytes
	 * - X-Container-Meta-Quota-Count
	 * - X-Container-Meta-Access-Log-Delivery
	 * You can also set the CORS container headers:
	 * - X-Container-Meta-Access-Control-Allow-Origin
	 * - X-Container-Meta-Access-Control-Max-Age
	 * - X-Container-Meta-Access-Control-Allow-Headers
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
			$headers[$key] = $value;
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 404)
		{
			throw new DomainException(
				"The \"" . $container . "\" container was not found.\n",
				$response->code
			);
		}

		return $this->displayResponseCodeAndHeaders($response);
	}

	/**
	 * Remove the specified container metadata.
	 * Works with X-Remove-Container-Meta-XXXX requests, where XXXX
	 * is the name of your custom header.
	 *
	 * @param   string  $container  The container name
	 * @param   array   $metadata   An array of metadata items to be removed
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function removeContainerMetadata($container, $metadata)
	{
		foreach ($metadata as $key)
		{
			$removeMetadata[$key] = "foo";
		}

		return $this->setOrEditContainerMetadata($container, $removeMetadata);
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

		if ($response->code == 404)
		{
			throw new DomainException(
				"The \"" . $container . "\" container was not found.\n",
				$response->code
			);
		}

		return $this->displayResponseCodeAndHeaders($response);
	}
}
