<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\MultiFactor;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeBooleanAware;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete Event class for the onUserMultifactorValidate event
 *
 * @since 4.2.0
 */
class Validate extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeBooleanAware;

    /**
     * Public constructor
     *
     * @param   MfaTable  $record  The MFA record to validate against
     * @param   User      $user    The user currently logged into the site
     * @param   string    $code    The MFA code we are validating
     *
     * @since   4.2.0
     */
    public function __construct(MfaTable $record, User $user, string $code)
    {
        parent::__construct(
            'onUserMultifactorValidate',
            [
                'record' => $record,
                'user'   => $user,
                'code'   => $code,
            ]
        );
    }

    /**
     * Validate the value of the 'record' named parameter
     *
     * @param   MfaTable  $value  The value to validate
     *
     * @return  MfaTable
     * @since   4.2.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    public function setRecord(MfaTable $value): MfaTable
    {
        if (empty($value)) {
            throw new \DomainException(sprintf('Argument \'record\' of event %s must be a MfaTable object.', $this->name));
        }

        return $value;
    }

    /**
     * Validate the value of the 'user' named parameter
     *
     * @param   User  $value  The value to validate
     *
     * @return  User
     * @since   4.2.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    public function setUser(User $value): User
    {
        if (empty($value) || ($value->id <= 0) || ($value->guest == 1)) {
            throw new \DomainException(sprintf('Argument \'user\' of event %s must be a non-guest User object.', $this->name));
        }

        return $value;
    }

    /**
     * Validate the value of the 'code' named parameter
     *
     * @param   string|null  $value  The value to validate
     *
     * @return  string|null
     * @since   4.2.0
     *
     * @deprecated 4.4.0 will be removed in 6.0
     *                Use counterpart with onSet prefix
     */
    public function setCode(?string $value): ?string
    {
        // No validation necessary, the type check in the method options is enough
        return $value;
    }

    /**
     * Validate the value of the 'record' named parameter
     *
     * @param   MfaTable  $value  The value to validate
     *
     * @return  MfaTable
     * @since   4.4.0
     */
    protected function onSetRecord(MfaTable $value): MfaTable
    {
        return $this->setRecord($value);
    }

    /**
     * Validate the value of the 'user' named parameter
     *
     * @param   User  $value  The value to validate
     *
     * @return  User
     * @since   4.4.0
     */
    protected function onSetUser(User $value): User
    {
        return $this->setUser($value);
    }

    /**
     * Validate the value of the 'code' named parameter
     *
     * @param   string|null  $value  The value to validate
     *
     * @return  string|null
     * @since   4.4.0
     */
    protected function onSetCode(?string $value): ?string
    {
        return $this->setCode($value);
    }
}
