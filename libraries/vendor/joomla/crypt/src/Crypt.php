<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt;

use Joomla\Crypt\Cipher\Crypto;

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
	public function __construct(CipherInterface $cipher = null, Key $key = null)
	{
		// Set the encryption cipher.
		$this->cipher = isset($cipher) ? $cipher : new Crypto;

		// Set the encryption key[/pair)].
		$this->key = isset($key) ? $key : $this->generateKey();
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   1.0
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
	 */
	public function generateKey(array $options = array())
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
		// This method is backported by the paragonie/random_compat library and native in PHP 7
		return random_bytes($length);
	}
}
