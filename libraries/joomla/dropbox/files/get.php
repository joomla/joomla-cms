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
 * Defines the GET operations on files
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropboxFilesGet extends JDropboxFiles
{
	/**
	 * Retrieves information about the user's file.
	 *
	 * @param   string  $root  The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path  The path to the file you want to retrieve.
	 * @param   string  $rev   The revision of the file to retrieve. This defaults to the most recent revision.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getFiles($root, $path, $rev = null)
	{
		$url = "https://" . $this->options->get("api.content") . "/1/files/" . $root . "/" . $path;

		if (isset($rev))
		{
			$url .= "?rev=" . $rev;
		}

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Retrieves file and folder metadata.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getMetadata($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/metadata/" . $root . "/" . $path;
		$paramsString = "";

		foreach ($params as $key => $param)
		{
			$paramsString .= "&" . $key . "=" . $param;
		}

		if (! empty($params))
		{
			$paramsString[0] = "?";
			$url .= $paramsString;
		}

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Obtains metadata for the previous revisions of a file.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getRevisions($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/revisions/" . $root . "/" . $path;
		$paramsString = "";

		foreach ($params as $key => $param)
		{
			$paramsString .= "&" . $key . "=" . $param;
		}

		if (! empty($params))
		{
			$paramsString[0] = "?";
			$url .= $paramsString;
		}

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
