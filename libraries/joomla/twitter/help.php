<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Help class for the Joomla Platform.
 *
 * @since  12.3
 */
class JTwitterHelp extends JTwitterObject
{
	/**
	 * Method to get the supported languages from the API.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getLanguages()
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('help', 'languages');

		// Set the API path
		$path = '/help/languages.json';

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the current configuration used by Twitter including twitter.com slugs which are not usernames,
	 * maximum photo resolutions, and t.co URL lengths.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getConfiguration()
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('help', 'configuration');

		// Set the API path
		$path = '/help/configuration.json';

		// Send the request.
		return $this->sendRequest($path);
	}
}
