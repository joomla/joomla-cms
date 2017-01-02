<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Helper class with utilitarian functions concerning strings
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
abstract class FOFStringUtils
{
	/**
	 * Convert a string into a slug (alias), suitable for use in URLs. Please
	 * note that transliteration suupport is rudimentary at this stage.
	 *
	 * @param   string  $value  A string to convert to slug
	 *
	 * @return  string  The slug
	 */
	public static function toSlug($value)
	{
		// Remove any '-' from the string they will be used as concatonater
		$value = str_replace('-', ' ', $value);

		// Convert to ascii characters
		$value = self::toASCII($value);

		// Lowercase and trim
		$value = trim(strtolower($value));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$value = preg_replace(array('/\s+/', '/[^A-Za-z0-9\-_]/'), array('-', ''), $value);

		// Limit length
		if (strlen($value) > 100)
		{
			$value = substr($value, 0, 100);
		}

		return $value;
	}

	/**
	 * Convert common norhern European languages' letters into plain ASCII. This
	 * is a rudimentary transliteration.
	 *
	 * @param   string  $value  The value to convert to ASCII
	 *
	 * @return  string  The converted string
	 */
	public static function toASCII($value)
	{
		$string = htmlentities(utf8_decode($value), null, 'ISO-8859-1');
		$string = preg_replace(
			array('/&szlig;/', '/&(..)lig;/', '/&([aouAOU])uml;/', '/&(.)[^;]*;/'), array('ss', "$1", "$1" . 'e', "$1"), $string
		);

		return $string;
	}

	/**
	 * Convert a string to a boolean.
	 *
	 * @param   string  $string  The string.
	 *
	 * @return  boolean  The converted string
	 */
	public static function toBool($string)
	{
		$string = trim((string) $string);

		if ($string == 'true')
		{
			return true;
		}

		if ($string == 'false')
		{
			return false;
		}

		return (bool) $string;
	}
}
