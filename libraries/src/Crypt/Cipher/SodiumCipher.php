<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt\Cipher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Crypt\CipherInterface;
use Joomla\CMS\Crypt\Key;
use ParagonIE\Sodium\Compat;

/**
 * JCrypt cipher for sodium algorithm encryption, decryption and key generation.
 *
 * @since  3.8.0
 */
class SodiumCipher implements CipherInterface
{
	/**
	 * The message nonce to be used with encryption/decryption
	 *
	 * @var    string
	 * @since  3.8.0
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
	 * @since   3.8.0
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

		$decrypted = Compat::crypto_box_open(
			$data,
			$this->nonce,
			Compat::crypto_box_keypair_from_secretkey_and_publickey($key->private, $key->public)
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
	 * @since   3.8.0
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

		return Compat::crypto_box(
			$data,
			$this->nonce,
			Compat::crypto_box_keypair_from_secretkey_and_publickey($key->private, $key->public)
		);
	}

	/**
	 * Method to generate a new encryption key object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  Key
	 *
	 * @since   3.8.0
	 * @throws  RuntimeException
	 */
	public function generateKey(array $options = array())
	{
		// Create the new encryption key object.
		$key = new Key('sodium');

		// Generate the encryption key.
		$pair = Compat::crypto_box_keypair();

		$key->public  = Compat::crypto_box_publickey($pair);
		$key->private = Compat::crypto_box_secretkey($pair);

		return $key;
	}

	/**
	 * Set the nonce to use for encrypting/decrypting messages
	 *
	 * @param   string  $nonce  The message nonce
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function setNonce($nonce)
	{
		$this->nonce = $nonce;
	}
}
