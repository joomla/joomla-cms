<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Constraint;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks that a required field has been given a value
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldLengthConstraint extends AbstractConstraint
{
    /**
     * Method to instantiate the object.
     *
     * @param   FormField  $field      The form field object being inspected
     * @param   string     $value      Was the result of the constraint valid or not?
     * @param   int        $maxLength  Was the result of the constraint valid or not?
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(FormField $field, string $value, int $maxLength)
    {
        $valid = ($maxLength === 0) || (\strlen($value) <= $maxLength);

        parent::__construct($valid, $field);
    }

    /**
     * Get an error message that can be displayed to a user about how to remediate the constraint.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \BadMethodCallException  When the constraint is valid
     */
    public function getErrorMessage(): string
    {
        if ($this->isValid()) {
            throw new \BadMethodCallException(sprintf('Field %s is valid', $this->getField()->getAttribute('name', '')));
        }

        return Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $this->getFieldLabel());
    }

    /**
     * Name of the constraint - note this is for machine access and should be unique for a form field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return 'field-required';
    }
}
