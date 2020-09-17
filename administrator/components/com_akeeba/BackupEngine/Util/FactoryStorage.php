<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
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
	 * @param   string  $tag
	 *
	 * @return  string
	 */
	public function get_storage_filename($tag = null)
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
		$filename = sprintf("akstorage%s%s.php", empty($tag) ? '' : '_', $tag);

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
			return false;
		}

		return @unlink($this->get_storage_filename($tag));
	}

	/**
	 * Stores a value to the storage
	 *
	 * @param   string       $value
	 * @param   string|null  $tag
	 *
	 * @return  bool  True on success
	 */
	public function set($value, $tag = null)
	{
		$storage_filename = $this->get_storage_filename($tag);

		if (file_exists($storage_filename))
		{
			@unlink($storage_filename);
		}

		return @file_put_contents($storage_filename, $this->encode($value)) !== false;
	}

	/**
	 * Retrieves a value from storage
	 *
	 * @param   string|null  $tag
	 *
	 * @return  false|string
	 */
	public function &get($tag = null)
	{
		$ret              = false;
		$storage_filename = $this->get_storage_filename($tag);
		$data             = @file_get_contents($storage_filename);

		if ($data === false)
		{
			return $ret;
		}

		try
		{
			$ret = $this->decode($data);
		}
		catch (RuntimeException $e)
		{
			$ret = false;
		}

		unset($data);

		return $ret;
	}

	/**
	 * Encodes the (serialized) data in a format suitable for storing in a deliberately web-inaccessible PHP file
	 *
	 * @param   string  $data  The data to encode
	 *
	 * @return  string  The encoded data
	 */
	public function encode(&$data)
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

		return '<' . '?' . 'php die(); ' . '>' . '?' . "\n" .
			$encodingMethod . "\n" . $ret;
	}

	/**
	 * Decodes the data read from the deliberately web-inaccessible PHP file.
	 *
	 * @param   string  $data  The data read from the file
	 *
	 * @return  false|string  The decoded data. False if the decoding failed.
	 */
	public function decode(&$data)
	{
		// Parts: 0 = PHP die line; 1 = encoding mode; 2 = data
		$parts = explode("\n", $data, 3);

		if (count($parts) != 3)
		{
			throw new RuntimeException("Invalid backup temporary data (memory file)");
		}

		switch ($parts[1])
		{
			case 'base64';
				return base64_decode($parts[2]);
				break;

			case 'uuencode':
				return convert_uudecode($parts[2]);
				break;

			case 'plain':
				return $parts[2];
				break;

			default:
				throw new RuntimeException(sprintf('Unsupported encoding method “%s”', $parts[1]));
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
