<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Constraint;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks that a required field has been given a value
 *
 * @since  __DEPLOY_VERSION__
 * @deprecated   7.0  This should not be used directly by extensions and is only provided to allow core to provide
 *                    compatibility across Joomla versions.
 */
class LegacyRuleConstraint extends AbstractConstraint
{
    /**
     * The form rule that was tested
     *
     * @var    FormRule
     * @since  __DEPLOY_VERSION__
     */
    private readonly FormRule $rule;

    /**
     * Method to instantiate the object.
     *
     * @param   bool       $valid  Was the result of the constraint valid or not?
     * @param   FormField  $field  The form field object being inspected
     * @param   FormRule   $rule   The form rule that was run on the field
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(bool $valid, FormField $field, FormRule $rule)
    {
        parent::__construct($valid, $field);

        $this->rule = $rule;
    }

    /**
     * Allows internal access to the form rule object which can be useful for creating appropriate extra information
     * on error messages.
     *
     * @return  FormRule
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getRule(): FormRule
    {
        return $this->rule;
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
            throw new \BadMethodCallException(\sprintf('Field %s is valid', $this->getField()->getAttribute('name', '')));
        }

        // Does the field have a defined error message?
        $message = (string) $this->getField()->getAttribute('message');

        if ($message) {
            $message = Text::_($message);
        } else {
            $message = Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $this->getFieldLabel());
        }

        return $message;
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
        return 'legacy-rule-constraint';
    }
}
