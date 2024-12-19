<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  5.0.0
 */

class ShowOnRule extends FormRule
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    string
     * @since  5.0.0
     */
    protected $regex = '^[A-Za-z0-9-]+((:.+)|(!:.*))$';

    /**
     * Method to test the value.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @throws  \UnexpectedValueException if rule is invalid.
     * @since   5.0.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string)$element['required'] === 'true' || (string)$element['required'] === 'required');

        if (!$required && empty($value) && $value !== '0') {
            return true;
        }

        // Make sure we allow multiple showon rules to be added
        $rules    = [];
        $andRules = explode('[AND]', $value);
        foreach ($andRules as $andRule) {
            $orRules = explode('[OR]', $andRule);
            foreach ($orRules as $orRule) {
                $rules[] = $orRule;
            }
        }

        foreach ($rules as $i => $rule) {
            if (!parent::test($element, $rule, $group, $input, $form)) {
                return false;
            }
        }

        return true;
    }
}
