<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for classes which require a user to work with.
 *
 * @since  4.2.0
 */
trait CurrentUserTrait
{
    /**
     * The current user object.
     *
     * @var    User
     * @since  4.2.0
     */
    private $currentUser;

    /**
     * Returns the current user, if none is set the identity of the global app
     * is returned. This will change in 6.0 and an empty user will be returned.
     *
     * @return  User
     *
     * @since   4.2.0
     */
    protected function getCurrentUser(): User
    {
        if (!$this->currentUser) {
            @trigger_error(
                \sprintf('User must be set in %s. This will not be caught anymore in 6.0', __METHOD__),
                E_USER_DEPRECATED
            );
            $this->currentUser = Factory::getApplication()->getIdentity() ?: Factory::getUser();
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
     * @since   4.2.0
     */
    public function setCurrentUser(User $currentUser): void
    {
        $this->currentUser = $currentUser;
    }
}
