<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use InvalidArgumentException;
use RuntimeException;

/**
 * Management class for temporary storage of the serialised engine state.
 */
class FactoryStorage
{
	protected static $tempFileStoragePath;

	/**
	 * Returns the fully qualified path to the storage file
	 *
	 * @param   string  $tag        Backup tag
	 * @param   string  $extension  File extension, default is php
	 *
	 * @return  string
	 */
	public function get_storage_filename($tag = null, $extension = 'php')
	{
		if (is_null(self::$tempFileStoragePath))
		{
			$registry                  = Factory::getConfiguration();
			self::$tempFileStoragePath = $registry->get('akeeba.basic.output_directory', '');

			if (empty(self::$tempFileStoragePath))
			{
				throw new InvalidArgumentException('You have not set a backup output directory.');
			}

			self::$tempFileStoragePath = rtrim(self::$tempFileStoragePath, '/\\');

			if (!is_writable(self::$tempFileStoragePath) || !is_readable(self::$tempFileStoragePath))
			{
				throw new InvalidArgumentException(sprintf('Backup output directory %s needs to be both readable and writeable to PHP for this backup software to function correctly.', self::$tempFileStoragePath));
			}
		}

		$tag      = empty($tag) ? '' : $tag;
		$filename = sprintf("akstorage%s%s.%s", empty($tag) ? '' : '_', $tag, $extension);

		return self::$tempFileStoragePath . DIRECTORY_SEPARATOR . $filename;
	}

	/**
	 * Resets the storage. This method removes all stored values.
	 *
	 * @param   null  $tag
	 *
	 * @return    bool    True on success
	 */
	public function reset($tag = null)
	{
		$filename = $this->get_storage_filename($tag);

		if (!is_file($filename) && !is_link($filename))
		{
			$filename = $this->get_storage_filename($tag, 'dat');
		}

		if (!is_file($filename) && !is_link($filename))
		{
			return false;
		}

		return @unlink($this->get_storage_filename($tag));
	}

	/**
	 * Stores a value to the storage
	 *
	 * @param   string       $value      Serialised value to store
	 * @param   string|null  $tag        Backup tag
	 * @param   string       $extension  File extension to use, default is php
	 *
	 * @return  bool  True on success
	 */
	public function set($value, $tag = null, $extension = 'php')
	{
		$storage_filename = $this->get_storage_filename($tag, $extension);

		if (file_exists($storage_filename))
		{
			@unlink($storage_filename);
		}

		$isPHPFile = strtolower($extension) == 'php';

		return @file_put_contents($storage_filename, $this->encode($value, $isPHPFile)) !== false;
	}

	/**
	 * Retrieves a value from storage
	 *
	 * @param   string|null  $tag  Backup tag. Used to determine the session state (memory) file name.
	 *
	 * @return  false|string
	 */
	public function &get($tag = null)
	{
		$ret              = false;
		$storage_filename = $this->get_storage_filename($tag);
		$isPHPFile        = true;
		$data             = @file_get_contents($storage_filename);

		/**
		 * Some hosts, like WPEngine, do not allow us to use .php files for storing the factory state. In these case we
		 * fall back to using the far less secure .dat extension. This if-block caters for that case.
		 */
		if ($data === false)
		{
			$storage_filename = $this->get_storage_filename($tag, 'dat');
			$isPHPFile        = false;
			$data             = @file_get_contents($storage_filename);
		}

		if ($data === false)
		{
			return $ret;
		}

		try
		{
			$ret = $this->decode($data, $isPHPFile);
		}
		catch (RuntimeException $e)
		{
			$ret = false;
		}

		unset($data);

		return $ret;
	}

	/**
	 * Encodes the (serialized) data in a format suitable for storing in a deliberately web-inaccessible PHP file.
	 *
	 * IMPORTANT: On some hosts we HAVE to fall back to a .dat file. This is nowhere near as secure. This is not a
	 * problem with Akeeba Backup but with the host, e.g. WPEngine. We WANT to do things securely but hosts' misguided
	 * attempts at "security" force us to have a very insecure fallback. Please do not report this as a security issue
	 * with us. report it to the host. We can't do something the host doesn't allow our code to do, obviously!
	 *
	 * @param   string  $data       The data to encode
	 * @param   bool    $isPHPFile  Is this file extension .php?
	 *
	 * @return  string  The encoded data
	 */
	public function encode(&$data, $isPHPFile = true)
	{
		$encodingMethod = $this->getEncodingMethod();
		
		switch ($encodingMethod)
		{
			case 'base64':
				$ret = base64_encode($data);
				break;

			case 'uuencode':
				$ret = convert_uuencode($data);
				break;

			case 'plain':
			default:
				$ret = $data;
				break;
		}

		if ($isPHPFile)
		{
			return '<' . '?' . 'php die(); ' . '>' . '?' . "\n" .
				$encodingMethod . "\n" . $ret;
		}

		return $encodingMethod . "\n" . $ret;
	}

	/**
	 * Decodes the data read from the deliberately web-inaccessible PHP file.
	 *
	 * @param   string  $data       The data read from the file
	 * @param   bool    $isPHPFile  Does the memory file have a .php extension?
	 *
	 * @return  false|string  The decoded data. False if the decoding failed.
	 */
	public function decode(&$data, $isPHPFile = true)
	{
		// Parts: 0 = PHP die line; 1 = encoding mode; 2 = data
		$parts = explode("\n", $data, 3);

		$expectedPartsCount = $isPHPFile ? 3 : 2;

		if (count($parts) != $expectedPartsCount)
		{
			throw new RuntimeException("Invalid backup temporary data (memory file)");
		}

		$encodingIndex = $isPHPFile ? 1 : 0;
		$dataIndex     = $isPHPFile ? 2 : 1;

		switch ($parts[$encodingIndex])
		{
			case 'base64';
				return base64_decode($parts[$dataIndex]);
				break;

			case 'uuencode':
				return convert_uudecode($parts[$dataIndex]);
				break;

			case 'plain':
				return $parts[$dataIndex];
				break;

			default:
				throw new RuntimeException(sprintf('Unsupported encoding method “%s”', $parts[$encodingIndex]));
				break;
		}
	}

	/**
	 * Get the recommended method for encoding the temporary data
	 *
	 * @return string
	 */
	protected function getEncodingMethod()
	{
		// Preferred encoding: base sixty four, handled by PHP
		if (function_exists('base64_encode') && function_exists('base64_decode'))
		{
			return 'base64';
		}

		// Fallback: UUencoding
		if (function_exists('convert_uuencode') && function_exists('convert_uudecode'))
		{
			return 'uuencode';
		}

		// Final fallback (should NOT be necessary): plain text encoding
		return 'plain';
	}
}
