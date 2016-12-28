<?php
/**
 * Part of the Joomla Framework Data Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Data;

/**
 * An interface to define if an object is dumpable.
 *
 * @since  1.0
 */
interface DumpableInterface
{
	/**
	 * Dumps the object properties into a stdClass object, recursively if appropriate.
	 *
	 * @param   integer            $depth   The maximum depth of recursion.
	 *                                      For example, a depth of 0 will return a stdClass with all the properties in native
	 *                                      form. A depth of 1 will recurse into the first level of properties only.
	 * @param   \SplObjectStorage  $dumped  An array of already serialized objects that is used to avoid infinite loops.
	 *
	 * @return  \stdClass  The data properties as a simple PHP stdClass object.
	 *
	 * @since   1.0
	 */
	public function dump($depth = 3, \SplObjectStorage $dumped = null);
}
