<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Cipher;

use Defuse\Crypto\Crypto as DefuseCrypto;
use Defuse\Crypto\Key as DefuseKey;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\RuntimeTests;
use Joomla\Crypt\CipherInterface;
use Joomla\Crypt\Key;

/**
 * Joomla cipher for encryption, decryption and key generation via the php-encryption library.
 *
 * @since  __DEPLOY_VERSION__
 */
class Crypto implements CipherInterface
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 * @param   Key     $key   The key object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() !== 'crypto')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected crypto.');
		}

		// Decrypt the data.
		try
		{
			return DefuseCrypto::decrypt($data, DefuseKey::loadFromAsciiSafeString($key->getPrivate()));
		}
		catch (WrongKeyOrModifiedCiphertextException $ex)
		{
			throw new \RuntimeException('DANGER! DANGER! The ciphertext has been tampered with!', $ex->getCode(), $ex);
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new \RuntimeException('Cannot safely perform decryption', $ex->getCode(), $ex);
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
	 * @since   __DEPLOY_VERSION__
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function encrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() !== 'crypto')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected crypto.');
		}

		// Encrypt the data.
		try
		{
			return DefuseCrypto::encrypt($data, DefuseKey::loadFromAsciiSafeString($key->getPrivate()));
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new \RuntimeException('Cannot safely perform encryption', $ex->getCode(), $ex);
		}
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
		// Generate the encryption key.
		try
		{
			$public = DefuseKey::createNewRandomKey();
		}
		catch (EnvironmentIsBrokenException $ex)
		{
			throw new \RuntimeException('Cannot safely create a key', $ex->getCode(), $ex);
		}

		// Create the new encryption key object.
		return new Key('crypto', $public->saveToAsciiSafeString(), $public->getRawBytes());
	}

	/**
	 * Check if the cipher is supported in this environment.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function isSupported(): bool
	{
		try
		{
			RuntimeTests::runtimeTest();

			return true;
		}
		catch (EnvironmentIsBrokenException $e)
		{
			return false;
		}
	}
}
