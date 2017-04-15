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
	 * @param   string  $root              The root relative to which path is specified.
	 *                                     Valid values are sandbox and dropbox.
	 * @param   string  $path              The path to the file you want to retrieve.
	 * @param   string  $localFileHandler  A handler for the file you want to write to.
	 * @param   string  $rev               The revision of the file to retrieve.
	 *                                     This defaults to the most recent revision.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getFile($root, $path, $localFileHandler = null, $rev = null)
	{
		$url = "https://" . $this->options->get("api.content") . "/1/files/" . $root . "/" . $path;

		if (isset($rev))
		{
			$url .= "?rev=" . $rev;
		}

		// Process the response
		$fileBody = $this->commonGetOperations($url, true);

		return $this->commonWriteToFileOperations($fileBody, $localFileHandler);
	}

	/**
	 * Retrieves file and folder metadata.
	 *
	 * @param   string  $root    The root relative to which path is specified.
	 *                           Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getMetadata($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/metadata/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Create the request, send it and process the response
		return $this->commonGetOperations($url);
	}

	/**
	 * Obtains metadata for the previous revisions of a file.
	 *
	 * @param   string  $root    The root relative to which path is specified.
	 *                           Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the file you want to retrieve.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getRevisions($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/revisions/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Create the request, send it and process the response
		return $this->commonGetOperations($url);
	}

	/**
	 * Returns metadata for all files and folders whose filename contains the
	 * given search string as a substring.
	 * Searches are limited to the folder path and its sub-folder hierarchy
	 * provided in the call.
	 *
	 * @param   string  $root    The root relative to which path is specified.
	 *                           Valid values are sandbox and dropbox.
	 * @param   string  $path    The path to the folder you want to search from.
	 * @param   array   $params  The parameters to be used in the request.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getSearch($root, $path, $params = array())
	{
		$url = "https://" . $this->options->get("api.url") . "/1/search/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Create the request, send it and process the response
		return $this->commonGetOperations($url);
	}

	/**
	 * Creates and returns a copy_ref to a file. This reference string can be used to copy that file
	 * to another user's Dropbox by passing it in as the from_copy_ref parameter on fileopsCopy.
	 *
	 * @param   string  $root  The root relative to which path is specified.
	 *                         Valid values are sandbox and dropbox.
	 * @param   string  $path  The path to the file you want a copy_ref to refer to.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getCopyRef($root, $path)
	{
		$url = "https://" . $this->options->get("api.url") . "/1/copy_ref/" . $root . "/" . $path;

		// Create the request, send it and process the response
		return $this->commonGetOperations($url);
	}

	/**
	 * Gets a thumbnail for an image.
	 *
	 * @param   string  $root              The root relative to which path is specified.
	 *                                     Valid values are sandbox and dropbox.
	 * @param   string  $path              The path to the file you want to thumbnail.
	 * @param   array   $params            The parameters to be used in the request.
	 * @param   string  $localFileHandler  A handler for the file you want to write to.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 *
	 * @since   ??.?
	 */
	public function getThumbnail($root, $path, $params = array(), $localFileHandler = null)
	{
		$url = "https://" . $this->options->get("api.content") . "/1/thumbnails/" . $root . "/" . $path;
		$url .= $this->createParamsString($params);

		// Process the response
		$fileBody = $this->commonGetOperations($url, true);

		return $this->commonWriteToFileOperations($fileBody, $localFileHandler);
	}

	/**
	 * Common operations performed by the methods which write output to a file.
	 *
	 * @param   string  $fileBody          The content to be written.
	 * @param   string  $localFileHandler  A handler for the file you want to write to.
	 *
	 * @return string
	 *
	 * @since   ??.?
	 */
	protected function commonWriteToFileOperations($fileBody, $localFileHandler)
	{
		if (empty($localFileHandler))
		{
			return $fileBody;
		}

		// Write output to file
		if (fwrite($localFileHandler, $fileBody))
		{
			$message = "The file was successfully downloaded.";
		}
		else
		{
			$message = "The file could not be written.";
		}

		fclose($localFileHandler);

		return $message;
	}
}
