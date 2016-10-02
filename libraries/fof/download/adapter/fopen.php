<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  dispatcher
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * A download adapter using URL fopen() wrappers
 */
class FOFDownloadAdapterFopen extends FOFDownloadAdapterAbstract implements FOFDownloadInterface
{
	public function __construct()
	{
		$this->priority = 100;
		$this->supportsFileSize = false;
		$this->supportsChunkDownload = true;
		$this->name = 'fopen';

		// If we are not allowed to use ini_get, we assume that URL fopen is
		// disabled.
		if (!function_exists('ini_get'))
		{
			$this->isSupported = false;
		}
		else
		{
			$this->isSupported = ini_get('allow_url_fopen');
		}
	}

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
	public function downloadAndReturn($url, $from = null, $to = null, array $params = array())
	{
		if (empty($from))
		{
			$from = 0;
		}

		if (empty($to))
		{
			$to = 0;
		}

		if ($to < $from)
		{
			$temp = $to;
			$to   = $from;
			$from = $temp;

			unset($temp);
		}

		if (!(empty($from) && empty($to)))
		{
			$options = array(
				'http'	=> array(
					'method'	=> 'GET',
					'header'	=> "Range: bytes=$from-$to\r\n"
				),
				'ssl' => array(
					'verify_peer'   => true,
					'cafile'        => __DIR__ . '/cacert.pem',
					'verify_depth'  => 5,
				)
			);

			$options = array_merge($options, $params);

			$context = stream_context_create($options);
			$result  = @file_get_contents($url, false, $context, $from - $to + 1);
		}
		else
		{
			$options = array(
				'http'	=> array(
					'method'	=> 'GET',
				),
				'ssl' => array(
					'verify_peer'   => true,
					'cafile'        => __DIR__ . '/cacert.pem',
					'verify_depth'  => 5,
				)
			);

			$options = array_merge($options, $params);

			$context = stream_context_create($options);
			$result  = @file_get_contents($url, false, $context);
		}

		if ($result === false)
		{
			$error = JText::sprintf('LIB_FOF_DOWNLOAD_ERR_HTTPERROR');
			throw new Exception($error, 1);
		}
		else
		{
			return $result;
		}
	}
}