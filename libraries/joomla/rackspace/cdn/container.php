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
 * Defines the operations on CDN container services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceCdnContainer extends JRackspaceStorage
{
	/**
	 * Before a container can be CDN-enabled, it must exist in the storage system.
	 *
	 * @param   string  $container  The container name
	 * @param   int     $ttl        Time to live (in seconds)
	 * @param   string  $enable     "True" for enable or "False" for disable
	 *
	 * @return string  A message regarding the success of the operation
	 *
	 * @since   ??.?
	 */
	public function cdnEnableOrDisableContainer($container, $ttl, $enable = "True")
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-CDN-Management-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("cdn.host"),
			"X-TTL" => $ttl,
			"X-CDN-Enabled" => $enable,
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		if ($response->code == 201)
		{
			return "The container was CDN-enabled as requested.\n";
		}
		elseif ($response->code == 202)
		{
			return "The container was already CDN-enabled.\n";
		}

		return "The response code was " . $response->code;
	}

	/**
	 * HEAD operations against a CDN-enabled container are used to determine
	 * the CDN attributes of the container.
	 *
	 * @param   string  $container  The container name
	 *
	 * @return string  A message regarding the success of the operation
	 *
	 * @since   ??.?
	 */
	public function listCdnEnabledContainerMetadata($container)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-CDN-Management-Url"] . "/" . $container;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("cdn.host"),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->head($url, $headers);

		if ($response->code == 404)
		{
			return "The \"" . $container . "\" container does not exist.\n";
		}

		return $response->headers;
	}
}
