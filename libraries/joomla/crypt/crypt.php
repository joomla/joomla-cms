<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @package     Joomla.Platform
 * @subpackage  Crypt
 * @since       12.1
 */
class JCrypt
{
	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since  12.1
	 */
	public static function genRandomBytes($length = 16)
	{
		$sslStr = '';
		/*
		 * if a secure randomness generator exists and we don't
		 * have a buggy PHP version use it.
		 */
		if (
			function_exists('openssl_random_pseudo_bytes')
			&& (version_compare(PHP_VERSION, '5.3.4') >= 0
				|| substr(PHP_OS, 0, 3) !== 'WIN'
			)
		)
		{
			$sslStr = openssl_random_pseudo_bytes($length, $strong);
			if ($strong)
			{
				return $sslStr;
			}
		}

		/*
		 * Collect any entropy available in the system along with a number
		 * of time measurements of operating system randomness.
		 */
		$bitsPerRound = 2;
		$maxTimeMicro = 400;
		$shaHashLength = 20;
		$randomStr = '';
		$total = $length;

		// Check if we can use /dev/urandom.
		$urandom = false;
		$handle = null;
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
			$bytes = ($total > $shaHashLength)? $shaHashLength : $total;
			$total -= $bytes;
			/*
			 * Collect any entropy available from the PHP system and filesystem.
			 * If we have ssl data that isn't strong, we use it once.
			 */
			$entropy = rand() . uniqid(mt_rand(), true) . $sslStr;
			$entropy .= implode('', @fstat(fopen( __FILE__, 'r')));
			$entropy .= memory_get_usage();
			$sslStr = '';
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
				$samples = 3;
				$duration = 0;
				for ($pass = 0; $pass < $samples; ++$pass)
				{
					$microStart = microtime(true) * 1000000;
					$hash = sha1(mt_rand(), true);
					for ($count = 0; $count < 50; ++$count)
					{
						$hash = sha1($hash, true);
					}
					$microEnd = microtime(true) * 1000000;
					$entropy .= $microStart . $microEnd;
					if ($microStart > $microEnd) {
						$microEnd += 1000000;
					}
					$duration += $microEnd - $microStart;
				}
				$duration = $duration / $samples;

				/*
				 * Based on the average time, determine the total rounds so that
				 * the total running time is bounded to a reasonable number.
				 */
				$rounds = (int)(($maxTimeMicro / $duration) * 50);

				/*
				 * Take additional measurements. On average we can expect
				 * at least $bitsPerRound bits of entropy from each measurement.
				 */
				$iter = $bytes * (int) ceil(8 / $bitsPerRound);
				for ($pass = 0; $pass < $iter; ++$pass)
				{
					$microStart = microtime(true);
					$hash = sha1(mt_rand(), true);
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
