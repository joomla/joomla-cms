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
		foreach ($options as $key => $value)
		{
			$headers[$key] = $value;
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
	 * @return string  The response body
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
		foreach ($options as $key => $value)
		{
			$headers[$key] = $value;
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
}
