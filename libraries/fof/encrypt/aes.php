<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage encrypt
 * @copyright  Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * A simple implementation of AES-128, AES-192 and AES-256 encryption using the
 * high performance mcrypt library.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFEncryptAes
{
	/** @var string The AES cipher to use (this is an mcrypt identifier, not the bit strength) */
	private $_cipherType = 0;

	/** @var string Cipher mode. Can be CBC or ECB. We recommend using CBC */
	private $_cipherMode = 0;

	/** @var string The cipher key (password) */
	private $_keyString = '';

	/**
	 * Initialise the AES encryption object
	 *
	 * @param   string  $key       The encryption key (password). It can be a raw key (32 bytes) or a passphrase.
	 * @param   int     $strength  Bit strength (128, 192 or 256)
	 * @param   string  $mode      Ecnryption mode. Can be ebc or cbc. We recommend using cbc.
	 */
	public function __construct($key, $strength = 256, $mode = 'cbc')
	{
		$this->_keyString = $key;

		switch ($strength)
		{
			case 256:
			default:
				$this->_cipherType = MCRYPT_RIJNDAEL_256;
				break;

			case 192:
				$this->_cipherType = MCRYPT_RIJNDAEL_192;
				break;

			case 128:
				$this->_cipherType = MCRYPT_RIJNDAEL_128;
				break;
		}

		switch (strtoupper($mode))
		{
			case 'ECB':
				$this->_cipherMode = MCRYPT_MODE_ECB;
				break;

			case 'CBC':
				$this->_cipherMode = MCRYPT_MODE_CBC;
				break;
		}
	}

	/**
	 * Encrypts a string using AES
	 *
	 * @param   string  $stringToEncrypt  The plaintext to encrypt
	 * @param   bool    $base64encoded    Should I Base64-encode the result?
	 *
	 * @return   string  The cryptotext. Please note that the first 16 bytes of
	 *                   the raw string is the IV (initialisation vector) which
	 *                   is necessary for decoding the string.
	 */
	public function encryptString($stringToEncrypt, $base64encoded = true)
	{
		if (strlen($this->_keyString) != 32)
		{
			$key = hash('sha256', $this->_keyString, true);
		}
		else
		{
			$key = $this->_keyString;
		}

		// Set up the IV (Initialization Vector)
		$iv_size = mcrypt_get_iv_size($this->_cipherType, $this->_cipherMode);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);

		if (empty($iv))
		{
			$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_RANDOM);
		}

		if (empty($iv))
		{
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		}

		// Encrypt the data
		$cipherText = mcrypt_encrypt($this->_cipherType, $key, $stringToEncrypt, $this->_cipherMode, $iv);

		// Prepend the IV to the ciphertext
		$cipherText = $iv . $cipherText;

		// Optionally pass the result through Base64 encoding
		if ($base64encoded)
		{
			$cipherText = base64_encode($cipherText);
		}

		// Return the result
		return $cipherText;
	}

	/**
	 * Decrypts a ciphertext into a plaintext string using AES
	 *
	 * @param   string  $stringToDecrypt  The ciphertext to decrypt. The first 16 bytes of the raw string must contain the IV (initialisation vector).
	 * @param   bool    $base64encoded    Should I Base64-decode the data before decryption?
	 *
	 * @return   string  The plain text string
	 */
	public function decryptString($stringToDecrypt, $base64encoded = true)
	{
		if (strlen($this->_keyString) != 32)
		{
			$key = hash('sha256', $this->_keyString, true);
		}
		else
		{
			$key = $this->_keyString;
		}

		if ($base64encoded)
		{
			$stringToDecrypt = base64_decode($stringToDecrypt);
		}

		// Calculate the IV size
		$iv_size = mcrypt_get_iv_size($this->_cipherType, $this->_cipherMode);

		// Extract IV
		$iv = substr($stringToDecrypt, 0, $iv_size);
		$stringToDecrypt = substr($stringToDecrypt, $iv_size);

		// Decrypt the data
		$plainText = mcrypt_decrypt($this->_cipherType, $key, $stringToDecrypt, $this->_cipherMode, $iv);

		return $plainText;
	}

	/**
	 * Is AES encryption supported by this PHP installation?
	 *
	 * @return boolean
	 */
	public static function isSupported()
	{
		if (!function_exists('mcrypt_get_key_size'))
		{
			return false;
		}

		if (!function_exists('mcrypt_get_iv_size'))
		{
			return false;
		}

		if (!function_exists('mcrypt_create_iv'))
		{
			return false;
		}

		if (!function_exists('mcrypt_encrypt'))
		{
			return false;
		}

		if (!function_exists('mcrypt_decrypt'))
		{
			return false;
		}

		if (!function_exists('mcrypt_list_algorithms'))
		{
			return false;
		}

		if (!function_exists('hash'))
		{
			return false;
		}

		if (!function_exists('hash_algos'))
		{
			return false;
		}

		if (!function_exists('base64_encode'))
		{
			return false;
		}

		if (!function_exists('base64_decode'))
		{
			return false;
		}

		$algorightms = mcrypt_list_algorithms();

		if (!in_array('rijndael-128', $algorightms))
		{
			return false;
		}

		if (!in_array('rijndael-192', $algorightms))
		{
			return false;
		}

		if (!in_array('rijndael-256', $algorightms))
		{
			return false;
		}

		$algorightms = hash_algos();

		if (!in_array('sha256', $algorightms))
		{
			return false;
		}

		return true;
	}
}
