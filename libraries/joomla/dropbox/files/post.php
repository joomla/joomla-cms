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
 * Defines the POST operations on files
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropboxFilesPost extends JDropboxFiles
{
	/**
	 * Uploads a file using POST semantics
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   string  $data    The file contents to be uploaded.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postFiles($root, $path, $data, $params = array())
	{
		$url = "https://" . $this->options->get("api.content") . "/1/files/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, $data, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * A way of letting you keep up with changes to files and folders in a user's Dropbox.
	 * You can periodically call postDelta to get a list of "delta entries", which are
	 * instructions on how to update your local state to match the server's state.
	 *
	 * @param   array  $params  The parameters to be used in the request.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postDelta($params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/delta";
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Restores a file path to a previous revision.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request. "rev" (revision) is a required parameter.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postRestore($root, $path, $params)
	{
		$url = "https://" . $this->options->get("api.url") . "/1/restore/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates and returns a Dropbox link to files or folders users can use to view a preview of the file in a web browser.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request. "rev" (revision) is a required parameter.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postShares($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/shares/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Returns a link directly to a file.
	 * Similar to postShares. The difference is that this bypasses the Dropbox webserver, used to
	 * provide a preview of the file, so that you can effectively stream the contents of your media.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request. "rev" (revision) is a required parameter.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postMedia($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/media/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Completes an upload initiated by the putChunkedUpload method.
	 * Saves a file uploaded via putChunkedUpload to a user's Dropbox.
	 *
	 * @param   string  $root    The root relative to which path is specified. Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request. "rev" (revision) is a required parameter.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function postCommitChunkedUpload($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.content") . "/1/commit_chunked_upload/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
