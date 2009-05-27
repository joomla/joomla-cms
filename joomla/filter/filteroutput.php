<?php
/**
 * @version		$Id:output.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Filter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/**
 * JFilterOutput
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Filter
 * @since		1.5
 */
class JFilterOutput
{
	/**
	* Makes an object safe to display in forms
	*
	* Object parameters that are non-string, array, object or start with underscore
	* will be converted
	*
	* @static
	* @param object An object to be parsed
	* @param int The optional quote style for the htmlspecialchars function
	* @param string|array An optional single field name or array of field names not
	*					 to be parsed (eg, for a textarea)
	* @since 1.5
	*/
	function objectHTMLSafe(&$mixed, $quote_style=ENT_QUOTES, $exclude_keys='')
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
	 * This method processes a string and replaces all instances of & with &amp; in links only
	 *
	 * @static
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 * @since	1.5
	 */
	function linkXHTMLSafe($input)
	{
		$regex = 'href="([^"]*(&(amp;){0})[^"]*)*?"';
		return preg_replace_callback("#$regex#i", array('JFilterOutput', '_ampReplaceCallback'), $input);
	}

	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercased.
	 *
	 * @static
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 * @since	1.5
	 */
	function stringURLSafe($string)
	{
		//remove any '-' from the string they will be used as concatonater
		$str = str_replace('-', ' ', $string);

		$lang = &JFactory::getLanguage();
		$str = $lang->transliterate($str);

		// remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

		// lowercase and trim
		$str = trim(strtolower($str));
		return $str;
	}

	/**
	* Replaces &amp; with & for xhtml compliance
	*
	* @todo There must be a better way???
	*
	* @static
	* @since 1.5
	*/
	function ampReplace($text)
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
	 * @static
	 * @param	string	$m	String to process
	 * @return	string	Replaced string
	 * @since	1.5
	 */
	function _ampReplaceCallback($m)
	{
		 $rx = '&(?!amp;)';
		 return preg_replace('#'.$rx.'#', '&amp;', $m[0]);
	}

	/**
	* Cleans text of all formating and scripting code
	*/
	function cleanText (&$text)
	{
		$text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
		$text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
		$text = preg_replace('/<!--.+?-->/', '', $text);
		$text = preg_replace('/{.+?}/', '', $text);
		$text = preg_replace('/&nbsp;/', ' ', $text);
		$text = preg_replace('/&amp;/', ' ', $text);
		$text = preg_replace('/&quot;/', ' ', $text);
		$text = strip_tags($text);
		$text = htmlspecialchars($text);
		return $text;
	}
}
