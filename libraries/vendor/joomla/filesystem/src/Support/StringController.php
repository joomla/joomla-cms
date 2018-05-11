<?php
/**
 * Part of the Joomla Framework Filesystem Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * Defines a variable as an array
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function _getArray()
	{
		static $strings = array();

		return $strings;
	}

	/**
	 * Create a reference
	 *
	 * @param   string  $reference  The key
	 * @param   string  &$string    The value
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createRef($reference, &$string)
	{
		$ref = &self::_getArray();
		$ref[$reference] = & $string;
	}

	/**
	 * Get reference
	 *
	 * @param   string  $reference  The key for the reference.
	 *
	 * @return  mixed  False if not set, reference if it it exists
	 *
	 * @since   1.0
	 */
	public function getRef($reference)
	{
		$ref = &self::_getArray();

		if (isset($ref[$reference]))
		{
			return $ref[$reference];
		}
		else
		{
			return false;
		}
	}
}
