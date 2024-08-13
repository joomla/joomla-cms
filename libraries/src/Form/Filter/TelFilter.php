<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Filter;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFilterInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Filter class for phone numbers
 *
 * @since  4.0.0
 */
class TelFilter implements FormFilterInterface
{
    /**
     * Method to filter a field value.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
     * @param   ?Form              $form     The form object for which the field is being tested.
     *
     * @return  mixed   The filtered value.
     *
     * @since   4.0.0
     */
    public function filter(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $value = trim($value);

        // Does it match the NANP pattern?
        if (preg_match('/^(?:\+?1[-. ]?)?\(?([2-9][0-8][0-9])\)?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/', $value) == 1) {
            $number = (string) preg_replace('/[^\d]/', '', $value);

            if (substr($number, 0, 1) === '1') {
                $number = substr($number, 1);
            }

            if (substr($number, 0, 2) === '+1') {
                $number = substr($number, 2);
            }

            $result = '1.' . $number;
        } elseif (preg_match('/^\+(?:[0-9] ?){6,14}[0-9]$/', $value) == 1) {
            // If not, does it match ITU-T?
            $countrycode = substr($value, 0, strpos($value, ' '));
            $countrycode = (string) preg_replace('/[^\d]/', '', $countrycode);
            $number      = strstr($value, ' ');
            $number      = (string) preg_replace('/[^\d]/', '', $number);
            $result      = $countrycode . '.' . $number;
        } elseif (preg_match('/^\+[0-9]{1,3}\.[0-9]{4,14}(?:x.+)?$/', $value) == 1) {
            // If not, does it match EPP?
            if (strstr($value, 'x')) {
                $xpos  = strpos($value, 'x');
                $value = substr($value, 0, $xpos);
            }

            $result = str_replace('+', '', $value);
        } elseif (preg_match('/[0-9]{1,3}\.[0-9]{4,14}$/', $value) == 1) {
            // Maybe it is already ccc.nnnnnnn?
            $result = $value;
        } else {
            // If not, can we make it a string of digits?
            $value = (string) preg_replace('/[^\d]/', '', $value);

            if ($value != null && \strlen($value) <= 15) {
                $length = \strlen($value);

                // If it is fewer than 13 digits assume it is a local number
                if ($length <= 12) {
                    $result = '.' . $value;
                } else {
                    // If it has 13 or more digits let's make a country code.
                    $cclen  = $length - 12;
                    $result = substr($value, 0, $cclen) . '.' . substr($value, $cclen);
                }
            } else {
                // If not let's not save anything.
                $result = '';
            }
        }

        return $result;
    }
}
