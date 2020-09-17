<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Encrypt;

defined('_JEXEC') || die;

use FOF30\Utils\Phpfunc;

/**
 * Generates cryptographically-secure random values.
 */
class Randval implements RandvalInterface
{
	/**
	 * @var Phpfunc
	 */
	protected $phpfunc;

	/**
	 *
	 * Constructor.
	 *
	 * @param   Phpfunc  $phpfunc  An object to intercept PHP function calls;
	 *                             this makes testing easier.
	 *
	 */
	public function __construct(Phpfunc $phpfunc = null)
	{
		if (!is_object($phpfunc) || !($phpfunc instanceof Phpfunc))
		{
			$phpfunc = new Phpfunc();
		}

		$this->phpfunc = $phpfunc;
	}

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
		if ($this->phpfunc->extension_loaded('openssl') && (version_compare(PHP_VERSION, '5.3.4') >= 0 || IS_WIN))
		{
			$strong    = false;
			$randBytes = openssl_random_pseudo_bytes($bytes, $strong);

			if ($strong)
			{
				return $randBytes;
			}
		}

		return $this->genRandomBytes($bytes);
	}

	/**
	 * Generate random bytes. Adapted from Joomla! 3.2.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 */
	public function genRandomBytes($length = 32)
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
		if ($this->phpfunc->function_exists('stream_set_read_buffer') && @is_readable('/dev/urandom'))
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

	/**
	 * Return a randomly generated password using safe characters (a-z, A-Z, 0-9).
	 *
	 * @param   int  $length  How many characters long should the password be. Default is 64.
	 *
	 * @return  string
	 *
	 * @since   3.3.2
	 */
	public function getRandomPassword($length = 64)
	{
		$salt     = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$base     = strlen($salt);
		$makepass = '';

		/*
		 * Start with a cryptographic strength random string, then convert it to
		 * a string with the numeric base of the salt.
		 * Shift the base conversion on each character so the character
		 * distribution is even, and randomize the start shift so it's not
		 * predictable.
		 */
		$random = $this->generate($length + 1);
		$shift  = ord($random[0]);

		for ($i = 1; $i <= $length; ++$i)
		{
			$makepass .= $salt[($shift + ord($random[$i])) % $base];
			$shift    += ord($random[$i]);
		}

		return $makepass;
	}

}
