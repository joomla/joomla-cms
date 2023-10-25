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
use Joomla\CMS\Event\Result\ResultTypeObjectAware;
use Joomla\Component\Users\Administrator\DataShape\CaptiveRenderOptions;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Concrete Event class for the onUserMultifactorCaptive event
 *
 * @since 4.2.0
 */
class Captive extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeObjectAware;

    /**
     * Public constructor
     *
     * @param   MfaTable  $record  The MFA record to use in the captive login page
     *
     * @since   4.2.0
     */
    public function __construct(MfaTable $record)
    {
        parent::__construct('onUserMultifactorCaptive', ['record' => $record]);

        $this->resultIsNullable        = true;
        $this->resultAcceptableClasses = [
            CaptiveRenderOptions::class,
        ];
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
}
