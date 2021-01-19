<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Encrypt;

defined('_JEXEC') || die;

use InvalidArgumentException;

/**
 * Base32 encoding class, used by the TOTP
 */
class Base32
{
	/**
	 * CSRFC3548
	 *
	 * The character set as defined by RFC3548
	 * @link http://www.ietf.org/rfc/rfc3548.txt
	 */
	public const CSRFC3548 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

	/**
	 * Convert any string to a base32 string
	 * This should be binary safe...
	 *
	 * @param   string  $str  The string to convert
	 *
	 * @return  string  The converted base32 string
	 */
	public function encode($str)
	{
		return $this->fromBin($this->str2bin($str));
	}

	/**
	 * Convert any base32 string to a normal sctring
	 * This should be binary safe...
	 *
	 * @param   string  $str  The base32 string to convert
	 *
	 * @return  string  The normal string
	 */
	public function decode($str)
	{
		$str = strtoupper($str);

		return $this->bin2str($this->tobin($str));
	}

	/**
	 * Converts any ascii string to a binary string
	 *
	 * @param   string  $str  The string you want to convert
	 *
	 * @return  string  String of 0's and 1's
	 */
	private function str2bin($str)
	{
		$chrs = unpack('C*', $str);

		return vsprintf(str_repeat('%08b', count($chrs)), $chrs);
	}

	/**
	 * Converts a binary string to an ascii string
	 *
	 * @param   string  $str  The string of 0's and 1's you want to convert
	 *
	 * @return  string  The ascii output
	 *
	 * @throws  InvalidArgumentException
	 */
	private function bin2str($str)
	{
		if (strlen($str) % 8 > 0)
		{
			throw new InvalidArgumentException('Length must be divisible by 8');
		}

		if (!preg_match('/^[01]+$/', $str))
		{
			throw new InvalidArgumentException('Only 0\'s and 1\'s are permitted');
		}

		preg_match_all('/.{8}/', $str, $chrs);
		$chrs = array_map('bindec', $chrs[0]);

		// I'm just being slack here
		array_unshift($chrs, 'C*');

		return call_user_func_array('pack', $chrs);
	}

	/**
	 * Converts a correct binary string to base32
	 *
	 * @param   string  $str  The string of 0's and 1's you want to convert
	 *
	 * @return  string  String encoded as base32
	 *
	 * @throws  InvalidArgumentException
	 */
	private function fromBin($str)
	{
		if (strlen($str) % 8 > 0)
		{
			throw new InvalidArgumentException('Length must be divisible by 8');
		}

		if (!preg_match('/^[01]+$/', $str))
		{
			throw new InvalidArgumentException('Only 0\'s and 1\'s are permitted');
		}

		// Base32 works on the first 5 bits of a byte, so we insert blanks to pad it out
		$str = preg_replace('/(.{5})/', '000$1', $str);

		// We need a string divisible by 5
		$length = strlen($str);
		$rbits  = $length & 7;

		if ($rbits > 0)
		{
			// Excessive bits need to be padded
			$ebits = substr($str, $length - $rbits);
			$str   = substr($str, 0, $length - $rbits);
			$str   .= "000$ebits" . str_repeat('0', 5 - strlen($ebits));
		}

		preg_match_all('/.{8}/', $str, $chrs);
		$chrs = array_map([$this, 'mapCharset'], $chrs[0]);

		return implode('', $chrs);
	}

	/**
	 * Accepts a base32 string and returns an ascii binary string
	 *
	 * @param   string  $str  The base32 string to convert
	 *
	 * @return  string  Ascii binary string
	 *
	 * @throws  InvalidArgumentException
	 */
	private function toBin($str)
	{
		if (!preg_match('/^[' . self::CSRFC3548 . ']+$/', $str))
		{
			throw new InvalidArgumentException('Base64 string must match character set');
		}

		// Convert the base32 string back to a binary string
		$str = implode('', array_map([$this, 'mapBin'], str_split($str)));

		// Remove the extra 0's we added
		$str = preg_replace('/000(.{5})/', '$1', $str);

		// Remove padding if necessary
		$length = strlen($str);
		$rbits  = $length & 7;

		if ($rbits > 0)
		{
			$str = substr($str, 0, $length - $rbits);
		}

		return $str;
	}

	/**
	 * Used with array_map to map the bits from a binary string
	 * directly into a base32 character set
	 *
	 * @param   string  $str  The string of 0's and 1's you want to convert
	 *
	 * @return  string  Resulting base32 character
	 *
	 * @access private
	 */
	private function mapCharset($str)
	{
		// Huh!
		$x = self::CSRFC3548;

		return $x[bindec($str)];
	}

	/**
	 * Used with array_map to map the characters from a base32
	 * character set directly into a binary string
	 *
	 * @param   string  $chr  The character to map
	 *
	 * @return  string  String of 0's and 1's
	 *
	 * @access private
	 */
	private function mapBin($chr)
	{
		return sprintf('%08b', strpos(self::CSRFC3548, $chr));
	}

}
