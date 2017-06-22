<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Crypt;

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt cipher interface.
 *
 * @since  12.1
 */
interface CryptCipher
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string    $data  The encrypted string to decrypt.
	 * @param   CryptKey  $key   The key[/pair] object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   12.1
	 */
	public function decrypt($data, CryptKey $key);

	/**
	 * Method to encrypt a data string.
	 *
	 * @param   string    $data  The data string to encrypt.
	 * @param   CryptKey  $key   The key[/pair] object to use for encryption.
	 *
	 * @return  string  The encrypted data string.
	 *
	 * @since   12.1
	 */
	public function encrypt($data, CryptKey $key);

	/**
	 * Method to generate a new encryption key[/pair] object.
	 *
	 * @param   array  $options  Key generation options.
	 *
	 * @return  CryptKey
	 *
	 * @since   12.1
	 */
	public function generateKey(array $options = array());
}
