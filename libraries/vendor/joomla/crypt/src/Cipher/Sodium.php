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
use ParagonIE\Sodium\Compat;

/**
 * Cipher for sodium algorithm encryption, decryption and key generation.
 *
 * @since  __DEPLOY_VERSION__
 */
class Sodium implements CipherInterface
{
	/**
	 * The message nonce to be used with encryption/decryption
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $nonce;

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
		if ($key->getType() !== 'sodium')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected sodium.');
		}

		if (!$this->nonce)
		{
			throw new \RuntimeException('Missing nonce to decrypt data');
		}

		$decrypted = Compat::crypto_box_open(
			$data,
			$this->nonce,
			Compat::crypto_box_keypair_from_secretkey_and_publickey($key->getPrivate(), $key->getPublic())
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
		if ($key->getType() !== 'sodium')
		{
			throw new \InvalidArgumentException('Invalid key of type: ' . $key->getType() . '.  Expected sodium.');
		}

		if (!$this->nonce)
		{
			throw new \RuntimeException('Missing nonce to decrypt data');
		}

		return Compat::crypto_box(
			$data,
			$this->nonce,
			Compat::crypto_box_keypair_from_secretkey_and_publickey($key->getPrivate(), $key->getPublic())
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
	 * @throws  RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		// Generate the encryption key.
		$pair = Compat::crypto_box_keypair();

		return new Key('sodium', Compat::crypto_box_secretkey($pair), Compat::crypto_box_publickey($pair));
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
		return class_exists(Compat::class);
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
}
