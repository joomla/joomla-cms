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
 * Defines the operations on storage object services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceStorageObject extends JRackspaceStorage
{
	/**
	 * GET operations against an object are used to retrieve the object's data.
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 * @param   array   $options    Additional headers
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function retrieveObject($container, $object, $options = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container
			. "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Set additional headers
		if ($options != null)
		{
			foreach ($options as $key => $value)
			{
				$headers[$key] = $value;
			}
		}

		// Send the http request
		$response = $this->client->get($url, $headers);

		return $response->body;
	}

	/**
	 * PUT operations are used to write, or overwrite, an object's content and metadata.
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 * @param   array   $etag       The MD5 checksum  of the object's data
	 * @param   array   $options    Additional headers
	 *
	 * @return string  A message regarding the success or failure of the request
	 *
	 * @since   ??.?
	 */
	public function createOrUpdateObject($container, $object, $etag = null, $options = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Set the ETag
		if ($etag != null)
		{
			$headers["ETag"] = $etag;
		}

		// Set additional headers
		if ($options != null)
		{
			foreach ($options as $key => $value)
			{
				$headers[$key] = $value;
			}
		}

		// Set the content
		$data = file_get_contents($object);

		// Send the http request
		$response = $this->client->put($url, $data, $headers);

		if ($response->code == 201)
		{
			return "The \"" . $object . "\" object was successfully created.\n";
		}
		else
		{
			return "The \"" . $object . "\" object was not successfully created.\n"
				. "Response code: " . $response->code . ".";
		}
	}

	/**
	 * Server-side copy feature.
	 *
	 * @param   string  $container          The container name
	 * @param   string  $sourceObject       The source object name
	 * @param   string  $destinationObject  The destination object name
	 * @param   array   $options            Additional headers
	 *
	 * @return string  A message regarding the success or failure of the request
	 *
	 * @since   ??.?
	 */
	public function copyObject($container, $sourceObject, $destinationObject, $options = null)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $destinationObject;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
			"X-Copy-From" => "/" . $container . "/" . $sourceObject ,
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Set additional headers
		if ($options != null)
		{
			foreach ($options as $key => $value)
			{
				$headers[$key] = $value;
			}
		}

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		if ($response->code == 201)
		{
			return "The \"" . $sourceObject . "\" object was successfully copied.\n";
		}
		else
		{
			return "The \"" . $sourceObject . "\" object was not successfully copied.\n"
				. "Response code: " . $response->code . ".";
		}
	}

	/**
	 * DELETE operations on an object are used to permanently remove an object
	 * from the storage system (data and metadata).
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 *
	 * @return string  A message regarding the success or failure of the request
	 *
	 * @since   ??.?
	 */
	public function deleteObject($container, $object)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->delete($url, $headers);

		if ($response->code == 204)
		{
			return "The \"" . $object . "\" object was successfully deleted.\n";
		}
		else
		{
			return "The \"" . $object . "\" object was not successfully deleted.\n"
				. "Response code: " . $response->code . ".";
		}
	}

	/**
	 * HEAD operations on an object are used to retrieve object metadata
	 * and other standard HTTP headers.
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 *
	 * @return string  The response headers
	 *
	 * @since   ??.?
	 */
	public function retrieveObjectMetadata($container, $object)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->head($url, $headers);

		if ($response->code / 100 == 2)
		{
			return $response->headers;
		}
		else
		{
			return "The \"" . $object . "\" object's metadata were not successfully retrieved.\n"
				. "Response code: " . $response->code . ".";
		}
	}

	/**
	 * You may set your own custom object metadata by using a POST request
	 * to the object name.
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 * @param   array   $metadata   An array of metadata items to be set
	 *
	 * @return string  A message regarding the success or failure of the request
	 *
	 * @since   ??.?
	 */
	public function updateObjectMetadata($container, $object, $metadata)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Set the metadata
		foreach ($metadata as $key => $value)
		{
			$headers["X-Object-Meta-" . $key] = $value;
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 202)
		{
			return "The \"" . $object . "\" object's metadata were successfully updated.\n";
		}
		else
		{
			return "The \"" . $object . "\" object's metadata were not successfully updated.\n"
				. "Response code: " . $response->code . ".";
		}
	}

	/**
	 * Remove the specified object metadata.
	 *
	 * @param   string  $container  The container name
	 * @param   string  $object     The object name
	 * @param   array   $metadata   An array of metadata items to be removed
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function removeObjectMetadata($container, $object, $metadata)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $container . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		foreach ($metadata as $key)
		{
			$headers["X-Remove-Object-Meta-" . $key] = "foo";
		}

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		if ($response->code == 202)
		{
			return "The \"" . $object . "\" object's metadata were successfully removed.\n";
		}
		elseif ($response->code == 404)
		{
			return "The \"" . $object . "\" object was not found.\n";
		}

		return null;
	}
}
