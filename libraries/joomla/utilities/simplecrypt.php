<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Utilities
 */

defined('JPATH_PLATFORM') or die;

/**
 * JSimpleCrypt is a very simple encryption algorithm for encyrpting/decrypting strings
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JSimpleCrypt extends JObject
{
	/**
	 * Encryption/Decryption Key
	 * @access	private
	 * @var		string
	 */
	var $_key;

	/**
	 * Object Constructor takes an optional key to be used for encryption/decryption.  If no key is given then the
	 * secret word from the configuration object is used.
	 *
	 * @access	protected
	 * @param	string	$key	Optional encryption key
	 * @return	void
	 * @since	1.5
	 */
	function __construct($key = null)
	{
		if ($key) {
			$this->_key = (string) $key;
		} else {
			$conf = &JFactory::getConfig();
			$this->_key = md5($conf->get('secret'));
		}
	}

	function decrypt($s)
	{
		$ai = $this->_hexToIntArray($s);
		(string) $s1 = $this->_xorString($ai);
		return $s1;
	}

	function encrypt($s)
	{
		$ai = $this->_xorCharString($s);
		$s1 = "";
		for ($i = 0; $i < count($ai); $i++)
			$s1 = $s1 . $this->_intToHex((int) $ai[$i]);
		return $s1;
	}

	function _hexToInt($s, $i)
	{
		(int) $j = $i * 2;
		(string) $s1 = $s;
		(string) $c = substr($s1, $j, 1); // get the char at position $j, length 1
		(string) $c1 = substr($s1, $j +1, 1); // get the char at postion $j + 1, length 1
		(int) $k = 0;

		switch ($c)
		{
			case "A" :
				$k += 160;
				break;
			case "B" :
				$k += 176;
				break;
			case "C" :
				$k += 192;
				break;
			case "D" :
				$k += 208;
				break;
			case "E" :
				$k += 224;
				break;
			case "F" :
				$k += 240;
				break;
			case " " :
				$k += 0;
				break;
			default :
				(int) $k = $k + (16 * (int) $c);
				break;
		}

		switch ($c1)
		{
			case "A" :
				$k += 10;
				break;
			case "B" :
				$k += 11;
				break;
			case "C" :
				$k += 12;
				break;
			case "D" :
				$k += 13;
				break;
			case "E" :
				$k += 14;
				break;
			case "F" :
				$k += 15;
				break;
			case " " :
				$k += 0;
				break;
			default :
				$k += (int) $c1;
				break;
		}

		return $k;
	}

	function _hexToIntArray($s)
	{
		(string) $s1 = $s;
		(int) $i = strlen($s1);
		(int) $j = $i / 2;
		for ($l = 0; $l < $j; $l++) {
			(int) $k = $this->_hexToInt($s1, $l);
			$ai[$l] = $k;
		}

		return $ai;
	}

	function _charToInt($c)
	{
		$ac[0] = $c;
		return $ac;
	}

	function _xorString($ai)
	{
		$s = $this->_key; //
		(int) $i = strlen($s);
		$ai1 = $ai;
		(int) $j = count($ai1);
		for ($i = 0; $i < $j; $i = strlen($s))
			$s = $s . $s;

		for ($k = 0; $k < $j; $k++) {
			(string) $c = substr($s, $k, 1);
			$ac[$k] = chr($ai1[$k] ^ ord($c));
		}

		(string) $s1 = implode('', $ac);
		return $s1;
	}

	function _intToHex($i)
	{
		(int) $j = (int) $i / 16;
		if ((int) $j == 0) {
			(string) $s = " ";
		} else {
			(string) $s = strtoupper(dechex($j));
		}
		(int) $k = (int) $i - (int) $j * 16;
		(string) $s = $s . strtoupper(dechex($k));

		return $s;
	}

	function _xorCharString($s)
	{
		$ac = preg_split('//', $s, -1, PREG_SPLIT_NO_EMPTY);
		(string) $s1 = $this->_key;
		(int) $i = strlen($s1);
		(int) $j = count($ac);
		for ($i = 0; $i < $j; $i = strlen($s1)) {
			$s1 = $s1 . $s1;
		}

		for ($k = 0; $k < $j; $k++) {
			$c = substr($s1, $k, 1);
			$ai[$k] = ord($c) ^ ord($ac[$k]);
		}

		return $ai;
	}
}
