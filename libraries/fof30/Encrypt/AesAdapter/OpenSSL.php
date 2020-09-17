<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Encrypt\AesAdapter;

defined('_JEXEC') || die;

use FOF30\Encrypt\Randval;
use FOF30\Utils\Phpfunc;

class OpenSSL extends AbstractAdapter implements AdapterInterface
{
	/**
	 * The OpenSSL options for encryption / decryption
	 *
	 * PHP 5.3 does not have the constants OPENSSL_RAW_DATA and OPENSSL_ZERO_PADDING. In fact, the parameter
	 * is called $raw_data and is a boolean. Since integer 1 is equivalent to boolean TRUE in PHP we can get
	 * away with initializing this parameter with the integer 1.
	 *
	 * @var  int
	 */
	protected $openSSLOptions = 1;

	/**
	 * The encryption method to use
	 *
	 * @var  string
	 */
	protected $method = 'aes-128-cbc';

	public function __construct()
	{
		/**
		 * PHP 5.4 and later replaced the $raw_data parameter with the $options parameter. Instead of a boolean we need
		 * to pass some flags. Here you go.
		 *
		 * Since PHP 5.3 does NOT have the relevant constants we must NOT run this bit of code under PHP 5.3.
		 *
		 * See http://stackoverflow.com/questions/24707007/using-openssl-raw-data-param-in-openssl-decrypt-with-php-5-3#24707117
		 */
		if (version_compare(PHP_VERSION, '5.4.0', 'ge'))
		{
			$this->openSSLOptions = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
		}
	}

	public function setEncryptionMode($mode = 'cbc', $strength = 128)
	{
		static $availableAlgorithms = null;
		static $defaultAlgo = 'aes-128-cbc';

		if (!is_array($availableAlgorithms))
		{
			$availableAlgorithms = openssl_get_cipher_methods();

			foreach ([
				         'aes-256-cbc', 'aes-256-ecb', 'aes-192-cbc',
				         'aes-192-ecb', 'aes-128-cbc', 'aes-128-ecb',
			         ] as $algo)
			{
				if (in_array($algo, $availableAlgorithms))
				{
					$defaultAlgo = $algo;
					break;
				}
			}
		}

		$strength = (int) $strength;
		$mode     = strtolower($mode);

		if (!in_array($strength, [128, 192, 256]))
		{
			$strength = 256;
		}

		if (!in_array($mode, ['cbc', 'ebc']))
		{
			$mode = 'cbc';
		}

		$algo = 'aes-' . $strength . '-' . $mode;

		if (!in_array($algo, $availableAlgorithms))
		{
			$algo = $defaultAlgo;
		}

		$this->method = $algo;
	}

	public function encrypt($plainText, $key, $iv = null)
	{
		$iv_size = $this->getBlockSize();
		$key     = $this->resizeKey($key, $iv_size);
		$iv      = $this->resizeKey($iv, $iv_size);

		if (empty($iv))
		{
			$randVal = new Randval();
			$iv      = $randVal->generate($iv_size);
		}

		$plainText  .= $this->getZeroPadding($plainText, $iv_size);
		$cipherText = openssl_encrypt($plainText, $this->method, $key, $this->openSSLOptions, $iv);
		$cipherText = $iv . $cipherText;

		return $cipherText;
	}

	public function decrypt($cipherText, $key)
	{
		$iv_size    = $this->getBlockSize();
		$key        = $this->resizeKey($key, $iv_size);
		$iv         = substr($cipherText, 0, $iv_size);
		$cipherText = substr($cipherText, $iv_size);
		$plainText  = openssl_decrypt($cipherText, $this->method, $key, $this->openSSLOptions, $iv);

		return $plainText;
	}

	public function isSupported(Phpfunc $phpfunc = null)
	{
		if (!is_object($phpfunc) || !($phpfunc instanceof $phpfunc))
		{
			$phpfunc = new Phpfunc();
		}

		if (!$phpfunc->function_exists('openssl_get_cipher_methods'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_random_pseudo_bytes'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_cipher_iv_length'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_encrypt'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('openssl_decrypt'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('hash'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('hash_algos'))
		{
			return false;
		}

		$algorightms = $phpfunc->openssl_get_cipher_methods();

		if (!in_array('aes-128-cbc', $algorightms))
		{
			return false;
		}

		$algorightms = $phpfunc->hash_algos();

		if (!in_array('sha256', $algorightms))
		{
			return false;
		}

		return true;
	}

	/**
	 * @return int
	 */
	public function getBlockSize()
	{
		return openssl_cipher_iv_length($this->method);
	}
}
