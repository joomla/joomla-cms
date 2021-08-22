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
 * Openstreetmap API GPS class for the Joomla Platform
 *
 * @since       3.2.0
 * @deprecated  4.0  Use the `joomla/openstreetmap` package via Composer instead
 */
class JOpenstreetmapGps extends JOpenstreetmapObject
{
	/**
	 * Method to retrieve GPS points
	 *
	 * @param   float    $left    Left boundary
	 * @param   float    $bottom  Bottom boundary
	 * @param   float    $right   Right boundary
	 * @param   float    $top     Top boundary
	 * @param   integer  $page    Page number
	 *
	 * @return	array  The XML response containing GPS points
	 *
	 * @since	3.2.0
	 */
	public function retrieveGps($left, $bottom, $right, $top, $page = 0)
	{
		// Set the API base
		$base = 'trackpoints?bbox=' . $left . ',' . $bottom . ',' . $right . ',' . $top . '&page=' . $page;

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $this->oauth->oauthRequest($path, 'GET', array());

		$xml_string = simplexml_load_string($response->body);

		return $xml_string;
	}

	/**
	 * Method to upload GPS Traces
	 *
	 * @param   string   $file         File name that contains trace points
	 * @param   string   $description  Description on trace points
	 * @param   string   $tags         Tags for trace
	 * @param   integer  $public       1 for public, 0 for private
	 * @param   string   $visibility   One of the following: private, public, trackable, identifiable
	 * @param   string   $username     Username
	 * @param   string   $password     Password
	 *
	 * @return  JHttpResponse  The response
	 *
	 * @since   3.2.0
	 */
	public function uploadTrace($file, $description, $tags, $public, $visibility, $username, $password)
	{
		// Set parameters.
		$parameters = array(
			'file' => $file,
			'description' => $description,
			'tags' => $tags,
			'public' => $public,
			'visibility' => $visibility,
		);

		// Set the API base
		$base = 'gpx/create';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$header['Content-Type'] = 'multipart/form-data';

		$header = array_merge($header, $parameters);
		$header = array_merge($header, array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));

		// Send the request.
		$response = $this->sendRequest($path, 'POST', $header, array());

		return $response;
	}

	/**
	 * Method to download Trace details
	 *
	 * @param   integer  $id        Trace identifier
	 * @param   string   $username  Username
	 * @param   string   $password  Password
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function downloadTraceMetadetails($id, $username, $password)
	{
		// Set the API base
		$base = 'gpx/' . $id . '/details';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$xml_string = $this->sendRequest($path, 'GET', array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));

		return $xml_string;
	}

	/**
	 * Method to download Trace data
	 *
	 * @param   integer  $id        Trace identifier
	 * @param   string   $username  Username
	 * @param   string   $password  Password
	 *
	 * @return  array  The XML response
	 *
	 * @since   3.2.0
	 */
	public function downloadTraceMetadata($id, $username, $password)
	{
		// Set the API base
		$base = 'gpx/' . $id . '/data';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$xml_string = $this->sendRequest($path, 'GET', array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));

		return $xml_string;
	}
}
