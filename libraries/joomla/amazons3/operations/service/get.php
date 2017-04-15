<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the GET operation on the service
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsServiceGet extends JAmazons3OperationsService
{
	/**
	 * Creates the get request and returns the response from Amazon
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getService()
	{
		$url = "https://" . $this->options->get("api.url") . "/";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}
}
