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
 * Openstreetmap API Gps class for the Joomla Platform
 *
 * @package     Joomla.Platform
 * @subpackage  Openstreetmap
 *
 * @since       13.1
 */
class JOpenstreetmapGps extends JOpenstreetmapObject
{
	/**
	 * Method to retrieve GPS points
	 * 
	 * @param   float  $left    left boundary
	 * @param   float  $bottom  bottom boundary
	 * @param   float  $right   right boundary
	 * @param   float  $top     top boundary
	 * @param   int    $page    page number
	 * 
	 * @return	array	The xml response containing GPS points
	 * 
	 * @since	13.1
	 */
	public function retrieveGps($left,$bottom,$right,$top,$page=0)
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
	 * @param   string  $file         file name that contains trace points
	 * @param   string  $description  description on trace points
	 * @param   string  $tags         tags for trace
	 * @param   int     $public       1 for public, 0 for private
	 * @param   string  $visibility   One of the following: private, public, trackable, identifiable
	 * @param   string  $username     username
	 * @param   string  $password     password
	 * 
	 * @return  JHttpResponse the response
	 * 
	 * @since   13.1
	 */
	public function uploadTrace($file, $description, $tags, $public, $visibility, $username, $password)
	{
		// Set parameters.
		$parameters = array(
				'file' => $file,
				'description' => $description,
				'tags' => $tags,
				'public' => $public,
				'visibility' => $visibility
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
	 * @param   int     $id        trace identifier
	 * @param   string  $username  username
	 * @param   string  $password  password
	 * 
	 * @return  array  The xml response
	 * 
	 * @since   13.1
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
	 * @param   int     $id        trace identifier
	 * @param   string  $username  username
	 * @param   string  $password  password
	 * 
	 * @return  array  The xml response
	 * 
	 * @since   13.1
	 */
	public function downloadTraceMetadata($id, $username, $password)
	{
		// Set the API base
		$base = 'gpx/' . $id . '/data';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		$client = JHttpFactory::getHttp();

		// Send the request.
		$xml_string = $this->sendRequest($path, 'GET', array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));

		return $xml_string;
	}
}
