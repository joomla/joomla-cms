<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Openstreetmap API Info class for the Joomla Platform
 *
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @since       13.1
*/
class JOpenstreetmapInfo extends JOpenstreetmapObject
{
	/**
	 * Method to get capabilities of the API
	 * 
	 * @return	array The xml response
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
	 * @param   float  $left    left boundary
	 * @param   float  $bottom  bottom boundary
	 * @param   float  $right   right boundary
	 * @param   float  $top     top boundary
	 * 
	 * @return  array The xml response
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
	 * @return  array The xml response
	 * 
	 * @since   13.1
	 */
	public function retrievePermissions()
	{
		if ($this->oauth != null)
		{
			$token = $this->oauth->getToken();
		}

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
