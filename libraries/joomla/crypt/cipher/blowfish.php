<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * JCrypt cipher for Blowfish encryption, decryption and key generation.
 *
 * @package     Joomla.Platform
 * @subpackage  Crypt
 * @since       12.1
 */
class JCryptCipherBlowfish implements JCryptCipher
{
	/**
	 * Constructor.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function __construct()
	{
		if (!is_callable('mcrypt_encrypt'))
		{
			throw new RuntimeException('The mcrypt extension is not available.');
		}
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string     $data  The encrypted string to decrypt.
	 * @param   JCryptKey  $key   The key[/pair] object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   12.1
	 */
	public function decrypt($data, JCryptKey $key)
	{
		// Decrypt the data.
		$decrypted = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key->private, $data, MCRYPT_MODE_CBC, $key->public));

		return $decrypted;
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string     $data  The data string to encrypt.
	 * @param   JCryptKey  $key   The key[/pair] object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   12.1
	 */
	public function encrypt($data, JCryptKey $key)
	{
		// Encrypt the data.
		$encrypted = mcrypt_encrypt(MCRYPT_BLOWFISH, $key->private, $data, MCRYPT_MODE_CBC, $key->public);

		return $encrypted;
	}

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  JCryptKey
	 *
	 * @since   12.1
	 */
	public function generateKey(array $options = array())
	{
		// Create the new encryption key[/pair] object.
		$key = new JCryptKey();

		// 448-bit key (56 bytes) - the only size that mcrypt/php uses for the Blowfish cipher
		// (using a smaller key works just fine, as mcrypt appends \0 to reach proper key-size)
		$key->private = 'SADFo92jzVnzSj39IUYGvi6eL8v6RvJH8Cytuiouh547vCytdyUFl76R';

		// Blowfish/CBC uses an 8-byte IV -- public key.
		$key->public = substr(md5(mt_rand(), true), 0, 8);

		// Get the salt and password setup.
		$salt = (isset($options['salt'])) ? $options['salt'] : substr(pack("h*", md5(mt_rand())), 0, 8);
		$password = (isset($options['password'])) ? $options['password'] : 'J00ml4R0ck$!';

		if (is_callable('mhash_keygen_s2k'))
		{
			$key->private = mhash_keygen_s2k(MHASH_MD5, $password, $salt, 56);
		}
		else
		{
			$key->private = substr(pack("H*", md5($salt . $password)), 0, 56);
		}

		// Set the key type.
		$key->type = 'blowfish';

		return $key;
	}
}
