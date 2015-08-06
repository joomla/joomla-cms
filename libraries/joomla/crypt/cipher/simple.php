<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Crypt
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JCrypt cipher for Simple encryption, decryption and key generation.
 *
 * @since       12.1
 * @deprecated  4.0 (CMS)
 */
class JCryptCipherSimple implements JCryptCipher
{
	/**
	 * Method to decrypt a data string.
	 *
	 * @param   string     $data  The encrypted string to decrypt.
	 * @param   JCryptKey  $key   The key[/pair] object to use for decryption.
	 *
	 * @return  string  The decrypted data string.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	public function decrypt($data, JCryptKey $key)
	{
		// Validate key.
		if ($key->type != 'simple')
		{
			throw new InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected simple.');
		}

		$decrypted = '';
		$tmp = $key->public;

		// Convert the HEX input into an array of integers and get the number of characters.
		$chars = $this->_hexToIntArray($data);
		$charCount = count($chars);

		// Repeat the key as many times as necessary to ensure that the key is at least as long as the input.
		for ($i = 0; $i < $charCount; $i = strlen($tmp))
		{
			$tmp = $tmp . $tmp;
		}

		// Get the XOR values between the ASCII values of the input and key characters for all input offsets.
		for ($i = 0; $i < $charCount; $i++)
		{
			$decrypted .= chr($chars[$i] ^ ord($tmp[$i]));
		}

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
	 * @throws  InvalidArgumentException
	 */
	public function encrypt($data, JCryptKey $key)
	{
		// Validate key.
		if ($key->type != 'simple')
		{
			throw new InvalidArgumentException('Invalid key of type: ' . $key->type . '.  Expected simple.');
		}

		$encrypted = '';
		$tmp = $key->private;

		// Split up the input into a character array and get the number of characters.
		$chars = preg_split('//', $data, -1, PREG_SPLIT_NO_EMPTY);
		$charCount = count($chars);

		// Repeat the key as many times as necessary to ensure that the key is at least as long as the input.
		for ($i = 0; $i < $charCount; $i = strlen($tmp))
		{
			$tmp = $tmp . $tmp;
		}

		// Get the XOR values between the ASCII values of the input and key characters for all input offsets.
		for ($i = 0; $i < $charCount; $i++)
		{
			$encrypted .= $this->_intToHex(ord($tmp[$i]) ^ ord($chars[$i]));
		}

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
		$key = new JCryptKey('simple');

		// Just a random key of a given length.
		$key->private = JCrypt::genRandomBytes(256);
		$key->public  = $key->private;

		return $key;
	}

	/**
	 * Convert hex to an integer
	 *
	 * @param   string   $s  The hex string to convert.
	 * @param   integer  $i  The offset?
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	private function _hexToInt($s, $i)
	{
		$j = (int) $i * 2;
		$k = 0;
		$s1 = (string) $s;

		// Get the character at position $j.
		$c = substr($s1, $j, 1);

		// Get the character at position $j + 1.
		$c1 = substr($s1, $j + 1, 1);

		switch ($c)
		{
			case 'A':
				$k += 160;
				break;
			case 'B':
				$k += 176;
				break;
			case 'C':
				$k += 192;
				break;
			case 'D':
				$k += 208;
				break;
			case 'E':
				$k += 224;
				break;
			case 'F':
				$k += 240;
				break;
			case ' ':
				$k += 0;
				break;
			default:
				(int) $k = $k + (16 * (int) $c);
				break;
		}

		switch ($c1)
		{
			case 'A':
				$k += 10;
				break;
			case 'B':
				$k += 11;
				break;
			case 'C':
				$k += 12;
				break;
			case 'D':
				$k += 13;
				break;
			case 'E':
				$k += 14;
				break;
			case 'F':
				$k += 15;
				break;
			case ' ':
				$k += 0;
				break;
			default:
				$k += (int) $c1;
				break;
		}

		return $k;
	}

	/**
	 * Convert hex to an array of integers
	 *
	 * @param   string  $hex  The hex string to convert to an integer array.
	 *
	 * @return  array  An array of integers.
	 *
	 * @since   11.1
	 */
	private function _hexToIntArray($hex)
	{
		$array = array();

		$j = (int) strlen($hex) / 2;

		for ($i = 0; $i < $j; $i++)
		{
			$array[$i] = (int) $this->_hexToInt($hex, $i);
		}

		return $array;
	}

	/**
	 * Convert an integer to a hexadecimal string.
	 *
	 * @param   integer  $i  An integer value to convert to a hex string.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	private function _intToHex($i)
	{
		// Sanitize the input.
		$i = (int) $i;

		// Get the first character of the hexadecimal string if there is one.
		$j = (int) ($i / 16);

		if ($j === 0)
		{
			$s = ' ';
		}
		else
		{
			$s = strtoupper(dechex($j));
		}

		// Get the second character of the hexadecimal string.
		$k = $i - $j * 16;
		$s = $s . strtoupper(dechex($k));

		return $s;
	}
}
