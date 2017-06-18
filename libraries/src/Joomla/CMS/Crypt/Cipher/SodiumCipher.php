<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Crypt\CipherInterface;
use Joomla\CMS\Crypt\Key;

/**
 * Crypt cipher for sodium algorithm encryption, decryption and key generation.
 *
 * @since  __DEPLOY_VERSION__
 */
class SodiumCipher implements CipherInterface
{
	/**
	 * The message nonce to be used with encryption/decryption
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $nonce;

	/**
	 * Constructor.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function __construct()
	{
		if (!static::isSupported())
		{
			throw new \RuntimeException('The libsodium extension is not available.');
		}
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   Key     $key   The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->type !== 'sodium')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected sodium.');
		}

		if (!$this->nonce)
		{
			throw new \RuntimeException('Missing nonce to decrypt data');
		}

		$decrypted = $this->callCryptoBoxOpen(
			$data,
			$this->nonce,
			$this->callCryptoBoxKeypairFromSecretkeyAndPublickey($key->private, $key->public)
		);

		if ($decrypted === false)
		{
			throw new \RuntimeException('Malformed message or invalid MAC');
		}

		return $decrypted;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 * @param   Key     $key   The key object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function encrypt($data, Key $key)
	{
		// Validate key.
		if ($key->type !== 'sodium')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected sodium.');
		}

		if (!$this->nonce)
		{
			throw new \RuntimeException('Missing nonce to decrypt data');
		}

		return $this->callCryptoBox(
			$data,
			$this->nonce,
			$this->callCryptoBoxKeypairFromSecretkeyAndPublickey($key->private, $key->public)
		);
	}

	/**
	 * Method to generate a new encryption key object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		// Create the new encryption key object.
		$key = new Key('sodium');

		// Generate the encryption key.
		$pair = $this->callCryptoBoxKeypair();

		$key->public  = $this->callCryptoBoxPublickey($pair);
		$key->private = $this->callCryptoBoxSecretkey($pair);

		return $key;
	}

	/**
	 * Test to see if the encryption cipher is available.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported()
	{
		// Requires PHP 5.4
		if (version_compare(PHP_VERSION, '5.4', '<'))
		{
			return false;
		}

		// Requires libsodium extension, either from PECL or in core PHP
		if (!extension_loaded('libsodium'))
		{
			return false;
		}

		// The environment is supported
		return true;
	}

	/**
	 * Set the nonce to use for encrypting/decrypting messages
	 *
	 * @param   string  $nonce  The message nonce
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setNonce($nonce)
	{
		$this->nonce = $nonce;
	}

	/**
	 * Calls the `crypto_box` function from the available `libsodium` implementation
	 *
	 * @param   string  $msg      Plaintext message
	 * @param   string  $nonce    Number to only be used Once; must be 24 bytes
	 * @param   string  $keypair  Your secret key and your recipient's public key
	 *
	 * @return  string  The encrypted message from the `libsodium` function
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBox($msg, $nonce, $keypair)
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box'))
		{
			return sodium_crypto_box($msg, $nonce, $keypair);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box'))
		{
			return \Sodium\crypto_box($msg, $nonce, $keypair);
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box" function');
	}

	/**
	 * Calls the `crypto_box_keypair` function from the available `libsodium` implementation
	 *
	 * @return  string  The X25519 keypair
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBoxKeypair()
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box_keypair'))
		{
			return sodium_crypto_box_keypair();
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box_keypair'))
		{
			return \Sodium\crypto_box_keypair();
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box_keypair" function');
	}

	/**
	 * Calls the `crypto_box_keypair_from_secretkey_and_publickey` function from the available `libsodium` implementation
	 *
	 * @param   string  $secretKey  Secret key
	 * @param   string  $publicKey  Public key
	 *
	 * @return  string  The keypair
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBoxKeypairFromSecretkeyAndPublickey($secretKey, $publicKey)
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box_keypair_from_secretkey_and_publickey'))
		{
			return sodium_crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box_keypair_from_secretkey_and_publickey'))
		{
			return \Sodium\crypto_box_keypair_from_secretkey_and_publickey($secretKey, $publicKey);
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box_keypair_from_secretkey_and_publickey" function');
	}

	/**
	 * Calls the `crypto_box_open` function from the available `libsodium` implementation
	 *
	 * @param   string  $msg      Encrypted message
	 * @param   string  $nonce    Number to only be used Once; must be 24 bytes
	 * @param   string  $keypair  Your secret key and the sender's public key
	 *
	 * @return  string  The decrypted message from the `libsodium` function
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBoxOpen($msg, $nonce, $keypair)
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box_open'))
		{
			return sodium_crypto_box_open($msg, $nonce, $keypair);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box_open'))
		{
			return \Sodium\crypto_box_open($msg, $nonce, $keypair);
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box_open" function');
	}

	/**
	 * Calls the `crypto_box_publickey` function from the available `libsodium` implementation
	 *
	 * @param   string  $keypair  The X25519 keypair
	 *
	 * @return  string  Your crypto_box public key
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBoxPublickey($keypair)
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box_publickey'))
		{
			return sodium_crypto_box_publickey($keypair);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box_publickey'))
		{
			return \Sodium\crypto_box_publickey($keypair);
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box_publickey" function');
	}

	/**
	 * Calls the `crypto_box_secretkey` function from the available `libsodium` implementation
	 *
	 * @param   string  $keypair  The X25519 keypair
	 *
	 * @return  string  Your crypto_box secret key
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function callCryptoBoxSecretkey($keypair)
	{
		// Try calling core PHP function
		if (is_callable('sodium_crypto_box_secretkey'))
		{
			return sodium_crypto_box_secretkey($keypair);
		}

		// Try calling PECL function
		if (is_callable('\\Sodium\\crypto_box_secretkey'))
		{
			return \Sodium\crypto_box_secretkey($keypair);
		}

		// Neither method is available, panic
		throw new \RuntimeException('Could not call "crypto_box_secretkey" function');
	}
}
