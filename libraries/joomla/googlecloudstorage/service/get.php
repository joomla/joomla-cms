<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the GET operation on the service
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageServiceGet extends JGooglecloudstorageService
{
	/**
	 * Creates the get request and returns the response
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getService()
	{
		$url = "https://" . $this->options->get("api.url") . "/";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"Content-Length" => 0,
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.read-only")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}
}
