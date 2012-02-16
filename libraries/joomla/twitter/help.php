<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Help class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterHelp extends JTwitterObject
{
	/**
	 * Method to get the supported languages from the API.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getLanguages()
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/help/languages.json';

		// Build the request path.
		$path = $base;

		// Send the request.
		return $this->sendRequest($path, 200);
	}
}
