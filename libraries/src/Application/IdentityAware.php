<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\User\User;

/**
 * Trait for application classes which are identity (user) aware
 *
 * @since  4.0.0
 */
trait IdentityAware
{
	/**
	 * The application identity object.
	 *
	 * @var    User
	 * @since  4.0.0
	 */
	protected $identity;

	/**
	 * Get the application identity.
	 *
	 * @return  User
	 *
	 * @since   4.0.0
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * @param   User  $identity  An optional identity object. If omitted, a null user object is created.
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	public function loadIdentity(User $identity = null)
	{
		$this->identity = $identity ?: User::getInstance();

		return $this;
	}
}
