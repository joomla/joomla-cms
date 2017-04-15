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
 * Defines the PUT operations on files
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropboxFilesPut extends JDropboxFiles
{
	/**
	 * Uploads a file using PUT semantics.
	 *
	 * @param   string  $root    The root relative to which path is specified.
	 *                           Valid values are sandbox and dropbox.
	 * @param   string  $path    The full path to the file you want to write to.
	 *                           This parameter should not point to a folder.
	 * @param   string  $data    The file contents to be uploaded.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function putFile($root, $path, $data, $params = array())
	{
		$url = "https://" . $this->options->get("api.content") . "/1/files_put/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Create the request, send it and process the response
		return $this->commonPutOperations($url, $data);
	}

	/**
	 * Uploads large files to Dropbox in multiple chunks
	 * Also has the ability to resume if the upload is interrupted.
	 * This allows for uploads larger than the /files and /files_put maximum of 150 MB.
	 *
	 * Typical usage:
	 * 1. Send a PUT request to /chunked_upload with the first chunk of the file without
	 *  setting upload_id, and receive an upload_id in return.
	 * 2. Repeatedly PUT subsequent chunks using the upload_id to identify the upload
	 *  in progress and an offset representing the number of bytes transferred so far.
	 * 3. After each chunk has been uploaded, the server returns a new offset representing
	 *  the total amount transferred.
	 * 4. After the last chunk, use postCommitChunkedUpload to complete the upload.
	 *
	 * @param   string  $body    A chunk of data from the file being uploaded. If resuming,
	 *                           the chunk should begin at the number of bytes into the file
	 *                           that equals the offset.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function putChunkedUpload($body, $params = array())
	{
		$url = "https://" . $this->options->get("api.content") . "/1/chunked_upload";
		$url .= $this->createParamsString($params);

		// Create the request, send it and process the response
		return $this->commonPutOperations($url, $body);
	}
}
