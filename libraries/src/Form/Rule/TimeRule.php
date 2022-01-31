<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  4.0.0
 */
class TimeRule extends FormRule
{
	/**
	 * Method to test the range for a number value using min and max attributes.
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
	 * @since   4.0.0
	 *
	 * @throws \Exception
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null): bool
	{
		// Check if the field is required.
		$required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

		// If the value is empty and the field is not required return True.
		if (($value === '' || $value === null) && !$required)
		{
			return true;
		}

		$stringValue = (string) $value;

		// If the length of a field is smaller than 5 return error message
		if (strlen($stringValue) !== 5 && !isset($element['step']))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If the third symbol isn't a ':' return error message
		if ($stringValue[2] !== ':')
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If the are other symbols except of numbers and ':' return error message
		if (!preg_match('#^[0-9:]+$#', $stringValue))
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If min and max is set
		if (isset($element['min']) && isset($element['max']))
		{
			$min = $element['min'][0] . $element['min'][1];
			$max = $element['max'][0] . $element['max'][1];

			// If the input is smaller than the set min return error message
			if (intval($min) > intval($stringValue[0] . $stringValue[1]))
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('JLIB_FORM_FIELD_INVALID_MIN_TIME', $min),
					'warning'
				);

				return false;
			}

			// If the input is greater than the set max return error message
			if (intval($max) < intval($stringValue[0] . $stringValue[1]))
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('JLIB_FORM_FIELD_INVALID_MAX_TIME'),
					'warning'
				);

				return false;
			}

			// If the hour input is equal to the set max but the minutes input is greater than zero return error message
			if (intval($max) === intval($stringValue[0] . $stringValue[1]))
			{
				if (intval($element['min'][3] . $element['min'][4]) !== 0)
				{
					Factory::getApplication()->enqueueMessage(
						Text::_('JLIB_FORM_FIELD_INVALID_MAX_TIME'),
						'warning'
					);

					return false;
				}
			}
		}

		// If the first symbol is greater than 2 return error message
		if (intval($stringValue[0]) > 2)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If the first symbol is greater than 2 and the second symbol is greater than 3 return error message
		if (intval($stringValue[0]) === 2 && intval($stringValue[1]) > 3)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If the fourth symbol is greater than 5 return error message
		if (intval($stringValue[3]) > 5)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT'),
				'warning'
			);

			return false;
		}

		// If the step is set return same error messages as above but taking into a count that there 8 and not 5 symbols
		if (isset($element['step']))
		{
			if (strlen($stringValue) !== 8
				|| intval($stringValue[5]) !== ':'
				|| intval($stringValue[6]) > 5)
			{
				Factory::getApplication()->enqueueMessage(
					Text::_('JLIB_FORM_FIELD_INVALID_TIME_INPUT_SECONDS'),
					'warning'
				);

				return false;
			}
		}

		return true;
	}
}
