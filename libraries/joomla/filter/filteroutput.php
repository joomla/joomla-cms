<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Filter
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JFilterOutput
 *
 * @static
 * @package		Joomla.Platform
 * @subpackage	Filter
 * @since		11.1
 */
class JFilterOutput
{
	/**
	* Makes an object safe to display in forms
	*
	* Object parameters that are non-string, array, object or start with underscore
	* will be converted
	*
	* @param object An object to be parsed
	* @param int The optional quote style for the htmlspecialchars function
	* @param string|array An optional single field name or array of field names not
	*					to be parsed (eg, for a textarea)
	* @since   11.1
	*/
	public static function objectHTMLSafe(&$mixed, $quote_style=ENT_QUOTES, $exclude_keys='')
	{
		if (is_object($mixed))
		{
			foreach (get_object_vars($mixed) as $k => $v)
			{
				if (is_array($v) || is_object($v) || $v == NULL || substr($k, 1, 1) == '_') {
					continue;
				}

				if (is_string($exclude_keys) && $k == $exclude_keys) {
					continue;
				} else if (is_array($exclude_keys) && in_array($k, $exclude_keys)) {
					continue;
				}

				$mixed->$k = htmlspecialchars($v, $quote_style, 'UTF-8');
			}
		}
	}

	/**
	 * This method processes a string and replaces all instances of & with &amp; in links only.
	 *
	 * @static
	 * @param	string	$input	String to process
	 * 
	 * @return	string	Processed string
	 * @since	11.1
	 */
	public static function linkXHTMLSafe($input)
	{
		$regex = 'href="([^"]*(&(amp;){0})[^"]*)*?"';
		return preg_replace_callback("#$regex#i", array('JFilterOutput', '_ampReplaceCallback'), $input);
	}

	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercased.
	 *
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 * @since	11.1
	 */
	public static function stringURLSafe($string)
	{
		//remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		$lang = JFactory::getLanguage();
		$str = $lang->transliterate($str);

		// Convert certain symbols to letter representation
		$str = str_replace(array('&', '"', '<', '>'), array('a', 'q', 'l', 'g'), $str);

		// Lowercase and trim
		$str = trim(strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

		return $str;
	}

	/**
	 * This method implements unicode slugs instead of transliteration.
	 *
	 * @static
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 * @since	11.1
	*/
	public static function stringURLUnicodeSlug($string)
	{
		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace('/\xE3\x80\x80/', ' ', $string);


		// Remove any '-' from the string as they will be used as concatenator.
		// Would be great to let the spaces in but only Firefox is friendly with this

		$str = str_replace('-', ' ', $str);

		// Replace forbidden characters by whitespaces
		$str = preg_replace( '#[:\#\*"@+=;!&\.%()\]\/\'\\\\|\[]#',"\x20", $str );

		// Delete all '?'
		$str = str_replace('?', '', $str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(JString::strtolower($str));

		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$str =preg_replace('#\x20+#','-', $str);
		
		return $str;
	}

	/**
	* Replaces &amp; with & for xhtml compliance
	*
	* @todo There must be a better way???
	*
	* @static
	* @since   11.1
	*/
	public static function ampReplace($text)
	{
		$text = str_replace('&&', '*--*', $text);
		$text = str_replace('&#', '*-*', $text);
		$text = str_replace('&amp;', '&', $text);
		$text = preg_replace('|&(?![\w]+;)|', '&amp;', $text);
		$text = str_replace('*-*', '&#', $text);
		$text = str_replace('*--*', '&&', $text);

		return $text;
	}

	/**
	 * Callback method for replacing & with &amp; in a string
	 *
	 * @param	string	$m	String to process
	 * 
	 * @return	string	Replaced string
	 * @since	11.1
	 */
	public static function _ampReplaceCallback($m)
	{
		$rx = '&(?!amp;)';
		
		return preg_replace('#'.$rx.'#', '&amp;', $m[0]);
	}

	/**
	* Cleans text of all formating and scripting code
	*/
	public static function cleanText (&$text)
	{
		$text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
		$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
		$text = preg_replace('/<!--.+?-->/', '', $text);
		$text = preg_replace('/{.+?}/', '', $text);
		$text = preg_replace('/&nbsp;/', ' ', $text);
		$text = preg_replace('/&amp;/', ' ', $text);
		$text = preg_replace('/&quot;/', ' ', $text);
		$text = strip_tags($text);
		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');

		return $text;
	}

	/**
	 * Strip img-tags from string
	 */
	public static function stripImages($string)
	{
		return  preg_replace('#(<[/]?img.*>)#U', '', $string);
	}
}
