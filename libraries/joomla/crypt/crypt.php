<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt is a Joomla Platform class for handling basic encryption/decryption of data.
 *
 * @package     Joomla.Platform
 * @subpackage  Crypt
 * @since       12.1
 */
class JCrypt
{
	/**
	 * @var    JCryptCipher  The encryption cipher object.
	 * @since  12.1
	 */
	private $_cipher;

	/**
	 * @var    JCryptKey  The encryption key[/pair)].
	 * @since  12.1
	 */
	private $_key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   JCryptCipher  $cipher  The encryption cipher object.
	 * @param   JCryptKey     $key     The encryption key[/pair)].
	 *
	 * @since   12.1
	 */
	public function __construct(JCryptCipher $cipher = null, JCryptKey $key = null)
	{
		// Set the encryption key[/pair)].
		$this->_key = $key;

		// Set the encryption cipher.
		$this->_cipher = isset($cipher) ? $cipher : new JCryptCipherSimple;
	}

	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string  $data  The encrypted string to decrypt.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   12.1
	 */
	public function decrypt($data)
	{
		return $this->_cipher->decrypt($data, $this->_key);
	}

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string  $data  The data string to encrypt.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   12.1
	 */
	public function encrypt($data)
	{
		return $this->_cipher->encrypt($data, $this->_key);
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
		return $this->_cipher->generateKey($options);
	}

	/**
	 * Method to set the encryption key[/pair] object.
	 *
	 * @param   JCryptKey  $key  The key object to set.
	 *
	 * @return  JCrypt
	 *
	 * @since   12.1
	 */
	public function setKey(JCryptKey $key)
	{
		$this->_key = $key;

		return $this;
	}
}
