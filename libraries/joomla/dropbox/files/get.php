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

	/**
	 * Returns metadata for all files and folders whose filename contains the given search string as a substring.
	 * Searches are limited to the folder path and its sub-folder hierarchy provided in the call.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getSearch($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/search/" . $root . "/" . $path;
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
	 * Creates and returns a copy_ref to a file. This reference string can be used to copy that file
	 * to another user's Dropbox by passing it in as the from_copy_ref parameter on fileopsCopy.
	 *
	 * @param   string  $root  The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path  The path to the file you want to retrieve.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getCopyRef($root, $path)
	{
		$url = "https://" . $this->options->get("api.url") . "/1/copy_ref/" . $root . "/" . $path;

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Gets a thumbnail for an image.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getThumbnails($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.content") . "/1/thumbnails/" . $root . "/" . $path;
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
