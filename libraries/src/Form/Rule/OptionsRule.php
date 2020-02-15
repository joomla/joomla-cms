<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 * Requires the value entered be one of the options in a field of type="list"
 *
 * @since  1.7.0
 */
class OptionsRule extends FormRule
{
	/**
	 * Method to test the value.
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
		// Check if the field is required.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Make an array of all available option values.
		$options = array();

		// Create the field
		$field = null;

		if ($form)
		{
			$field = $form->getField((string) $element->attributes()->name, $group);
		}

		// When the field exists, the real options are fetched.
		// This is needed for fields which do have dynamic options like from a database.
		if ($field && is_array($field->options))
		{
			foreach ($field->options as $opt)
			{
				$options[] = $opt->value;
			}
		}
		else
		{
			foreach ($element->option as $opt)
			{
				$options[] = $opt->attributes()->value;
			}
		}

		// There may be multiple values in the form of an array (if the element is checkboxes, for example).
		if (is_array($value))
		{
			// If all values are in the $options array, $diff will be empty and the options valid.
			$diff = array_diff($value, $options);

			return empty($diff);
		}
		else
		{
			// In this case value must be a string
			return in_array((string) $value, $options);
		}
	}
}
