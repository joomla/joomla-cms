<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  dispatcher
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

interface FOFDownloadInterface
{
	/**
	 * Does this download adapter support downloading files in chunks?
	 *
	 * @return  boolean  True if chunk download is supported
	 */
	public function supportsChunkDownload();

	/**
	 * Does this download adapter support reading the size of a remote file?
	 *
	 * @return  boolean  True if remote file size determination is supported
	 */
	public function supportsFileSize();

	/**
	 * Is this download class supported in the current server environment?
	 *
	 * @return  boolean  True if this server environment supports this download class
	 */
	public function isSupported();

	/**
	 * Get the priority of this adapter. If multiple download adapters are
	 * supported on a site, the one with the highest priority will be
	 * used.
	 *
	 * @return  boolean
	 */
	public function getPriority();

	/**
	 * Returns the name of this download adapter in use
	 *
	 * @return  string
	 */
	public function getName();

	/**
	 * Download a part (or the whole) of a remote URL and return the downloaded
	 * data. You are supposed to check the size of the returned data. If it's
	 * smaller than what you expected you've reached end of file. If it's empty
	 * you have tried reading past EOF. If it's larger than what you expected
	 * the server doesn't support chunk downloads.
	 *
	 * If this class' supportsChunkDownload returns false you should assume
	 * that the $from and $to parameters will be ignored.
	 *
	 * @param   string   $url     The remote file's URL
	 * @param   integer  $from    Byte range to start downloading from. Use null for start of file.
	 * @param   integer  $to      Byte range to stop downloading. Use null to download the entire file ($from is ignored)
	 * @param   array    $params  Additional params that will be added before performing the download
	 *
	 * @return  string  The raw file data retrieved from the remote URL.
	 *
	 * @throws  Exception  A generic exception is thrown on error
	 */
	public function downloadAndReturn($url, $from = null, $to = null, array $params = array());

	/**
	 * Get the size of a remote file in bytes
	 *
	 * @param   string  $url  The remote file's URL
	 *
	 * @return  integer  The file size, or -1 if the remote server doesn't support this feature
	 */
	public function getFileSize($url);
}