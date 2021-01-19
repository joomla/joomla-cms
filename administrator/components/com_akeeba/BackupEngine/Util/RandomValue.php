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

/**
 * Crypto-safe random value generator. Based on the Randval class of the Aura for PHP's Session package.
 * The following is the license file accompanying the original file.
 *
 * ********************************************************************************
 * Copyright (c) 2011-2016, Aura for PHP
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * - Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * ********************************************************************************
 *
 * Please note that this is a MODIFIED copy of the Randval class, mainly to allow it to be used on hosts
 * which lack both mbcrypt and OpenSSL PHP modules.
 */
class RandomValue
{
	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * @param   integer  $bytes  How many bytes to return
	 *
	 * @return  string
	 */
	public function generate($bytes = 32)
	{
		if (function_exists('extension_loaded') && function_exists('openssl_random_pseudo_bytes'))
		{
			defined('IS_WIN') || define('IS_WIN', DIRECTORY_SEPARATOR == '\\');

			if (extension_loaded('openssl') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || IS_WIN))
			{
				$strong    = false;
				$randBytes = openssl_random_pseudo_bytes($bytes, $strong);

				if ($strong)
				{
					return $randBytes;
				}
			}
		}

		if (function_exists('extension_loaded') && function_exists('mcrypt_create_iv'))
		{
			if (extension_loaded('mcrypt'))
			{
				return mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
			}
		}

		return $this->genRandomBytes($bytes);
	}

	/**
	 * Generates a random string with the specified length. WARNING: You get to specify the number of
	 * random characters in the string, not the number of random bytes. The character pool is 64 characters
	 * (6 bits) long. The entropy of your string is 6 * $characters bits. This means that a random string
	 * of 32 characters has an entropy of 192 bits whereas a random sequence of 32 bytes returned by generate()
	 * has an entropy of 8 * 32 = 256 bits.
	 *
	 * @param   int  $characters
	 *
	 * @return string
	 */
	public function generateString($characters = 32)
	{
		$sourceString = str_split('abcdefghijklmnopqrstuvwxyz-ABCDEFGHIJKLMNOPQRSTUVWXYZ_0123456789', 1);
		$ret          = '';

		$bytes     = ceil($characters / 4) * 3;
		$randBytes = $this->generate($bytes);

		for ($i = 0; $i < $bytes; $i += 3)
		{
			$subBytes = substr($randBytes, $i, 3);
			$subBytes = str_split($subBytes, 1);
			$subBytes = ord($subBytes[0]) * 65536 + ord($subBytes[1]) * 256 + ord($subBytes[2]);
			$subBytes = $subBytes & bindec('00000000111111111111111111111111');

			$b    = [];
			$b[0] = $subBytes >> 18;
			$b[1] = ($subBytes >> 12) & bindec('111111');
			$b[2] = ($subBytes >> 6) & bindec('111111');
			$b[3] = $subBytes & bindec('111111');

			$ret .= $sourceString[$b[0]] . $sourceString[$b[1]] . $sourceString[$b[2]] . $sourceString[$b[3]];
		}

		return substr($ret, 0, $characters);
	}

	/**
	 * Generate random bytes. Adapted from Joomla! 3.2.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 */
	protected function genRandomBytes($length = 32)
	{
		$length = (int) $length;
		$sslStr = '';

		/*
		 * Collect any entropy available in the system along with a number
		 * of time measurements of operating system randomness.
		 */
		$bitsPerRound  = 2;
		$maxTimeMicro  = 400;
		$shaHashLength = 20;
		$randomStr     = '';
		$total         = $length;

		// Check if we can use /dev/urandom.
		$urandom = false;
		$handle  = null;

		// This is PHP 5.3.3 and up
		if (function_exists('stream_set_read_buffer') && @is_readable('/dev/urandom'))
		{
			$handle = @fopen('/dev/urandom', 'rb');

			if ($handle)
			{
				$urandom = true;
			}
		}

		while ($length > strlen($randomStr))
		{
			$bytes = ($total > $shaHashLength) ? $shaHashLength : $total;
			$total -= $bytes;

			/*
			 * Collect any entropy available from the PHP system and filesystem.
			 * If we have ssl data that isn't strong, we use it once.
			 */
			$entropy = random_int(0, mt_getrandmax()) . uniqid(random_int(0, mt_getrandmax()), true) . $sslStr;
			$entropy .= implode('', @fstat(fopen(__FILE__, 'r')));
			$entropy .= memory_get_usage();
			$sslStr  = '';

			if ($urandom)
			{
				stream_set_read_buffer($handle, 0);
				$entropy .= @fread($handle, $bytes);
			}
			else
			{
				/*
				 * There is no external source of entropy so we repeat calls
				 * to mt_rand until we are assured there's real randomness in
				 * the result.
				 *
				 * Measure the time that the operations will take on average.
				 */
				$samples  = 3;
				$duration = 0;

				for ($pass = 0; $pass < $samples; ++$pass)
				{
					$microStart = microtime(true) * 1000000;
					$hash       = sha1(random_int(0, mt_getrandmax()), true);

					for ($count = 0; $count < 50; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$microEnd = microtime(true) * 1000000;
					$entropy  .= $microStart . $microEnd;

					if ($microStart >= $microEnd)
					{
						$microEnd += 1000000;
					}

					$duration += $microEnd - $microStart;
				}

				$duration = $duration / $samples;

				/*
				 * Based on the average time, determine the total rounds so that
				 * the total running time is bounded to a reasonable number.
				 */
				$rounds = (int) (($maxTimeMicro / $duration) * 50);

				/*
				 * Take additional measurements. On average we can expect
				 * at least $bitsPerRound bits of entropy from each measurement.
				 */
				$iter = $bytes * (int) ceil(8 / $bitsPerRound);

				for ($pass = 0; $pass < $iter; ++$pass)
				{
					$microStart = microtime(true);
					$hash       = sha1(random_int(0, mt_getrandmax()), true);

					for ($count = 0; $count < $rounds; ++$count)
					{
						$hash = sha1($hash, true);
					}

					$entropy .= $microStart . microtime(true);
				}
			}

			$randomStr .= sha1($entropy, true);
		}

		if ($urandom)
		{
			@fclose($handle);
		}

		return substr($randomStr, 0, $length);
	}
}
