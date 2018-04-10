<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

defined('JPATH_PLATFORM') or die;

use Joomla\Authentication\Password\Argon2iHandler as BaseArgon2iHandler;

/**
 * Password handler for Argon2i hashed passwords
 *
 * @since  4.0.0
 */
class Argon2iHandler extends BaseArgon2iHandler implements CheckIfRehashNeededHandlerInterface
{
	/**
	 * Check if the password requires rehashing
	 *
	 * @param   string  $hash  The password hash to check
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function checkIfRehashNeeded(string $hash): bool
	{
		return password_needs_rehash($hash, PASSWORD_ARGON2I);
	}
}
