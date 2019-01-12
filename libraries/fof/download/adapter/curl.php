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
 * A download adapter using the cURL PHP module
 */
class FOFDownloadAdapterCurl extends FOFDownloadAdapterAbstract implements FOFDownloadInterface
{
	protected $headers = array();

	public function __construct()
	{
		$this->priority = 110;
		$this->supportsFileSize = true;
		$this->supportsChunkDownload = true;
		$this->name = 'curl';
		$this->isSupported = function_exists('curl_init') && function_exists('curl_exec') && function_exists('curl_close');
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
		$ch = curl_init();

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

		// Default cURL options
		$options = array(
			CURLOPT_AUTOREFERER     => 1,
			CURLOPT_SSL_VERIFYPEER  => 1,
			CURLOPT_SSL_VERIFYHOST  => 2,
			CURLOPT_SSLVERSION      => 0,
			CURLOPT_AUTOREFERER     => 1,
			CURLOPT_URL             => $url,
			CURLOPT_BINARYTRANSFER  => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FOLLOWLOCATION  => 1,
			CURLOPT_CAINFO          => __DIR__ . '/cacert.pem',
			CURLOPT_HEADERFUNCTION  => array($this, 'reponseHeaderCallback')
		);

		if (!(empty($from) && empty($to)))
		{
			$options[CURLOPT_RANGE] = "$from-$to";
		}

		// Add any additional options: Since they are numeric, we must use the array operator. If the jey exists in both
		// arrays, only the first one will be used while the second one will be ignored
		$options = $params + $options;

		@curl_setopt_array($ch, $options);

		$this->headers = array();

		$result = curl_exec($ch);

		$errno       = curl_errno($ch);
		$errmsg      = curl_error($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($result === false)
		{
			$error = JText::sprintf('LIB_FOF_DOWNLOAD_ERR_CURL_ERROR', $errno, $errmsg);
		}
		elseif (($http_status >= 300) && ($http_status <= 399) && isset($this->headers['Location']) && !empty($this->headers['Location']))
		{
			return $this->downloadAndReturn($this->headers['Location'], $from, $to, $params);
		}
		elseif ($http_status > 399)
		{
			$result = false;
			$errno = $http_status;
			$error = JText::sprintf('LIB_FOF_DOWNLOAD_ERR_HTTPERROR', $http_status);
		}

		curl_close($ch);

		if ($result === false)
		{
			throw new Exception($error, $errno);
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Get the size of a remote file in bytes
	 *
	 * @param   string  $url  The remote file's URL
	 *
	 * @return  integer  The file size, or -1 if the remote server doesn't support this feature
	 */
	public function getFileSize($url)
	{
		$result = -1;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_NOBODY, true );
		curl_setopt($ch, CURLOPT_HEADER, true );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		@curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/cacert.pem');

		$data = curl_exec($ch);
		curl_close($ch);

		if ($data)
		{
			$content_length = "unknown";
			$status = "unknown";
			$redirection = null;

			if (preg_match( "/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches))
			{
				$status = (int)$matches[1];
			}

			if (preg_match( "/Content-Length: (\d+)/", $data, $matches))
			{
				$content_length = (int)$matches[1];
			}

			if (preg_match( "/Location: (.*)/", $data, $matches))
			{
				$redirection = (int)$matches[1];
			}

			if ($status == 200)
			{
				$result = $content_length;
			}

			if (($status > 300) && ($status <= 308))
			{
				if (!empty($redirection))
				{
					return $this->getFileSize($redirection);
				}

				return -1;
			}
		}

		return $result;
	}

	/**
	 * Handles the HTTP headers returned by cURL
	 *
	 * @param   resource  $ch    cURL resource handle (unused)
	 * @param   string    $data  Each header line, as returned by the server
	 *
	 * @return  int  The length of the $data string
	 */
	protected function reponseHeaderCallback(&$ch, &$data)
	{
		$strlen = strlen($data);

		if (($strlen) <= 2)
		{
			return $strlen;
		}

		if (substr($data, 0, 4) == 'HTTP')
		{
			return $strlen;
		}

		list($header, $value) = explode(': ', trim($data), 2);

		$this->headers[$header] = $value;

		return $strlen;
	}
}