<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

\defined('_JEXEC') or die;

/**
 * Interface to be implemented by classes depending on a current user.
 *
 * @since  __DEPLOY_VERSION__
 */
interface CurrentUserInterface
{
	/**
	 * Sets the current user.
	 *
	 * @param   User  $currentUser  The current user object
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCurrentUser(User $currentUser): void;
}
