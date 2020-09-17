<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Download\Adapter;

defined('_JEXEC') || die;

use FOF30\Download\DownloadInterface;
use FOF30\Download\Exception\DownloadError;
use Joomla\CMS\Language\Text;

/**
 * A download adapter using URL fopen() wrappers
 */
class Fopen extends AbstractAdapter implements DownloadInterface
{
	public function __construct()
	{
		$this->priority              = 100;
		$this->supportsFileSize      = false;
		$this->supportsChunkDownload = true;
		$this->name                  = 'fopen';

		// If we are not allowed to use ini_get, we assume that URL fopen is disabled
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
	 * @param   integer  $to      Byte range to stop downloading. Use null to download the entire file ($from is
	 *                            ignored)
	 * @param   array    $params  Additional params that will be added before performing the download
	 *
	 * @return  string  The raw file data retrieved from the remote URL.
	 *
	 * @throws  DownloadError  A generic exception is thrown on error
	 */
	public function downloadAndReturn($url, $from = null, $to = null, array $params = [])
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
			$options = [
				'http' => [
					'method' => 'GET',
					'header' => "Range: bytes=$from-$to\r\n",
				],
				'ssl'  => [
					'verify_peer'  => true,
					'cafile'       => JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem',
					'verify_depth' => 5,
				],
			];

			$options = array_merge($options, $params);

			$context = stream_context_create($options);
			$result  = @file_get_contents($url, false, $context, $from - $to + 1);
		}
		else
		{
			$options = [
				'http' => [
					'method' => 'GET',
				],
				'ssl'  => [
					'verify_peer'  => true,
					'cafile'       => JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem',
					'verify_depth' => 5,
				],
			];

			$options = array_merge($options, $params);

			$context = stream_context_create($options);
			$result  = @file_get_contents($url, false, $context);
		}

		global $http_response_header_test;

		if (!isset($http_response_header) && empty($http_response_header_test))
		{
			$error = Text::_('LIB_FOF_DOWNLOAD_ERR_FOPEN_ERROR');
			throw new DownloadError($error, 404);
		}
		else
		{
			// Used for testing
			if (!isset($http_response_header) && !empty($http_response_header_test))
			{
				$http_response_header = $http_response_header_test;
			}

			$http_code = 200;
			$nLines    = count($http_response_header);

			for ($i = $nLines - 1; $i >= 0; $i--)
			{
				$line = $http_response_header[$i];
				if (strncasecmp("HTTP", $line, 4) == 0)
				{
					$response  = explode(' ', $line);
					$http_code = $response[1];
					break;
				}
			}

			if ($http_code >= 299)
			{
				$error = Text::sprintf('LIB_FOF_DOWNLOAD_ERR_HTTPERROR', $http_code);
				throw new DownloadError($error, $http_code);
			}
		}

		if ($result === false)
		{
			$error = Text::sprintf('LIB_FOF_DOWNLOAD_ERR_FOPEN_ERROR');
			throw new DownloadError($error, 1);
		}
		else
		{
			return $result;
		}
	}
}
