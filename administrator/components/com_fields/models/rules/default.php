<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * JFormRuleDefault for com_fields to make sure the default value is valid.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormRuleDefault extends JFormRule
{
	/**
	 * Method to test the default value against the field type validation rule
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		// Skip validation if empty
		if (empty($value))
		{
			return true;
		}

		// Load the JFormRule object for the field.
		$type = $form->getValue('type');
		$rule = JFormHelper::loadRuleType($type);

		// If the object could not be loaded abort validation.
		if ($rule === false)
		{
			return true;
		}

		// Run the field validation rule test.
		$valid = $rule->test($element, $value, $group, $input, $form);

		// Check for an error in the validation test.
		if ($valid instanceof Exception)
		{
			return $valid;
		}

		// Check if the field is valid.
		if ($valid === false)
		{
			// Does the field have a defined error message?
			$message = (string) $element['message'];

			if ($message)
			{
				$message = JText::_($element['message']);

				return new UnexpectedValueException($message);
			}
			else
			{
				$message = JText::_($element['label']);
				$message = JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $message);

				return new UnexpectedValueException($message);
			}
		}

		return true;
	}
}
