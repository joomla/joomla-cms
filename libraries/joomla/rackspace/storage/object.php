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
	 * @return string  The response body or a message corresponding to the response code
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

		if ($response->code == 404)
		{
			throw new DomainException(
				"The \"" . $container . "/" . $object . "\" object was not found.\n",
				$response->code
			);
		}

		// Convert the respnse headers to a string
		$headersArrayAsString = str_replace(
			"\",\"", "\",\n\t\"",
			str_replace(
				array("{","}",":"),
				array("Array(\n\t","\n)","=>"),
				json_encode($response->headers)
			)
		);

		return "Response code: " . $response->code . ".\n"
			. "Response headers: " . $headersArrayAsString . "\n";
	}

	/**
	 * PUT operations are used to write or overwrite an object's content and metadata.
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

		return "The response code was " . $response->code . ".";
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

		return "The response code was " . $response->code . ".";
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
			return "The \"" . $container . "/" . $object . "\" object "
				. "was successfully deleted.\n";
		}

		return "The response code was " . $response->code . ".";
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

		return "The response code was " . $response->code . ".";
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
		foreach ($metadata as $key)
		{
			$removeMetadata[$key] = "foo";
		}

		return $this->updateObjectMetadata($container, $object, $removeMetadata);
	}

	/**
	 * Seeks for one of the tar, tar.gz, or tar.bz2 extensions and returns it.
	 *
	 * @param   string  $archive  The archive name
	 *
	 * @return string  The matching extension
	 *
	 * @since   ??.?
	 */
	public function getExtension($archive)
	{
		// Specify the valid extensions
		$validExtensions = array("tar", "tar.gz", "tar.bz2");

		// Look for them at the end of the archive
		foreach ($validExtensions as $ext)
		{
			if (substr_compare($archive, $ext, -strlen($ext), strlen($ext)) === 0)
			{
				return $ext;
			}
		}

		return null;
	}

	/**
	 * Bulk upload of files from archive.
	 * Accepted formats are tar, tar.gz, and tar.bz2.
	 *
	 * @param   string  $archive      The archive name
	 * @param   string  $upload_path  The upload path
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function extractArchive($archive, $upload_path)
	{
		$extension = $this->getExtension($archive);

		if ($extension == null)
		{
			return "The only accepted formats are tar, tar.gz and tar.bz2.";
		}

		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "/" . $upload_path
			. "?extract-archive=" . $extension;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
			"Accept" => "application/json",
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Set the data
		$data = file_get_contents($archive);

		// Send the http request
		$response = $this->client->put($url, $data, $headers);

		// The response code is always 200. You have to check the request body
		// to see if it was actually successful or not.
		return $this->processResponse($response);
	}

	/**
	 * This request will delete multiple objects or containers from their
	 * account with a single request.
	 *
	 * @param   array  $list  An array containing a newline separated list
	 *                        of URL encoded objects to delete
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function bulkDelete($list)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"] . "?bulk-delete=1";
		$data = "";

		// Create the list of URLs to be deleted
		foreach ($list as $item)
		{
			$data .= $item . "\n";
		}

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("storage.host"),
			"Content-Type" => "text/plain",
			"Content-Length" => strlen($data),
			"Accept" => "application/json",
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->deleteWithBody($url, $data, $headers);

		// The response code is always 200. You have to check the request body
		// to see if it was actually successful or not.
		return $this->processResponse($response);
	}
}
