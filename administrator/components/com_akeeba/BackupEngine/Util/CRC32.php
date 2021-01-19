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

/**
 * A handy class to abstract the calculation of CRC32 of files under various
 * server conditions and versions of PHP.
 */
class CRC32
{
	/**
	 * Returns the CRC32 of a file, selecting the more appropriate algorithm.
	 *
	 * @param   string   $filename                    Absolute path to the file being processed
	 * @param   integer  $AkeebaPackerZIP_CHUNK_SIZE  Obsoleted
	 *
	 * @return integer The CRC32 in numerical form
	 */
	public function crc32_file($filename, $AkeebaPackerZIP_CHUNK_SIZE)
	{
		static $configuration;

		if (!$configuration)
		{
			$configuration = Factory::getConfiguration();
		}

		$res = false;

		if (function_exists("hash_file"))
		{
			$res = $this->crc32UsingHashExtension($filename);

			Factory::getLog()->debug("File $filename - CRC32 = " . dechex($res) . " [HASH_FILE]");
		}
		else if (function_exists("file_get_contents") && (@filesize($filename) <= $AkeebaPackerZIP_CHUNK_SIZE))
		{
			$res = $this->crc32Legacy($filename);

			Factory::getLog()->debug("File $filename - CRC32 = " . dechex($res) . " [FILE_GET_CONTENTS]");
		}
		else
		{
			$res = 0;

			Factory::getLog()->debug("File $filename - CRC32 = " . dechex($res) . " [FAKE - CANNOT CALCULATE]");
		}

		if ($res === false)
		{
			$res = 0;

			Factory::getLog()->warning("File $filename - NOT READABLE: CRC32 IS WRONG!");
		}

		return $res;
	}

	/**
	 * Very efficient CRC32 calculation using the PHP 'hash' extension.
	 *
	 * @param   string  $filename  Absolute filepath
	 *
	 * @return integer The CRC32
	 */
	protected function crc32UsingHashExtension($filename)
	{
		// Detection of buggy PHP hosts
		static $mustInvert = null;

		if (is_null($mustInvert))
		{
			$test_crc   = @hash('crc32b', 'test', false);
			$mustInvert = (strtolower($test_crc) == '0c7e7fd8'); // Normally, it's D87F7E0C :)

			if ($mustInvert)
			{
				Factory::getLog()->warning('Your server has a buggy PHP version which produces inverted CRC32 values. Attempting a workaround. ZIP files may appear as corrupt.');
			}
		}

		$res = @hash_file('crc32b', $filename, false);

		if ($mustInvert)
		{
			// Workaround for buggy PHP versions (I think before 5.1.8) which produce inverted CRC32 sums
			$res2 = substr($res, 6, 2) . substr($res, 4, 2) . substr($res, 2, 2) . substr($res, 0, 2);
			$res  = $res2;
		}

		$res = hexdec($res);

		return $res;
	}

	/**
	 * A compatible CRC32 calculation using file_get_contents, utilizing immense amounts of RAM
	 *
	 * @param   string  $filename
	 *
	 * @return integer
	 */
	protected function crc32Legacy($filename)
	{
		return crc32(@file_get_contents($filename));
	}
}
