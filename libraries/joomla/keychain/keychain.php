<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Keychain
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Keychain Class
 *
 * @since       3.1.4
 * @deprecated  4.0  Deprecated without replacement
 */
class JKeychain extends \Joomla\Registry\Registry
{
	/**
	 * @var    string  Method to use for encryption.
	 * @since  3.1.4
	 */
	public $method = 'aes-128-cbc';

	/**
	 * @var    string  Initialisation vector for encryption method.
	 * @since  3.1.4
	 */
	public $iv = '1234567890123456';

	/**
	 * Create a passphrase file
	 *
	 * @param   string  $passphrase            The passphrase to store in the passphrase file.
	 * @param   string  $passphraseFile        Path to the passphrase file to create.
	 * @param   string  $privateKeyFile        Path to the private key file to encrypt the passphrase file.
	 * @param   string  $privateKeyPassphrase  The passphrase for the private key.
	 *
	 * @return  boolean  Result of writing the passphrase file to disk.
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function createPassphraseFile($passphrase, $passphraseFile, $privateKeyFile, $privateKeyPassphrase)
	{
		$privateKey = openssl_get_privatekey(file_get_contents($privateKeyFile), $privateKeyPassphrase);

		if (!$privateKey)
		{
			throw new RuntimeException('Failed to load private key.');
		}

		$crypted = '';

		if (!openssl_private_encrypt($passphrase, $crypted, $privateKey))
		{
			throw new RuntimeException('Failed to encrypt data using private key.');
		}

		return file_put_contents($passphraseFile, $crypted);
	}

	/**
	 * Delete a registry value (very simple method)
	 *
	 * @param   string  $path  Registry Path (e.g. joomla.content.showauthor)
	 *
	 * @return  mixed  Value of old value or boolean false if operation failed
	 *
	 * @since   3.1.4
	 */
	public function deleteValue($path)
	{
		$result = null;

		// Explode the registry path into an array
		$nodes = explode('.', $path);

		if ($nodes)
		{
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0, $n = count($nodes) - 1; $i < $n; $i++)
			{
				if (!isset($node->{$nodes[$i]}) && ($i != $n))
				{
					$node->{$nodes[$i]} = new stdClass;
				}

				$node = $node->{$nodes[$i]};
			}

			// Get the old value if exists so we can return it
			$result = $node->{$nodes[$i]};
			unset($node->{$nodes[$i]});
		}

		return $result;
	}

	/**
	 * Load a keychain file into this object.
	 *
	 * @param   string  $keychainFile    Path to the keychain file.
	 * @param   string  $passphraseFile  The path to the passphrase file to decript the keychain.
	 * @param   string  $publicKeyFile   The file containing the public key to decrypt the passphrase file.
	 *
	 * @return  boolean  Result of loading the object.
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function loadKeychain($keychainFile, $passphraseFile, $publicKeyFile)
	{
		if (!file_exists($keychainFile))
		{
			throw new RuntimeException('Attempting to load non-existent keychain file');
		}

		$passphrase = $this->getPassphraseFromFile($passphraseFile, $publicKeyFile);

		$cleartext = openssl_decrypt(file_get_contents($keychainFile), $this->method, $passphrase, true, $this->iv);

		if ($cleartext === false)
		{
			throw new RuntimeException('Failed to decrypt keychain file');
		}

		return $this->loadObject(json_decode($cleartext));
	}

	/**
	 * Save this keychain to a file.
	 *
	 * @param   string  $keychainFile    The path to the keychain file.
	 * @param   string  $passphraseFile  The path to the passphrase file to encrypt the keychain.
	 * @param   string  $publicKeyFile   The file containing the public key to decrypt the passphrase file.
	 *
	 * @return  boolean  Result of storing the file.
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function saveKeychain($keychainFile, $passphraseFile, $publicKeyFile)
	{
		$passphrase = $this->getPassphraseFromFile($passphraseFile, $publicKeyFile);
		$data = $this->toString('JSON');

		$encrypted = @openssl_encrypt($data, $this->method, $passphrase, true, $this->iv);

		if ($encrypted === false)
		{
			throw new RuntimeException('Unable to encrypt keychain');
		}

		return file_put_contents($keychainFile, $encrypted);
	}

	/**
	 * Get the passphrase for this keychain
	 *
	 * @param   string  $passphraseFile  The file containing the passphrase to encrypt and decrypt.
	 * @param   string  $publicKeyFile   The file containing the public key to decrypt the passphrase file.
	 *
	 * @return  string  The passphrase in from passphraseFile
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	protected function getPassphraseFromFile($passphraseFile, $publicKeyFile)
	{
		if (!file_exists($publicKeyFile))
		{
			throw new RuntimeException('Missing public key file');
		}

		$publicKey = openssl_get_publickey(file_get_contents($publicKeyFile));

		if (!$publicKey)
		{
			throw new RuntimeException('Failed to load public key.');
		}

		if (!file_exists($passphraseFile))
		{
			throw new RuntimeException('Missing passphrase file');
		}

		$passphrase = '';

		if (!openssl_public_decrypt(file_get_contents($passphraseFile), $passphrase, $publicKey))
		{
			throw new RuntimeException('Failed to decrypt passphrase file');
		}

		return $passphrase;
	}
}
