<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filter;

defined('JPATH_PLATFORM') or die;

use Joomla\Filter\OutputFilter as BaseOutputFilter;
use Joomla\String\StringHelper;
use Joomla\CMS\Language\Language;

/**
 * OutputFilter
 *
 * @since  11.1
 */
class OutputFilter extends BaseOutputFilter
{
	/**
	 * This method processes a string and replaces all instances of & with &amp; in links only.
	 *
	 * @param   string  $input  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 */
	public static function linkXHTMLSafe($input)
	{
		$regex = 'href="([^"]*(&(amp;){0})[^"]*)*?"';

		return preg_replace_callback("#$regex#i", array('\\Joomla\\CMS\\Filter\\OutputFilter', '_ampReplaceCallback'), $input);
	}

	/**
	 * This method processes a string and escapes it for use in JavaScript
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed text
	 */
	public static function stringJSSafe($string)
	{
		for ($i = 0, $l = strlen($string), $new_str = ''; $i < $l; $i++)
		{
			$new_str .= (ord(substr($string, $i, 1)) < 16 ? '\\x0' : '\\x') . dechex(ord(substr($string, $i, 1)));
		}

		return $new_str;
	}

	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
	 *
	 * @param   string  $string    String to process
	 * @param   string  $language  Language to transilterate to
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 */
	public static function stringURLSafe($string, $language = '')
	{
		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		// Transliterate on the language requested (fallback to current language if not specified)
		$lang = $language == '' || $language == '*' ? \JFactory::getLanguage() : Language::getInstance($language);
		$str = $lang->transliterate($str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(StringHelper::strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}

	/**
	 * Callback method for replacing & with &amp; in a string
	 *
	 * @param   string  $m  String to process
	 *
	 * @return  string  Replaced string
	 *
	 * @since   3.5
	 */
	public static function ampReplaceCallback($m)
	{
		$rx = '&(?!amp;)';

		return preg_replace('#' . $rx . '#', '&amp;', $m[0]);
	}

	/**
	 * Callback method for replacing & with &amp; in a string
	 *
	 * @param   string  $m  String to process
	 *
	 * @return  string  Replaced string
	 *
	 * @since       11.1
	 * @deprecated  4.0 Use OutputFilter::ampReplaceCallback() instead
	 */
	public static function _ampReplaceCallback($m)
	{
		return static::ampReplaceCallback($m);
	}
}
