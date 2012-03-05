<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform String Normalise Class
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       11.3
 */
abstract class JStringNormalise
{
	/**
	 * Method to convert a string from camel case.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The space separated string.
	 *
	 * @since   12.1
	 */
	public static function fromCamelCase($input)
	{
		return JString::trim(preg_replace('#([A-Z])#', ' $1', $input));
	}

	/**
	 * Method to convert a string into camel case.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The camel case string.
	 *
	 * @since   11.3
	 */
	public static function toCamelCase($input)
	{
		// Convert words to uppercase and then remove spaces.
		$input = self::toSpaceSeparated($input);
		$input = JString::ucwords($input);
		$input = JString::str_ireplace(' ', '', $input);

		return $input;
	}

	/**
	 * Method to convert a string into dash separated form.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The dash separated string.
	 *
	 * @since   11.3
	 */
	public static function toDashSeparated($input)
	{
		// Convert spaces and underscores to dashes.
		$input = JString::str_ireplace(array(' ', '_'), '-', $input);

		// Remove duplicate dashes.
		$input = preg_replace('#-+#', '-', $input);

		return $input;
	}

	/**
	 * Method to convert a string into space separated form.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The space separated string.
	 *
	 * @since   11.3
	 */
	public static function toSpaceSeparated($input)
	{
		// Convert underscores and dashes to spaces.
		$input = JString::str_ireplace(array('_', '-'), ' ', $input);

		// Remove duplicate spaces.
		$input = preg_replace('#\s+#', ' ', $input);

		return $input;
	}

	/**
	 * Method to convert a string into underscore separated form.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The underscore separated string.
	 *
	 * @since   11.3
	 */
	public static function toUnderscoreSeparated($input)
	{
		// Convert spaces and dashes to underscores.
		$input = JString::str_ireplace(array(' ', '-'), '_', $input);

		// Remove duplicate underscores.
		$input = preg_replace('#_+#', '_', $input);

		return $input;
	}

	/**
	 * Method to convert a string into variable form.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The variable string.
	 *
	 * @since   11.3
	 */
	public static function toVariable($input)
	{
		// Remove dashes and underscores, then convert to camel case.
		$input = self::toSpaceSeparated($input);
		$input = self::toCamelCase($input);

		// Remove leading digits.
		$input = preg_replace('#^[0-9]+.*$#', '', $input);

		// Lowercase the first character.
		$first = JString::substr($input, 0, 1);
		$first = JString::strtolower($first);

		// Replace the first character with the lowercase character.
		$input = JString::substr_replace($input, $first, 0, 1);

		return $input;
	}

	/**
	 * Method to convert a string into key form.
	 *
	 * @param   string  $input  The string input.
	 *
	 * @return  string  The key string.
	 *
	 * @since   11.3
	 */
	public static function toKey($input)
	{
		// Remove spaces and dashes, then convert to lower case.
		$input = self::toUnderscoreSeparated($input);
		$input = JString::strtolower($input);

		return $input;
	}
}
