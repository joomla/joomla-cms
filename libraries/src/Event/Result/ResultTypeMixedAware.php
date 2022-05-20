<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Result;

\defined('JPATH_PLATFORM') or die;

use InvalidArgumentException;

/**
 * This Trait partially implements the ResultAwareInterface for type checking.
 *
 * Events using this Trait (and the ResultAware trait) will expect event handlers to set results
 * of a any type. THIS IS A COP OUT! If you expect a nullable or union type it's best to implement
 * the typeCheckResult method yourself to check for the exact types you expect.
 *
 * @since  __DEPLOY_VERSION__
 */
trait ResultTypeMixedAware
{
	/**
	 * Checks the type of the data being appended to the result argument.
	 *
	 * @param   mixed  $data  The data to type check
	 *
	 * @return  void
	 * @throws  InvalidArgumentException
	 *
	 * @internal
	 * @since   __DEPLOY_VERSION__
	 */
	public function typeCheckResult($data): void
	{
		// Intentionally left blank; no type check is performed.
	}
}
