<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @since  4.0.0
 */
class CssIdentifierRule extends FormRule
{
    /**
     * Method to test if the file path is valid
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  boolean  True if the value is valid, false otherwise.
     *
     * @since   4.0.0
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && empty($value) && $value !== '0') {
            return true;
        }

        // Make sure we allow multiple classes to be added
        $cssIdentifiers = explode(' ', $value);

        foreach ($cssIdentifiers as $i => $identifier) {
            /**
             * The following regex rules are based on the Html::cleanCssIdentifier method from Drupal
             * https://github.com/drupal/drupal/blob/8.8.5/core/lib/Drupal/Component/Utility/Html.php#L116-L130
             *
             * with the addition for Joomla that we allow the colon (U+003A) and the @ (U+0040).
             */

            /**
             * Valid characters in a CSS identifier are:
             * - the hyphen (U+002D)
             * - a-z (U+0030 - U+0039)
             * - A-Z (U+0041 - U+005A)
             * - the underscore (U+005F)
             * - the colon (U+003A)
             * - the @-sign (U+0040)
             * - 0-9 (U+0061 - U+007A)
             * - ISO 10646 characters U+00A1 and higher
             */
            if (preg_match('/[^\\x{002D}\\x{0030}-\\x{0039}\\x{0040}-\\x{005A}\\x{005F}\\x{003A}\\x{0061}-\\x{007A}\\x{00A1}-\\x{FFFF}]/u', $identifier)) {
                return false;
            }

            /**
             * Full identifiers cannot start with a digit, two hyphens, or a hyphen followed by a digit.
             */
            if (preg_match('/^[0-9]/', $identifier) || preg_match('/^(-[0-9])|^(--)/', $identifier)) {
                return false;
            }
        }

        return true;
    }
}
