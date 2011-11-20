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
class JCryptCipherRijndael256 implements JCryptCipher
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
		// Generate an initialisation vector.
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB));

		// Decrypt the data.
		$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key->private, $data, MCRYPT_MODE_ECB, $iv));

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
		// Generate an initialisation vector.
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB));

		// Encrypt the data.
		$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key->private, $data, MCRYPT_MODE_ECB, $iv);

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
		$key->type = 'rijndael256';

		return $key;
	}
}
