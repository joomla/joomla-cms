<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

defined('JPATH_PLATFORM') or die;

/**
 * File system helper
 *
 * Holds support functions for the filesystem, particularly the stream
 *
 * @since  1.7.0
 */
class FilesystemHelper
{
	/**
	 * Remote file size function for streams that don't support it
	 *
	 * @param   string  $url  TODO Add text
	 *
	 * @return  mixed
	 *
	 * @link    https://www.php.net/manual/en/function.filesize.php
	 * @since   1.7.0
	 */
	public static function remotefsize($url)
	{
		$sch = parse_url($url, PHP_URL_SCHEME);

		if (($sch != 'http') && ($sch != 'https') && ($sch != 'ftp') && ($sch != 'ftps'))
		{
			return false;
		}

		if (($sch == 'http') || ($sch == 'https'))
		{
			$headers = get_headers($url, 1);

			if ((!array_key_exists('Content-Length', $headers)))
			{
				return false;
			}

			return $headers['Content-Length'];
		}

		if (($sch == 'ftp') || ($sch == 'ftps'))
		{
			$server = parse_url($url, PHP_URL_HOST);
			$port = parse_url($url, PHP_URL_PORT);
			$path = parse_url($url, PHP_URL_PATH);
			$user = parse_url($url, PHP_URL_USER);
			$pass = parse_url($url, PHP_URL_PASS);

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

			switch ($sch)
			{
				case 'ftp':
					$ftpid = ftp_connect($server, $port);
					break;

				case 'ftps':
					$ftpid = ftp_ssl_connect($server, $port);
					break;
			}

			if (!$ftpid)
			{
				return false;
			}

			$login = ftp_login($ftpid, $user, $pass);

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
	 * @link    https://www.php.net/manual/en/function.ftp-chmod.php
	 * @since   1.7.0
	 */
	public static function ftpChmod($url, $mode)
	{
		$sch = parse_url($url, PHP_URL_SCHEME);

		if (($sch != 'ftp') && ($sch != 'ftps'))
		{
			return false;
		}

		$server = parse_url($url, PHP_URL_HOST);
		$port = parse_url($url, PHP_URL_PORT);
		$path = parse_url($url, PHP_URL_PATH);
		$user = parse_url($url, PHP_URL_USER);
		$pass = parse_url($url, PHP_URL_PASS);

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

		switch ($sch)
		{
			case 'ftp':
				$ftpid = ftp_connect($server, $port);
				break;

			case 'ftps':
				$ftpid = ftp_ssl_connect($server, $port);
				break;
		}

		if (!$ftpid)
		{
			return false;
		}

		$login = ftp_login($ftpid, $user, $pass);

		if (!$login)
		{
			return false;
		}

		$res = ftp_chmod($ftpid, $mode, $path);
		ftp_close($ftpid);

		return $res;
	}

	/**
	 * Modes that require a write operation
	 *
	 * @return  array
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public static function getJStreams()
	{
		static $streams = array();

		if (!$streams)
		{
			$files = new \DirectoryIterator(__DIR__ . '/Streams');

			/* @type  $file  DirectoryIterator */
			foreach ($files as $file)
			{
				// Only load for php files.
				if (!$file->isFile() || $file->getExtension() !== 'php')
				{
					continue;
				}

				$streams[] = str_replace('stream', '', strtolower($file->getBasename('.php')));
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
	 * @since   1.7.0
	 */
	public static function isJoomlaStream($streamname)
	{
		return in_array($streamname, self::getJStreams());
	}

	/**
	 * Calculates the maximum upload file size and returns string with unit or the size in bytes
	 *
	 * Call it with JFilesystemHelper::fileUploadMaxSize();
	 *
	 * @param   bool  $unitOutput  This parameter determines whether the return value should be a string with a unit
	 *
	 * @return  float|string The maximum upload size of files with the appropriate unit or in bytes
	 *
	 * @since   3.4
	 */
	public static function fileUploadMaxSize($unitOutput = true)
	{
		static $max_size = false;
		static $output_type = true;

		if ($max_size === false || $output_type != $unitOutput)
		{
			$max_size   = self::parseSize(ini_get('post_max_size'));
			$upload_max = self::parseSize(ini_get('upload_max_filesize'));

			if ($upload_max > 0 && ($upload_max < $max_size || $max_size == 0))
			{
				$max_size = $upload_max;
			}

			if ($unitOutput == true)
			{
				$max_size = self::parseSizeUnit($max_size);
			}

			$output_type = $unitOutput;
		}

		return $max_size;
	}

	/**
	 * Returns the size in bytes without the unit for the comparison
	 *
	 * @param   string  $size  The size which is received from the PHP settings
	 *
	 * @return  float The size in bytes without the unit
	 *
	 * @since   3.4
	 */
	private static function parseSize($size)
	{
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
		$size = preg_replace('/[^0-9\.]/', '', $size);

		$return = round($size);

		if ($unit)
		{
			$return = round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}

		return $return;
	}

	/**
	 * Creates the rounded size of the size with the appropriate unit
	 *
	 * @param   float  $maxSize  The maximum size which is allowed for the uploads
	 *
	 * @return  string String with the size and the appropriate unit
	 *
	 * @since   3.4
	 */
	private static function parseSizeUnit($maxSize)
	{
		$base     = log($maxSize) / log(1024);
		$suffixes = array('', 'k', 'M', 'G', 'T');

		return round(pow(1024, $base - floor($base)), 0) . $suffixes[floor($base)];
	}
}
