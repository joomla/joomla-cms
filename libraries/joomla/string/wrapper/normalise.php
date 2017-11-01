<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JStringNormalise
 *
 * @package     Joomla.Platform
 * @subpackage  String
 * @since       3.4
 * @deprecated  4.0 Will be removed without replacement
 */
class JStringWrapperNormalise
{
	/**
	 * Helper wrapper method for fromCamelCase
	 *
	 * @param   string   $input    The string input (ASCII only).
	 * @param   boolean  $grouped  Optionally allows splitting on groups of uppercase characters.
	 *
	 * @return mixed  The space separated string or an array of substrings if grouped is true.
	 *
	 * @see     JUserHelper::fromCamelCase()
	 * @since   3.4
	 */
	public function fromCamelCase($input, $grouped = false)
	{
		return JStringNormalise::fromCamelCase($input, $grouped);
	}

	/**
	 * Helper wrapper method for toCamelCase
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The camel case string.
	 *
	 * @see     JUserHelper::toCamelCase()
	 * @since   3.4
	 */
	public function toCamelCase($input)
	{
		return JStringNormalise::toCamelCase($input);
	}

	/**
	 * Helper wrapper method for toDashSeparated
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The dash separated string.
	 *
	 * @see     JUserHelper::toDashSeparated()
	 * @since   3.4
	 */
	public function toDashSeparated($input)
	{
		return JStringNormalise::toDashSeparated($input);
	}

	/**
	 * Helper wrapper method for toSpaceSeparated
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The space separated string.
	 *
	 * @see     JUserHelper::toSpaceSeparated()
	 * @since   3.4
	 */
	public function toSpaceSeparated($input)
	{
		return JStringNormalise::toSpaceSeparated($input);
	}

	/**
	 * Helper wrapper method for toUnderscoreSeparated
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The underscore separated string.
	 *
	 * @see     JUserHelper::toUnderscoreSeparated()
	 * @since   3.4
	 */
	public function toUnderscoreSeparated($input)
	{
		return JStringNormalise::toUnderscoreSeparated($input);
	}

	/**
	 * Helper wrapper method for toVariable
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The variable string.
	 *
	 * @see     JUserHelper::toVariable()
	 * @since   3.4
	 */
	public function toVariable($input)
	{
		return JStringNormalise::toVariable($input);
	}

	/**
	 * Helper wrapper method for toKey
	 *
	 * @param   string  $input  The string input (ASCII only).
	 *
	 * @return string  The key string.
	 *
	 * @see     JUserHelper::toKey()
	 * @since   3.4
	 */
	public function toKey($input)
	{
		return JStringNormalise::toKey($input);
	}
}
