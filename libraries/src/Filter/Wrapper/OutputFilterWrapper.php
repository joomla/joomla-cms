<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filter\Wrapper;

defined('JPATH_PLATFORM') or die;

use Joomla\Filter\OutputFilter;

/**
 * Wrapper class for OutputFilter
 *
 * @since       3.4
 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
 */
class OutputFilterWrapper
{
	/**
	 * Helper wrapper method for objectHTMLSafe
	 *
	 * @param   object   &$mixed        An object to be parsed.
	 * @param   integer  $quote_style   The optional quote style for the htmlspecialchars function.
	 * @param   mixed    $exclude_keys  An optional string single field name or array of field names not.
	 *
	 * @return  void
	 *
	 * @see     OutputFilter::objectHTMLSafe()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function objectHTMLSafe(&$mixed, $quote_style = 3, $exclude_keys = '')
	{
		return OutputFilter::objectHTMLSafe($mixed, $quote_style, $exclude_keys);
	}

	/**
	 * Helper wrapper method for linkXHTMLSafe
	 *
	 * @param   string  $input  String to process.
	 *
	 * @return  string  Processed string.
	 *
	 * @see     OutputFilter::linkXHTMLSafe()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function linkXHTMLSafe($input)
	{
		return OutputFilter::linkXHTMLSafe($input);
	}

	/**
	 * Helper wrapper method for stringURLSafe
	 *
	 * @param   string  $string  String to process.
	 *
	 * @return  string  Processed string.
	 *
	 * @see     OutputFilter::stringURLSafe()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function stringURLSafe($string)
	{
		return OutputFilter::stringURLSafe($string);
	}

	/**
	 * Helper wrapper method for stringURLUnicodeSlug
	 *
	 * @param   string  $string  String to process.
	 *
	 * @return  string  Processed string.
	 *
	 * @see     OutputFilter::stringURLUnicodeSlug()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function stringURLUnicodeSlug($string)
	{
		return OutputFilter::stringURLUnicodeSlug($string);
	}

	/**
	 * Helper wrapper method for ampReplace
	 *
	 * @param   string  $text  Text to process.
	 *
	 * @return  string  Processed string.
	 *
	 * @see     OutputFilter::ampReplace()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function ampReplace($text)
	{
		return OutputFilter::ampReplace($text);
	}

	/**
	 * Helper wrapper method for _ampReplaceCallback
	 *
	 * @param   string  $m  String to process.
	 *
	 * @return  string  Replaced string.
	 *
	 * @see     OutputFilter::_ampReplaceCallback()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function _ampReplaceCallback($m)
	{
		return OutputFilter::_ampReplaceCallback($m);
	}

	/**
	 * Helper wrapper method for cleanText
	 *
	 * @param   string  &$text  Text to clean.
	 *
	 * @return  string  Cleaned text.
	 *
	 * @see     OutputFilter::cleanText()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function cleanText(&$text)
	{
		return OutputFilter::cleanText($text);
	}

	/**
	 * Helper wrapper method for stripImages
	 *
	 * @param   string  $string  Sting to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @see     OutputFilter::stripImages()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function stripImages($string)
	{
		return OutputFilter::stripImages($string);
	}

	/**
	 * Helper wrapper method for stripIframes
	 *
	 * @param   string  $string  Sting to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @see     OutputFilter::stripIframes()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Filter\OutputFilter` directly
	 */
	public function stripIframes($string)
	{
		return OutputFilter::stripIframes($string);
	}
}
