<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\User;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

/**
 * Trait for classes which require a user to work with.
 *
 * @since  __DEPLOY_VERSION__
 */
trait CurrentUserTrait
{
	/**
	 * The current user object.
	 *
	 * @var    User
	 * @since  __DEPLOY_VERSION__
	 */
	private $currentUser;

	/**
	 * Returns the current user, if none is set an object with id 0 is returned.
	 *
	 * @return  User
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCurrentUser(): User
	{
		if (!$this->currentUser)
		{
			$this->currentUser = new User;
		}

		return $this->currentUser;
	}

	/**
	 * Sets the current user.
	 *
	 * @param   User  $currentUser  The current user object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCurrentUser(User $currentUser): void
	{
		$this->currentUser = $currentUser;
	}
}
