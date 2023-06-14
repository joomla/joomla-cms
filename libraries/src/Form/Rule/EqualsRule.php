<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.7.0
 */
class EqualsRule extends FormRule
{
    /**
     * Method to test if two values are equal. To use this rule, the form
     * XML needs a validate attribute of equals and a field attribute
     * that is equal to the field to test against.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   Registry           $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   Form               $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   1.7.0
     * @throws  \InvalidArgumentException
     * @throws  \UnexpectedValueException
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        $field = (string) $element['field'];

        // Check that a validation field is set.
        if (!$field) {
            throw new \UnexpectedValueException(sprintf('$field empty in %s::test', \get_class($this)));
        }

        if (\is_null($form)) {
            throw new \InvalidArgumentException(sprintf('The value for $form must not be null in %s', \get_class($this)));
        }

        if (\is_null($input)) {
            throw new \InvalidArgumentException(sprintf('The value for $input must not be null in %s', \get_class($this)));
        }

        $test = $input->get($field);

        if (isset($group) && $group !== '') {
            $test = $input->get($group . '.' . $field);
        }

        // Test the two values against each other.
        return $value == $test;
    }
}
