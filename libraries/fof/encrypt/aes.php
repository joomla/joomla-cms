<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage encrypt
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @note	This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
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
	/**
	 * The cipher key.
	 *
	 * @var   string
	 */
	protected $key = '';

	/**
	 * The AES encryption adapter in use.
	 *
	 * @var  FOFEncryptAesInterface
	 */
	protected $adapter;
	
	/**
	 * Initialise the AES encryption object.
	 *
	 * Note: If the key is not 16 bytes this class will do a stupid key expansion for legacy reasons (produce the
	 * SHA-256 of the key string and throw away half of it).
	 *
	 * @param   string          $key      The encryption key (password). It can be a raw key (16 bytes) or a passphrase.
	 * @param   int             $strength Bit strength (128, 192 or 256) – ALWAYS USE 128 BITS. THIS PARAMETER IS DEPRECATED.
	 * @param   string          $mode     Encryption mode. Can be ebc or cbc. We recommend using cbc.
	 * @param   FOFUtilsPhpfunc $phpfunc  For testing
	 * @param   string          $priority Priority which adapter we should try first
	 */
	public function __construct($key, $strength = 128, $mode = 'cbc', FOFUtilsPhpfunc $phpfunc = null, $priority = 'openssl')
	{
		if ($priority == 'openssl')
		{
			$this->adapter = new FOFEncryptAesOpenssl();
			
			if (!$this->adapter->isSupported($phpfunc))
			{
				$this->adapter = new FOFEncryptAesMcrypt();
			}
		}
		else
		{
			$this->adapter = new FOFEncryptAesMcrypt();
			
			if (!$this->adapter->isSupported($phpfunc))
			{
				$this->adapter = new FOFEncryptAesOpenssl();
			}
		}

		$this->adapter->setEncryptionMode($mode, $strength);
		$this->setPassword($key, true);
	}

	/**
	 * Sets the password for this instance.
	 *
	 * WARNING: Do not use the legacy mode, it's insecure
	 *
	 * @param   string $password   The password (either user-provided password or binary encryption key) to use
	 * @param   bool   $legacyMode True to use the legacy key expansion. We recommend against using it.
	 */
	public function setPassword($password, $legacyMode = false)
	{
		$this->key = $password;

		$passLength = strlen($password);

		if (function_exists('mb_strlen'))
		{
			$passLength = mb_strlen($password, 'ASCII');
		}

		// Legacy mode was doing something stupid, requiring a key of 32 bytes. DO NOT USE LEGACY MODE!
		if ($legacyMode && ($passLength != 32))
		{
			// Legacy mode: use the sha256 of the password
			$this->key = hash('sha256', $password, true);
			// We have to trim or zero pad the password (we end up throwing half of it away in Rijndael-128 / AES...)
			$this->key = $this->adapter->resizeKey($this->key, $this->adapter->getBlockSize());
		}
	}

	/**
	 * Encrypts a string using AES
	 *
	 * @param   string $stringToEncrypt The plaintext to encrypt
	 * @param   bool   $base64encoded   Should I Base64-encode the result?
	 *
	 * @return   string  The cryptotext. Please note that the first 16 bytes of
	 *                   the raw string is the IV (initialisation vector) which
	 *                   is necessary for decoding the string.
	 */
	public function encryptString($stringToEncrypt, $base64encoded = true)
	{
		$blockSize = $this->adapter->getBlockSize();
		$randVal   = new FOFEncryptRandval();
		$iv        = $randVal->generate($blockSize);

		$key        = $this->getExpandedKey($blockSize, $iv);
		$cipherText = $this->adapter->encrypt($stringToEncrypt, $key, $iv);

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
	 * @param   string $stringToDecrypt The ciphertext to decrypt. The first 16 bytes of the raw string must contain
	 *                                  the IV (initialisation vector).
	 * @param   bool   $base64encoded   Should I Base64-decode the data before decryption?
	 *
	 * @return   string  The plain text string
	 */
	public function decryptString($stringToDecrypt, $base64encoded = true)
	{
		if ($base64encoded)
		{
			$stringToDecrypt = base64_decode($stringToDecrypt);
		}

		// Extract IV
		$iv_size = $this->adapter->getBlockSize();
		$iv      = substr($stringToDecrypt, 0, $iv_size);
		$key     = $this->getExpandedKey($iv_size, $iv);

		// Decrypt the data
		$plainText = $this->adapter->decrypt($stringToDecrypt, $key);

		return $plainText;
	}

	/**
	 * Is AES encryption supported by this PHP installation?
	 *
	 * @param   FOFUtilsPhpfunc  $phpfunc
	 *
	 * @return boolean
	 */
	public static function isSupported(FOFUtilsPhpfunc $phpfunc = null)
	{
		if (!is_object($phpfunc) || !($phpfunc instanceof $phpfunc))
		{
			$phpfunc = new FOFUtilsPhpfunc();
		}

		$adapter = new FOFEncryptAesMcrypt();

		if (!$adapter->isSupported($phpfunc))
		{
			$adapter = new FOFEncryptAesOpenssl();
		}

		if (!$adapter->isSupported($phpfunc))
		{
			return false;
		}

		if (!$phpfunc->function_exists('base64_encode'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('base64_decode'))
		{
			return false;
		}

		if (!$phpfunc->function_exists('hash_algos'))
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
	 * @param $blockSize
	 * @param $iv
	 *
	 * @return string
	 */
	public function getExpandedKey($blockSize, $iv)
	{
		$key        = $this->key;
		$passLength = strlen($key);

		if (function_exists('mb_strlen'))
		{
			$passLength = mb_strlen($key, 'ASCII');
		}

		if ($passLength != $blockSize)
		{
			$iterations = 1000;
			$salt       = $this->adapter->resizeKey($iv, 16);
			$key        = hash_pbkdf2('sha256', $this->key, $salt, $iterations, $blockSize, true);
		}

		return $key;
	}
}

if (!function_exists('hash_pbkdf2'))
{
	function hash_pbkdf2($algo, $password, $salt, $count, $length = 0, $raw_output = false)
	{
		if (!in_array(strtolower($algo), hash_algos()))
		{
			trigger_error(__FUNCTION__ . '(): Unknown hashing algorithm: ' . $algo, E_USER_WARNING);
		}

		if (!is_numeric($count))
		{
			trigger_error(__FUNCTION__ . '(): expects parameter 4 to be long, ' . gettype($count) . ' given', E_USER_WARNING);
		}

		if (!is_numeric($length))
		{
			trigger_error(__FUNCTION__ . '(): expects parameter 5 to be long, ' . gettype($length) . ' given', E_USER_WARNING);
		}

		if ($count <= 0)
		{
			trigger_error(__FUNCTION__ . '(): Iterations must be a positive integer: ' . $count, E_USER_WARNING);
		}

		if ($length < 0)
		{
			trigger_error(__FUNCTION__ . '(): Length must be greater than or equal to 0: ' . $length, E_USER_WARNING);
		}

		$output      = '';
		$block_count = $length ? ceil($length / strlen(hash($algo, '', $raw_output))) : 1;

		for ($i = 1; $i <= $block_count; $i++)
		{
			$last = $xorsum = hash_hmac($algo, $salt . pack('N', $i), $password, true);

			for ($j = 1; $j < $count; $j++)
			{
				$xorsum ^= ($last = hash_hmac($algo, $last, $password, true));
			}

			$output .= $xorsum;
		}

		if (!$raw_output)
		{
			$output = bin2hex($output);
		}

		return $length ? substr($output, 0, $length) : $output;
	}
}
