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
 * JCrypt cipher for Triple DES encryption, decryption and key generation.
 *
 * @package     Joomla.Platform
 * @subpackage  Crypt
 * @since       12.1
 */
class JCryptCipher3DES implements JCryptCipher
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
	    $decrypted = mcrypt_decrypt(MCRYPT_3DES, $key->private, $data, MCRYPT_MODE_ECB);

	    // Get the padding values.
	    $block = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	    $pad = ord($decrypted[($len = strlen($decrypted)) - 1]);

	    // Make sure to strip the padding before we return the value.
	    return substr($decrypted, 0, strlen($decrypted) - $pad);
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
		// Add padding for the string to encrypt.
	    $block = mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_ECB);
	    $pad = $block - (strlen($data) % $block);
	    $data .= str_repeat(chr($pad), $pad);

		// Encrypt the data.
	    $encrypted = mcrypt_encrypt(MCRYPT_3DES, $key->private, $data, MCRYPT_MODE_ECB);

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
			$key->private = mhash_keygen_s2k(MHASH_MD5, $password, $salt, 24);
		}
		else
		{
			$key->private = substr(pack("H*", md5($salt . $password)), 0, 24);
		}

		// Set the key type.
		$key->type = '3des';

		return $key;
	}
}
