<?php
/**
 * Part of the Joomla Framework Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filesystem;

/**
 * File system helper
 *
 * Holds support functions for the filesystem, particularly the stream
 *
 * @since  1.0
 */
class Helper
{
	/**
	 * Remote file size function for streams that don't support it
	 *
	 * @param   string  $url  TODO Add text
	 *
	 * @return  mixed
	 *
	 * @link    https://secure.php.net/manual/en/function.filesize.php#71098
	 * @since   1.0
	 */
	public static function remotefsize($url)
	{
		$sch = parse_url($url, PHP_URL_SCHEME);

		if (!\in_array($sch, array('http', 'https', 'ftp', 'ftps'), true))
		{
			return false;
		}

		if (\in_array($sch, array('http', 'https'), true))
		{
			$headers = @ get_headers($url, 1);

			if (!$headers || (!array_key_exists('Content-Length', $headers)))
			{
				return false;
			}

			return $headers['Content-Length'];
		}

		if (\in_array($sch, array('ftp', 'ftps'), true))
		{
			$server = parse_url($url, PHP_URL_HOST);
			$port   = parse_url($url, PHP_URL_PORT);
			$path   = parse_url($url, PHP_URL_PATH);
			$user   = parse_url($url, PHP_URL_USER);
			$pass   = parse_url($url, PHP_URL_PASS);

			if ((!$server) || (!$path))
			{
				return false;
			}

			if (!$port)
			{
				$port = 21;
			}

			if (!$user)
			{
				$user = 'anonymous';
			}

			if (!$pass)
			{
				$pass = '';
			}

			$ftpid = null;

			switch ($sch)
			{
				case 'ftp':
					$ftpid = @ftp_connect($server, $port);

					break;

				case 'ftps':
					$ftpid = @ftp_ssl_connect($server, $port);

					break;
			}

			if (!$ftpid)
			{
				return false;
			}

			$login = @ftp_login($ftpid, $user, $pass);

			if (!$login)
			{
				return false;
			}

			$ftpsize = ftp_size($ftpid, $path);
			ftp_close($ftpid);

			if ($ftpsize == -1)
			{
				return false;
			}

			return $ftpsize;
		}
	}

	/**
	 * Quick FTP chmod
	 *
	 * @param   string   $url   Link identifier
	 * @param   integer  $mode  The new permissions, given as an octal value.
	 *
	 * @return  mixed
	 *
	 * @link    https://secure.php.net/manual/en/function.ftp-chmod.php
	 * @since   1.0
	 */
	public static function ftpChmod($url, $mode)
	{
		$sch = parse_url($url, PHP_URL_SCHEME);

		if (($sch != 'ftp') && ($sch != 'ftps'))
		{
			return false;
		}

		$server = parse_url($url, PHP_URL_HOST);
		$port   = parse_url($url, PHP_URL_PORT);
		$path   = parse_url($url, PHP_URL_PATH);
		$user   = parse_url($url, PHP_URL_USER);
		$pass   = parse_url($url, PHP_URL_PASS);

		if ((!$server) || (!$path))
		{
			return false;
		}

		if (!$port)
		{
			$port = 21;
		}

		if (!$user)
		{
			$user = 'anonymous';
		}

		if (!$pass)
		{
			$pass = '';
		}

		$ftpid = null;

		switch ($sch)
		{
			case 'ftp':
				$ftpid = @ftp_connect($server, $port);

				break;

			case 'ftps':
				$ftpid = @ftp_ssl_connect($server, $port);

				break;
		}

		if (!$ftpid)
		{
			return false;
		}

		$login = @ftp_login($ftpid, $user, $pass);

		if (!$login)
		{
			return false;
		}

		$res = @ftp_chmod($ftpid, $mode, $path);
		ftp_close($ftpid);

		return $res;
	}

	/**
	 * Modes that require a write operation
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function getWriteModes()
	{
		return array('w', 'w+', 'a', 'a+', 'r+', 'x', 'x+');
	}

	/**
	 * Stream and Filter Support Operations
	 *
	 * Returns the supported streams, in addition to direct file access
	 * Also includes Joomla! streams as well as PHP streams
	 *
	 * @return  array  Streams
	 *
	 * @since   1.0
	 */
	public static function getSupported()
	{
		// Really quite cool what php can do with arrays when you let it...
		static $streams;

		if (!$streams)
		{
			$streams = array_merge(stream_get_wrappers(), self::getJStreams());
		}

		return $streams;
	}

	/**
	 * Returns a list of transports
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function getTransports()
	{
		// Is this overkill?
		return stream_get_transports();
	}

	/**
	 * Returns a list of filters
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function getFilters()
	{
		// Note: This will look like the getSupported() function with J! filters.
		// TODO: add user space filter loading like user space stream loading
		return stream_get_filters();
	}

	/**
	 * Returns a list of J! streams
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public static function getJStreams()
	{
		static $streams = array();

		if (!$streams)
		{
			$files = new \DirectoryIterator(__DIR__ . '/Stream');

			/** @var $file \DirectoryIterator */
			foreach ($files as $file)
			{
				// Only load for php files.
				if (!$file->isFile() || $file->getExtension() != 'php')
				{
					continue;
				}

				$streams[] = $file->getBasename('.php');
			}
		}

		return $streams;
	}

	/**
	 * Determine if a stream is a Joomla stream.
	 *
	 * @param   string  $streamname  The name of a stream
	 *
	 * @return  boolean  True for a Joomla Stream
	 *
	 * @since   1.0
	 */
	public static function isJoomlaStream($streamname)
	{
		return \in_array($streamname, self::getJStreams());
	}
}
