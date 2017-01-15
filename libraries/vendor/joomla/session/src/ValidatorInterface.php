<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

/**
 * Interface for validating a part of the session
 *
 * @since  __DEPLOY_VERSION__
 */
interface ValidatorInterface
{
	/**
	 * Validates the session
	 *
	 * @param   boolean  $restart  Flag if the session should be restarted
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception\InvalidSessionException
	 */
	public function validate($restart = false);
}
