<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Constraint;

use Joomla\CMS\Form\Constraint\ConstraintInterface;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for implementing a ConstraintValidationInterface when the validation response has been pre-determined
 * outside of the class
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractConstraint implements ConstraintInterface
{
    /**
     * Was the constraint met?
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    private readonly bool $valid;

    /**
     * Was the constraint met?
     *
     * @var    FormField
     * @since  __DEPLOY_VERSION__
     */
    private readonly FormField $field;

    /**
     * Method to instantiate the object.
     *
     * @param   bool       $valid  Was the result of the constraint valid or not?
     * @param   FormField  $field  The form field object being inspected
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(bool $valid, FormField $field)
    {
        $this->valid = $valid;
        $this->field = $field;
    }

    /**
     * Was the field data valid or not. If there are no constraints on the field this should return true.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * Allows internal access to the form field object which can be useful when creating specific error messages.
     *
     * @return  FormField
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getField(): FormField
    {
        return $this->field;
    }

    /**
     * Gets the human friendly label of the form field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getFieldLabel(): string
    {
        if ($this->field->getAttribute('label')) {
            $fieldLabel = $this->field->getAttribute('label');

            // Try to translate label if not set to false
            $translate = $this->field->getAttribute('translateLabel', '');

            if (!($translate === 'false' || $translate === 'off' || $translate === '0')) {
                $fieldLabel = Text::_($fieldLabel);
            }
        } else {
            $fieldLabel = Text::_($this->field->getAttribute('name'));
        }

        return $fieldLabel;
    }
}
