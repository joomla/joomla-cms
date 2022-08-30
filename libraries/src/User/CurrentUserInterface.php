<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a current user.
 *
 * @since  4.2.0
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
     * @since   4.2.0
     */
    public function setCurrentUser(User $currentUser): void;
}
