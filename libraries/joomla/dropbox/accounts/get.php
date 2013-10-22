<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the GET operations on accounts
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropboxAccountsGet extends JDropboxAccounts
{
	/**
	 * Retrieves information about the user's account.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getInfo()
	{
		$url = "https://" . $this->options->get("api.url") . "/1/account/info";

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
