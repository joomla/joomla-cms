<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Remove phpcs exception with deprecated constant JCOMPAT_UNICODE_PROPERTIES
 * @phpcs:disable PSR1.Files.SideEffects
 */

namespace Joomla\CMS\Form;

\defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

// Detect if we have full UTF-8 and unicode PCRE support.
if (!\defined('JCOMPAT_UNICODE_PROPERTIES')) {
    /**
     * Flag indicating UTF-8 and PCRE support is present
     *
     * @var    boolean
     * @since  1.6
     *
     * @deprecated 5.0 Will be removed without replacement (Also remove phpcs exception)
     */
    \define('JCOMPAT_UNICODE_PROPERTIES', (bool) @preg_match('/\pL/u', 'a'));
}

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  1.6
 */
class FormRule
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    string
     * @since  1.6
     */
    protected $regex;

    /**
     * The regular expression modifiers to use when testing a form field value.
     *
     * @var    string
     * @since  1.6
     */
    protected $modifiers = '';

    /**
     * Method to test the value.
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
     * @since   1.6
     * @throws  \UnexpectedValueException if rule is invalid.
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
    {
        // Check for a valid regex.
        if (empty($this->regex)) {
            throw new \UnexpectedValueException(sprintf('%s has invalid regex.', \get_class($this)));
        }

        // Detect if we have full UTF-8 and unicode PCRE support.
        static $unicodePropertiesSupport = null;

        if ($unicodePropertiesSupport === null) {
            $unicodePropertiesSupport = (bool) @\preg_match('/\pL/u', 'a');
        }

        // Add unicode property support if available.
        if ($unicodePropertiesSupport) {
            $this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers . 'u';
        }

        // Test the value against the regular expression.
        if (preg_match(\chr(1) . $this->regex . \chr(1) . $this->modifiers, $value)) {
            return true;
        }

        return false;
    }
}
