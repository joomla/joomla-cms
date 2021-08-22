<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Openstreetmap API User class for the Joomla Platform
 *
 * @since       3.2.0
 * @deprecated  4.0  Use the `joomla/openstreetmap` package via Composer instead
 */
class JOpenstreetmapUser extends JOpenstreetmapObject
{
	/**
	 * Method to get user details
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function getDetails()
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = 'user/details';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters);

		return $response->body;
	}

	/**
	 * Method to get preferences
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function getPreferences()
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = 'user/preferences';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', $parameters);

		return $response->body;
	}

	/**
	 * Method to replace user preferences
	 *
	 * @param   array  $preferences  Array of new preferences
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function replacePreferences($preferences)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = 'user/preferences';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Create a list of preferences
		$preference_list = '';

		if (!empty($preferences))
		{
			foreach ($preferences as $key => $value)
			{
				$preference_list .= '<preference k="' . $key . '" v="' . $value . '"/>';
			}
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<osm version="0.6" generator="JOpenstreetmap">
				<preferences>'
				. $preference_list .
				'</preferences>
			</osm>';

		$header['Content-Type'] = 'text/xml';

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $xml, $header);

		return $response->body;
	}

	/**
	 * Method to change user preferences
	 *
	 * @param   string  $key         Key of the preference
	 * @param   string  $preference  New value for preference
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function changePreference($key, $preference)
	{
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
		);

		// Set the API base
		$base = 'user/preferences/' . $key;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'PUT', $parameters, $preference);

		return $response->body;
	}
}
