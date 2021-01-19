<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Postproc\Exception\DeleteNotSupported;
use Akeeba\Engine\Postproc\Exception\DownloadToBrowserNotSupported;
use Akeeba\Engine\Postproc\Exception\DownloadToServerNotSupported;
use Akeeba\Engine\Postproc\Exception\OAuthNotSupported;
use Akeeba\Engine\Postproc\Exception\RangeDownloadNotSupported;
use Exception;

interface PostProcInterface
{
	/**
	 * This function takes care of post-processing a file (typically a backup archive part file).
	 *
	 * If the process has ran to completion it returns true.
	 *
	 * If more work is required (the file has only been partially uploaded) it returns false.
	 *
	 * It the process has failed an Exception is thrown.
	 *
	 * @param   string       $localFilepath   Absolute path to the part we'll have to process
	 * @param   string|null  $remoteBaseName  Base name of the uploaded file, skip to use $absolute_filename's
	 *
	 * @return  bool  True on success, false if more work is required
	 *
	 * @throws  Exception  When an error occurred during post-processing
	 */
	public function processPart($localFilepath, $remoteBaseName = null);

	/**
	 * Deletes a remote file
	 *
	 * @param   string  $path  The absolute, remote storage path to the file we're deleting
	 *
	 * @return  void
	 *
	 * @throws  DeleteNotSupported  When this feature is not supported at all.
	 * @throws  Exception  When an engine error occurs
	 */
	public function delete($path);

	/**
	 * Downloads a remotely stored file back to the site's server. It can optionally do a range download. If range
	 * downloads are not supported we throw a RangeDownloadNotSupported exception. Any other type of Exception means
	 * that the download failed.
	 *
	 * @param   string    $remotePath  The path to the remote file
	 * @param   string    $localFile   The absolute path to the local file we're writing to
	 * @param   int|null  $fromOffset  The offset (in bytes) to start downloading from
	 * @param   int|null  $length      The amount of data (in bytes) to download
	 *
	 * @return  void
	 *
	 * @throws  DownloadToServerNotSupported  When this feature is not supported at all.
	 * @throws  RangeDownloadNotSupported  When range downloads are not supported.
	 * @throws  Exception  On failure.
	 */
	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null);

	/**
	 * Downloads a remotely stored file to the user's browser, without storing it on the site's web server first.
	 *
	 * If $this->inlineDownloadToBrowser is true the method outputs a byte stream to the browser and returns null.
	 *
	 * If $this->inlineDownloadToBrowser is false it returns a string containing a public download URL. The user's
	 * browser will be redirected to that URL.
	 *
	 * If this feature is not supported a DownloadToBrowserNotSupported exception will be thrown.
	 *
	 * Any other Exception indicates an error while trying to download to browser such as file not found, problem with
	 * the remote service etc.
	 *
	 * @param   string  $remotePath  The absolute, remote storage path to the file we want to download
	 *
	 * @return  string|null
	 *
	 * @throws  DownloadToBrowserNotSupported  When this feature is not supported at all.
	 * @throws  Exception  When an error occurs.
	 */
	public function downloadToBrowser($remotePath);

	/**
	 * A proxy which allows us to execute arbitrary methods in this engine. Used for AJAX calls, typically to update UI
	 * elements with information fetched from the remote storage service.
	 *
	 * For security reasons, only methods whitelisted in the $this->allowedCustomAPICallMethods array can be called.
	 *
	 * @param   string  $method  The method to call.
	 * @param   array   $params  Any parameters to send to the method, in array format
	 *
	 * @return  mixed  The return value of the method.
	 */
	public function customAPICall($method, $params = []);

	/**
	 * Opens an OAuth window (performs an HTTP redirection).
	 *
	 * @param   array  $params  Any parameters required to launch OAuth
	 *
	 * @return  void
	 *
	 * @throws  OAuthNotSupported  When not supported.
	 * @throws  Exception  When an error occurred.
	 */
	public function oauthOpen($params = []);

	/**
	 * Fetches the authentication token from the OAuth helper script, after you've run the first step of the OAuth
	 * authentication process. Must be overridden in subclasses.
	 *
	 * @param   array  $params
	 *
	 * @return  void
	 *
	 * @throws  OAuthNotSupported
	 */
	public function oauthCallback(array $params);

	/**
	 * Does the engine recommend doing a step break before post-processing backup archives with it?
	 *
	 * @return  bool
	 */
	public function recommendsBreakBefore();

	/**
	 * Does the engine recommend doing a step break after post-processing backup archives with it?
	 *
	 * @return  bool
	 */
	public function recommendsBreakAfter();

	/**
	 * Is it advisable to delete files successfully post-processed by this post-processing engine?
	 *
	 * Currently only the “None” method advises against deleting successfully post-processed files for the simple reason
	 * that it does absolutely nothing with the files. The only copy is still on the server.
	 *
	 * @return  bool
	 */
	public function isFileDeletionAfterProcessingAdvisable();

	/**
	 * Does this engine support deleting remotely stored files?
	 *
	 * Most engines support deletion. However, some engines such as “Send by email”, do not have a way to find files
	 * already processed and delete them. Or it may be that we are sending the file to a write-only storage service
	 * which does not support deletions.
	 *
	 * @return  bool
	 */
	public function supportsDelete();

	/**
	 * Does this engine support downloading backup archives back to the site's web server?
	 *
	 * @return  bool
	 */
	public function supportsDownloadToFile();

	/**
	 * Does this engine support downloading backup archives directly to the user's browser?
	 *
	 * @return  bool
	 */
	public function supportsDownloadToBrowser();

	/**
	 * Does this engine return a bytestream when asked to download backup archives directly to the user's browser?
	 *
	 * @return  bool
	 */
	public function doesInlineDownloadToBrowser();

	/**
	 * Returns the remote absolute path to the file which was just processed.
	 *
	 * @return  string
	 */
	public function getRemotePath();
}
