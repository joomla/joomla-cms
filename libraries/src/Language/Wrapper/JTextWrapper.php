<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language\Wrapper;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JText
 *
 * @since       3.4
 * @deprecated  4.0  Use `JText` directly
 */
class JTextWrapper
{
	/**
	 * Helper wrapper method for _
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation).
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store.
	 *
	 * @return  string  The translated string or the key if $script is true.
	 *
	 * @see     \JText::_
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return \JText::_($string, $jsSafe, $interpretBackSlashes, $script);
	}

	/**
	 * Helper wrapper method for alt
	 *
	 * @param   string   $string                The string to translate.
	 * @param   string   $alt                   The alternate option for global string.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation).
	 * @param   boolean  $script                To indicate that the string will be pushed in the javascript language store.
	 *
	 * @return  string  The translated string or the key if $script is true.
	 *
	 * @see     \JText::alt
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function alt($string, $alt, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		return \JText::alt($string, $alt, $jsSafe, $interpretBackSlashes, $script);
	}

	/**
	 * Helper wrapper method for plural
	 *
	 * @param   string   $string  The format string.
	 * @param   integer  $n       The number of items.
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options.
	 *
	 * @see     \JText::plural
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function plural($string, $n)
	{
		return \JText::plural($string, $n);
	}

	/**
	 * Helper wrapper method for sprintf
	 *
	 * @param   string  $string  The format string.
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options.
	 *
	 * @see     \JText::sprintf
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function sprintf($string)
	{
		return \JText::sprintf($string);
	}

	/**
	 * Helper wrapper method for printf
	 *
	 * @param   string  $string  The format string.
	 *
	 * @return  mixed
	 *
	 * @see     \JText::printf
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function printf($string)
	{
		return \JText::printf($string);
	}

	/**
	 * Helper wrapper method for script
	 *
	 * @param   string   $string                The \JText key.
	 * @param   boolean  $jsSafe                Ensure the output is JavaScript safe.
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n.
	 *
	 * @return  string
	 *
	 * @see     \JText::script
	 * @since   3.4
	 * @deprecated  4.0  Use `JText` directly
	 */
	public function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
	{
		return \JText::script($string, $jsSafe, $interpretBackSlashes);
	}
}
