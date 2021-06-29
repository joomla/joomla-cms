<?php
/**
 * Part of the Joomla Framework Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Filesystem\Support;

/**
 * String Controller
 *
 * @since  1.0
 */
class StringController
{
	/**
	 * Internal string references
	 *
	 * @var     array
	 * @ssince  1.4.0
	 */
	private static $strings = array();

	/**
	 * Defines a variable as an array
	 *
	 * @return  array
	 *
	 * @since   1.0
	 * @deprecated  2.0  Use `getArray` instead.
	 */
	public static function _getArray()
	{
		return self::getArray();
	}

	/**
	 * Defines a variable as an array
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	public static function getArray()
	{
		return self::$strings;
	}

	/**
	 * Create a reference
	 *
	 * @param   string  $reference  The key
	 * @param   string  $string     The value
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function createRef($reference, &$string)
	{
		self::$strings[$reference] = & $string;
	}

	/**
	 * Get reference
	 *
	 * @param   string  $reference  The key for the reference.
	 *
	 * @return  mixed  False if not set, reference if it exists
	 *
	 * @since   1.0
	 */
	public static function getRef($reference)
	{
		if (isset(self::$strings[$reference]))
		{
			return self::$strings[$reference];
		}

		return false;
	}
}
