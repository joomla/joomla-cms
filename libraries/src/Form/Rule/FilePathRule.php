<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  3.9.21
 */
class FilePathRule extends FormRule
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
     * @since   3.9.21
     */
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        $value = trim($value);

        // If the field is empty and not required, the field is valid.
        $required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

        if (!$required && empty($value)) {
            return true;
        }

        // Get the exclude setting from the xml
        $exclude = (array) explode('|', (string) $element['exclude']);

        // Exclude current folder '.' to be safe from full path disclosure
        $exclude[] = '.';

        // Check the exclude setting
        $path = preg_split('/[\/\\\\]/', $value);

        if (\in_array(strtolower($path[0]), $exclude) || empty($path[0])) {
            return false;
        }

        // Prepend the root path
        $value = JPATH_ROOT . '/' . $value;

        // Check if $value is a valid path, which includes not allowing to break out of the current path
        try {
            Path::check($value);
        } catch (\Exception) {
            // When there is an exception in the check path this is not valid
            return false;
        }

        // When there are no exception this rule should pass.
        // See: https://github.com/joomla/joomla-cms/issues/30500#issuecomment-683290162
        return true;
    }
}
