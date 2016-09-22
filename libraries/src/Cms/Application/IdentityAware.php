<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Application;

/**
 * Trait for application classes which are identity (user) aware
 *
 * @since  4.0
 */
trait IdentityAware
{
	/**
	 * The application identity object.
	 *
	 * @var    \JUser
	 * @since  4.0
	 */
	protected $identity;

	/**
	 * Get the application identity.
	 *
	 * @return  \JUser
	 *
	 * @since   4.0
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * @param   \JUser  $identity  An optional identity object. If omitted, a null user object is created.
	 *
	 * @return  $this
	 *
	 * @since   4.0
	 */
	public function loadIdentity(\JUser $identity = null)
	{
		$this->identity = $identity ?: \JUser::getInstance();

		return $this;
	}
}
