<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;

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

		if (!($field instanceof \JFormFieldSubform))
		{
			throw new \UnexpectedValueException(sprintf('%s is no subform field.', $element['name']));
		}

		$subForm = $field->loadSubForm();

		// Multiple values: Validate every row.
		if ($field->multiple)
		{
			foreach ($value as $row)
			{
				if ($subForm->validate($row) === false)
				{
					// Pass the first error that occurred on the subform validation.
					$errors = $subForm->getErrors();

					if (!empty($errors[0]))
					{
						return $errors[0];
					}

					return false;
				}
			}
		}
		// Single value.
		else
		{
			if ($subForm->validate($value) === false)
			{
				// Pass the first error that occurred on the subform validation.
				$errors = $subForm->getErrors();

				if (!empty($errors[0]))
				{
					return $errors[0];
				}

				return false;
			}
		}

		return true;
	}
}
