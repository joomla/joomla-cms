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
 * A download adapter using the cURL PHP integration
 */
class Curl extends AbstractAdapter implements DownloadInterface
{
	protected $headers = [];

	public function __construct()
	{
		$this->priority              = 110;
		$this->supportsFileSize      = true;
		$this->supportsChunkDownload = true;
		$this->name                  = 'curl';
		$this->isSupported           = function_exists('curl_init') && function_exists('curl_exec') && function_exists('curl_close');
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

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSLVERSION, 0);
		curl_setopt($ch, CURLOPT_CAINFO, JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem');
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'reponseHeaderCallback']);

		if (!(empty($from) && empty($to)))
		{
			curl_setopt($ch, CURLOPT_RANGE, "$from-$to");
		}

		if (!is_array($params))
		{
			$params = [];
		}

		$patched_accept_encoding = false;

		// Work around LiteSpeed sending compressed output under HTTP/2 when no encoding was requested
		// See https://github.com/joomla/joomla-cms/issues/21423#issuecomment-410941000
		if (defined('CURLOPT_ACCEPT_ENCODING'))
		{
			if (!array_key_exists(CURLOPT_ACCEPT_ENCODING, $params))
			{
				$params[CURLOPT_ACCEPT_ENCODING] = 'identity';
			}

			$patched_accept_encoding = true;
		}

		if (!empty($params))
		{
			foreach ($params as $k => $v)
			{
				// I couldn't patch the accept encoding header (missing constant), so I'll check if we manually set it
				if (!$patched_accept_encoding && $k == CURLOPT_HTTPHEADER)
				{
					foreach ($v as $custom_header)
					{
						// Ok, we explicitly set the Accept-Encoding header, so we consider it patched
						if (stripos($custom_header, 'Accept-Encoding') !== false)
						{
							$patched_accept_encoding = true;
						}
					}
				}

				@curl_setopt($ch, $k, $v);
			}
		}

		// Accept encoding wasn't patched, let's manually do that
		if (!$patched_accept_encoding)
		{
			@curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept-Encoding: identity']);

			$patched_accept_encoding = true;
		}

		$result = curl_exec($ch);

		$errno       = curl_errno($ch);
		$errmsg      = curl_error($ch);
		$error       = '';
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($result === false)
		{
			$error = Text::sprintf('LIB_FOF_DOWNLOAD_ERR_CURL_ERROR', $errno, $errmsg);
		}
		elseif (($http_status >= 300) && ($http_status <= 399) && isset($this->headers['location']) && !empty($this->headers['location']))
		{
			return $this->downloadAndReturn($this->headers['location'], $from, $to, $params);
		}
		elseif ($http_status > 399)
		{
			$result = false;
			$errno  = $http_status;
			$error  = Text::sprintf('LIB_FOF_DOWNLOAD_ERR_HTTPERROR', $http_status);
		}

		curl_close($ch);

		if ($result === false)
		{
			throw new DownloadError($error, $errno);
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
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_CAINFO, JPATH_LIBRARIES . '/src/Http/Transport/cacert.pem');

		$data = curl_exec($ch);
		curl_close($ch);

		if ($data)
		{
			$content_length = "unknown";
			$status         = "unknown";
			$redirection    = null;

			if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/i", $data, $matches))
			{
				$status = (int) $matches[1];
			}

			if (preg_match("/Content-Length: (\d+)/i", $data, $matches))
			{
				$content_length = (int) $matches[1];
			}

			if (preg_match("/Location: (.*)/i", $data, $matches))
			{
				$redirection = (int) $matches[1];
			}

			if ($status == 200 || ($status > 300 && $status <= 308))
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

		if (strpos($data, ':') === false)
		{
			return $strlen;
		}

		[$header, $value] = explode(': ', trim($data), 2);

		$this->headers[strtolower($header)] = $value;

		return $strlen;
	}
}
