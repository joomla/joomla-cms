<?php
/**
 * Part of the Joomla Framework Crypt Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Crypt\Cipher;

use Joomla\Crypt\CipherInterface;
use Joomla\Crypt\Key;

/**
 * Joomla cipher for encryption, decryption and key generation via the openssl extension.
 *
 * @since  __DEPLOY_VERSION__
 */
class OpenSSL implements CipherInterface
{
	/**
	 * Initialisation vector for key generator method.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $iv;

	/**
	 * Method to use for encryption.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $method;

	/**
	 * Instantiate the cipher.
	 *
	 * @param   string  $iv      The initialisation vector to use
	 * @param   string  $method  The encryption method to use
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $iv, string $method)
	{
		$this->iv     = $iv;
		$this->method = $method;
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
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function decrypt($data, Key $key)
	{
		// Validate key.
		if ($key->getType() !== 'openssl')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected openssl.');
		}

		$cleartext = openssl_decrypt($data, $this->method, $key->getPrivate(), true, $this->iv);

		if ($cleartext === false)
		{
			throw new \RuntimeException('Failed to decrypt data');
		}

		return $cleartext;
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
		if ($key->getType() !== 'openssl')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected openssl.');
		}

		$encrypted = openssl_encrypt($data, $this->method, $key->getPrivate(), true, $this->iv);

		if ($encrypted === false)
		{
			throw new \RuntimeException('Unable to encrypt data');
		}

		return $encrypted;
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
	public function generateKey(array $options = [])
	{
		$passphrase = $options['passphrase'] ?? false;

		if ($passphrase === false)
		{
			throw new \RuntimeException('Missing passphrase file');
		}

		return new Key('openssl', $passphrase, 'unused');
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
		return extension_loaded('openssl');
	}
}
