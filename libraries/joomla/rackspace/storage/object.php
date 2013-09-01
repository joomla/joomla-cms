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
	public function retrieveObject($container, $object, $options)
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
}
