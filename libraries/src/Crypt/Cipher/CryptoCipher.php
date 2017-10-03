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
 * Crypt cipher for encryption, decryption and key generation via the php-encryption library.
 *
 * @since  3.5
 */
class CryptoCipher implements CipherInterface
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
	 * @throws  \RuntimeException
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->type != 'crypto')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected crypto.');
		}

		// Decrypt the data.
		try
		{
			return \Crypto::Decrypt($data, $key->public);
		}
		catch (\InvalidCiphertextException $ex)
		{
			throw new \RuntimeException('DANGER! DANGER! The ciphertext has been tampered with!', $ex->getCode(), $ex);
		}
		catch (\CryptoTestFailedException $ex)
		{
			throw new \RuntimeException('Cannot safely perform decryption', $ex->getCode(), $ex);
		}
		catch (\CannotPerformOperationException $ex)
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
	 * @since   3.5
	 * @throws  \RuntimeException
	 */
	public function encrypt($data, Key $key)
	{
		// Validate key.
		if ($key->type != 'crypto')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected crypto.');
		}

		// Encrypt the data.
		try
		{
			return \Crypto::Encrypt($data, $key->public);
		}
		catch (\CryptoTestFailedException $ex)
		{
			throw new \RuntimeException('Cannot safely perform encryption', $ex->getCode(), $ex);
		}
		catch (\CannotPerformOperationException $ex)
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
	 * @since   3.5
	 * @throws  \RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		// Create the new encryption key object.
		$key = new Key('crypto');

		// Generate the encryption key.
		try
		{
			$key->public = \Crypto::CreateNewRandomKey();
		}
		catch (\CryptoTestFailedException $ex)
		{
			throw new \RuntimeException('Cannot safely create a key', $ex->getCode(), $ex);
		}
		catch (\CannotPerformOperationException $ex)
		{
			throw new \RuntimeException('Cannot safely create a key', $ex->getCode(), $ex);
		}

		// Explicitly flag the private as unused in this cipher.
		$key->private = 'unused';

		return $key;
	}
}
