<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Framework.
 *
 * @since  3.5
 */
class JFormRuleDatetime extends JFormRule
{
	/**
	 * Method to test if the field value has datetime format
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
	 * @since   3.5
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		if ((int) $value > 0)
		{
			try
			{
				$date = JFactory::getDate($value);
			}
			catch (Exception $e)
			{
				$fieldLabel = JText::_($element['label']);
				$message    = JText::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID_DATETIME', $value, $fieldLabel);

				return new UnexpectedValueException($message);
			}
		}

		return true;
	}
}
