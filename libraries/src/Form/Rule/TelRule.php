<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
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
 * Form Rule class for the Joomla Platform
 *
 * @since  1.7.0
 */
class TelRule extends FormRule
{
    /**
     * Method to test the url for a valid parts.
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
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // If the field is empty and not required, the field is valid.
        $required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

        if (!$required && empty($value)) {
            return true;
        }

        /*
         * @link http://www.nanpa.com/
         * @link http://tools.ietf.org/html/rfc4933
         * @link http://www.itu.int/rec/T-REC-E.164/en
         *
         * Regex by Steve Levithan
         * @link http://blog.stevenlevithan.com/archives/validate-phone-number
         * @note that valid ITU-T and EPP must begin with +.
         */
        $regexarray = [
            'NANP'  => '/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/',
            'ITU-T' => '/^\+(?:[0-9] ?){6,14}[0-9]$/',
            'EPP'   => '/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/',
        ];

        if (isset($element['plan'])) {
            $plan = (string) $element['plan'];

            if ($plan === 'northamerica' || $plan === 'us') {
                $plan = 'NANP';
            } elseif ($plan === 'International' || $plan === 'int' || $plan === 'missdn' || !$plan) {
                $plan = 'ITU-T';
            } elseif ($plan === 'IETF') {
                $plan = 'EPP';
            }

            $regex = $regexarray[$plan];

            // Test the value against the regular expression.
            if (preg_match($regex, $value) == false) {
                return false;
            }
        } else {
            /*
             * If the rule is set but no plan is selected just check that there are between
             * 7 and 15 digits inclusive and no illegal characters (but common number separators
             * are allowed).
             */
            $cleanvalue = preg_replace('/[+. \-(\)]/', '', $value);
            $regex      = '/^[0-9]{7,15}?$/';

            if (preg_match($regex, $cleanvalue) == true) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }
}
