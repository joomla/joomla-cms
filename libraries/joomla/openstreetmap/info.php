<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Openstreetmap API Info class for the Joomla Platform
 *
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 * @since       13.1
*/
class JOpenstreetmapInfo extends JOpenstreetmapObject
{
	/**
	 * Method to get capabilities of the API
	 *
	 * @return	array  The XML response
	 *
	 * @since	13.1
	 */
	public function getCapabilities()
	{
		// Set the API base
		$base = 'capabilities';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', array());

		$xml_string = simplexml_load_string($response->body);

		return $xml_string;
	}

	/**
	 * Method to retrieve map data of a bounding box
	 *
	 * @param   float  $left    Left boundary
	 * @param   float  $bottom  Bottom boundary
	 * @param   float  $right   Right boundary
	 * @param   float  $top     Top boundary
	 *
	 * @return  array  The XML response
	 *
	 * @since   13.1
	 */
	public function retrieveMapData($left, $bottom, $right, $top)
	{
		// Set the API base
		$base = 'map?bbox=' . $left . ',' . $bottom . ',' . $right . ',' . $top;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', array());

		$xml_string = simplexml_load_string($response->body);

		return $xml_string;
	}

	/**
	 * Method to retrieve permissions for current user
	 *
	 * @return  array  The XML response
	 *
	 * @since   13.1
	 */
	public function retrievePermissions()
	{
		// Set the API base
		$base = 'permissions';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', array());

		$xml_string = simplexml_load_string($response->body);

		return $xml_string;
	}
}
