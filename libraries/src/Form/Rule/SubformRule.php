<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form rule to validate subforms field-wise.
 *
 * @since  3.9.7
 */
class SubformRule extends FormRule
{
    /**
     * Method to test given values for a subform..
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   Form               $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   3.9.7
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // Get the form field object.
        $field = $form->getField($element['name'], $group);

        if (!($field instanceof SubformField)) {
            throw new \UnexpectedValueException(sprintf('%s is no subform field.', $element['name']));
        }

        if ($value === null) {
            return true;
        }

        $subForm = $field->loadSubForm();

        // Multiple values: Validate every row.
        if ($field->multiple) {
            foreach ($value as $row) {
                if ($subForm->validate($row) === false) {
                    // Pass the first error that occurred on the subform validation.
                    $errors = $subForm->getErrors();

                    if (!empty($errors[0])) {
                        return $errors[0];
                    }

                    return false;
                }
            }
        } else {
            // Single value.
            if ($subForm->validate($value) === false) {
                // Pass the first error that occurred on the subform validation.
                $errors = $subForm->getErrors();

                if (!empty($errors[0])) {
                    return $errors[0];
                }

                return false;
            }
        }

        return true;
    }
}
