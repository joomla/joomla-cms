<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Crypt\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key as CryptoKey;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

/**
 * JCrypt cipher for encryption, decryption and key generation via the php-encryption library.
 *
 * @since  3.5
 */
class JCryptCipherCrypto implements JCryptCipher
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   Key     $key   The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() != 'crypto')
		{
			throw new InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected crypto.');
		}

		$cryptoKey = CryptoKey::loadFromAsciiSafeString($key->getPublic());

		// Decrypt the data.
		try
		{
			return Crypto::Decrypt($data, $cryptoKey);
		}
		catch (WrongKeyOrModifiedCiphertextException $ex)
		{
			throw new RuntimeException('DANGER! DANGER! The ciphertext has been tampered with!', $ex->getCode(), $ex);
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new RuntimeException('Cannot safely perform decryption', $ex->getCode(), $ex);
		}
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 * @param   Key     $key   The key object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	public function encrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() != 'crypto')
		{
			throw new InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected crypto.');
		}

		$cryptoKey = CryptoKey::loadFromAsciiSafeString($key->getPublic());

		// Encrypt the data.
		try
		{
			return Crypto::Encrypt($data, $cryptoKey);
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new RuntimeException('Cannot safely perform encryption', $ex->getCode(), $ex);
		}
	}

	/**
	 * Method to generate a new encryption key object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		// Generate the encryption key.
		try
		{
			$public = CryptoKey::CreateNewRandomKey();
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new RuntimeException('Cannot safely create a key', $ex->getCode(), $ex);
		}

		// Explicitly flag the private as unused in this cipher.
		$private = 'unused';

		return new Key('crypto', $private, $public);
	}
}
