<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Simplecrypt
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JSimpleCrypt is a very simple encryption algorithm for encrypting/decrypting strings
 *
 * @since       11.1
 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use JCrypt instead.
 */
class JSimplecrypt
{
	/**
	 * Encryption/Decryption Key
	 *
	 * @var         JCrypt
	 * @since       12.1
	 * @deprecated  12.3  Use JCrypt instead.
	 */
	private $_crypt;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption. If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @param   string  $privateKey  Optional encryption key
	 *
	 * @since       11.1
	 * @deprecated  12.3  Use JCrypt instead.
	 */
	public function __construct($privateKey = null)
	{
		JLog::add('JSimpleCrypt is deprecated. Use JCrypt instead.', JLog::WARNING, 'deprecated');

		if (empty($privateKey))
		{
			$privateKey = md5(JFactory::getConfig()->get('secret'));
		}

		// Build the JCryptKey object.
		$key = new JCryptKey('simple', $privateKey, $privateKey);

		// Setup the JCrypt object.
		$this->_crypt = new JCrypt(new JCryptCipherSimple, $key);
	}

	/**
	 * Decrypt a string
	 *
	 * @param   string  $s  String to decrypt
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @deprecated  12.3  Use JCrypt instead.
	 */
	public function decrypt($s)
	{
		return $this->_crypt->decrypt($s);
	}

	/**
	 * Encrypt a string
	 *
	 * @param   string  $s  String to encrypt
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @deprecated  12.3  Use JCrypt instead.
	 */
	public function encrypt($s)
	{
		return $this->_crypt->encrypt($s);
	}
}
