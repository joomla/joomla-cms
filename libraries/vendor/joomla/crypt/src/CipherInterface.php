<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt;

use Joomla\Crypt\Exception\DecryptionException;
use Joomla\Crypt\Exception\EncryptionException;
use Joomla\Crypt\Exception\InvalidKeyException;
use Joomla\Crypt\Exception\InvalidKeyTypeException;
use Joomla\Crypt\Exception\UnsupportedCipherException;

/**
 * Joomla Framework Cipher interface.
 *
 * @since  1.0
 */
interface CipherInterface
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   Key     $key   The key[/pair] object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   1.0
	 * @throws  DecryptionException if the data cannot be decrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 * @throws  UnsupportedCipherException if the cipher is not supported on the current environment
	 */
	public function decrypt($data, Key $key);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 * @param   Key     $key   The key[/pair] object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   1.0
	 * @throws  EncryptionException if the data cannot be encrypted
	 * @throws  InvalidKeyTypeException if the key is not valid for the cipher
	 * @throws  UnsupportedCipherException if the cipher is not supported on the current environment
	 */
	public function encrypt($data, Key $key);

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
	public function generateKey(array $options = []);

	/**
	 * Check if the cipher is supported in this environment.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported(): bool;
}
