<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\String\StringHelper;

/**
 * String handling class for utf-8 data
 * Wraps the phputf8 library
 * All functions assume the validity of utf-8 strings.
 *
 * @since       11.1
 * @deprecated  4.0  Use {@link \Joomla\String\StringHelper} instead unless otherwise noted.
 */
abstract class JString extends StringHelper
{
	/**
	 * Split a string in camel case format
	 *
	 * "FooBarABCDef"            becomes  array("Foo", "Bar", "ABC", "Def");
	 * "JFooBar"                 becomes  array("J", "Foo", "Bar");
	 * "J001FooBar002"           becomes  array("J001", "Foo", "Bar002");
	 * "abcDef"                  becomes  array("abc", "Def");
	 * "abc_defGhi_Jkl"          becomes  array("abc_def", "Ghi_Jkl");
	 * "ThisIsA_NASAAstronaut"   becomes  array("This", "Is", "A_NASA", "Astronaut")),
	 * "JohnFitzgerald_Kennedy"  becomes  array("John", "Fitzgerald_Kennedy")),
	 *
	 * @param   string  $string  The source string.
	 *
	 * @return  array   The splitted string.
	 *
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use JStringNormalise::fromCamelCase()
	 * @since   11.3
	 */
	public static function splitCamelCase($string)
	{
		JLog::add('JString::splitCamelCase has been deprecated. Use JStringNormalise::fromCamelCase.', JLog::WARNING, 'deprecated');

		return JStringNormalise::fromCamelCase($string, true);
	}

	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string  $url  URL to parse
	 *
	 * @return  mixed  Associative array or false if badly formed URL.
	 *
	 * @link    http://us3.php.net/manual/en/function.parse-url.php
	 * @since   11.1
	 * @deprecated  4.0 (CMS) - Use {@link \Joomla\Uri\UriHelper::parse_url()} instead.
	 */
	public static function parse_url($url)
	{
		JLog::add('JString::parse_url has been deprecated. Use \\Joomla\\Uri\\UriHelper::parse_url.', JLog::WARNING, 'deprecated');

		return \Joomla\Uri\UriHelper::parse_url($url);
	}
}
