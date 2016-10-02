<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Session\Exception\InvalidSessionException;

/**
 * Interface for validating a part of the session
 *
 * @since  __DEPLOY_VERSION__
 */
interface ValidatorInterface
{
	/**
	 * Validates the session throwing a SessionValidationException if there is an invalid property in the exception
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidSessionException
	 */
	public function validate($restart = false);
}
