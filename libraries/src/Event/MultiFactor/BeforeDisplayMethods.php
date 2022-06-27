<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\MultiFactor;

use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\User\User;

/**
 * Concrete Event class for the onUserMultifactorBeforeDisplayMethods event
 *
 * @since 4.2.0
 */
class BeforeDisplayMethods extends AbstractImmutableEvent
{
    use ResultAware;

    /**
     * Public constructor
     *
     * @param   User  $user  The user the MFA methods are displayed for
     *
     * @since   4.2.0
     */
    public function __construct(User $user)
    {
        parent::__construct('onUserMultifactorBeforeDisplayMethods', ['user' => $user]);
    }

    /**
     * Validate the value of the 'user' named parameter
     *
     * @param   User  $value  The value to validate
     *
     * @return  User
     * @since   4.2.0
     */
    public function setUser(User $value): User
    {
        if (empty($value) || ($value->id <= 0) || ($value->guest == 1)) {
            throw new DomainException(sprintf('Argument \'user\' of event %s must be a non-guest User object.', $this->name));
        }

        return $value;
    }
}
