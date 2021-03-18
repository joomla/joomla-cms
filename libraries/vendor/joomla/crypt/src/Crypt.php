<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt;

use Joomla\Crypt\Cipher\Crypto;
use Joomla\Crypt\Exception\DecryptionException;
use Joomla\Crypt\Exception\EncryptionException;
use Joomla\Crypt\Exception\InvalidKeyException;
use Joomla\Crypt\Exception\InvalidKeyTypeException;
use Joomla\Crypt\Exception\UnsupportedCipherException;

/**
 * Crypt is a Joomla Framework class for handling basic encryption/decryption of data.
 *
 * @since  1.0
 */
class Crypt
{
	/**
	 * The encryption cipher object.
	 *
	 * @var    CipherInterface
	 * @since  1.0
	 */
	private $cipher;

	/**
	 * The encryption key[/pair)].
	 *
	 * @var    Key
	 * @since  1.0
	 */
	private $key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   CipherInterface  $cipher  The encryption cipher object.
	 * @param   Key              $key     The encryption key[/pair)].
	 *
	 * @since   1.0
	 */
	public function __construct(?CipherInterface $cipher = null, ?Key $key = null)
	{
		// Set the encryption cipher.
		$this->cipher = $cipher ?: new Crypto;

		// Set the encryption key[/pair)].
		$this->key = $key ?: $this->generateKey();
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   1.0
	 * @throws  DecryptionException if the data cannot be decrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 * @throws  UnsupportedCipherException if the cipher is not supported on the current environment
	 */
	public function decrypt($data)
	{
		return $this->cipher->decrypt($data, $this->key);
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 * @throws  EncryptionException if the data cannot be encrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 * @throws  UnsupportedCipherException if the cipher is not supported on the current environment
	 */
	public function encrypt($data)
	{
		return $this->cipher->encrypt($data, $this->key);
	}

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   1.0
	 * @throws  InvalidKeyException if the key cannot be generated
	 * @throws  UnsupportedCipherException if the cipher is not supported on the current environment
	 */
	public function generateKey(array $options = [])
	{
		return $this->cipher->generateKey($options);
	}

	/**
	 * Method to set the encryption key[/pair] object.
	 *
	 * @param   Key  $key  The key object to set.
	 *
	 * @return  Crypt  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function setKey(Key $key)
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Generate random bytes.
	 *
	 * @param   integer  $length  Length of the random data to generate
	 *
	 * @return  string  Random binary data
	 *
	 * @since   1.0
	 */
	public static function genRandomBytes($length = 16)
	{
		return random_bytes($length);
	}
}
