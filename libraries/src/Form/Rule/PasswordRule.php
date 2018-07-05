<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  3.1.2
 */
class PasswordRule extends FormRule
{
	/**
	 * Method to test if two values are not equal. To use this rule, the form
	 * XML needs a validate attribute of equals and a field attribute
	 * that is equal to the field to test against.
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
	 * @since   3.1.2
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function test(\SimpleXMLElement $element, $value, $group = null, Registry $input = null, Form $form = null)
	{
		$meter            = isset($element['strengthmeter']) ? ' meter="0"' : '1';
		$threshold        = isset($element['threshold']) ? (int) $element['threshold'] : 66;
		$minimumLength    = isset($element['minimum_length']) ? (int) $element['minimum_length'] : 4;
		$minimumIntegers  = isset($element['minimum_integers']) ? (int) $element['minimum_integers'] : 0;
		$minimumSymbols   = isset($element['minimum_symbols']) ? (int) $element['minimum_symbols'] : 0;
		$minimumUppercase = isset($element['minimum_uppercase']) ? (int) $element['minimum_uppercase'] : 0;

		// If we have parameters from com_users, use those instead.
		// Some of these may be empty for legacy reasons.
		$params = ComponentHelper::getParams('com_users');

		if (!empty($params))
		{
			$minimumLengthp    = $params->get('minimum_length');
			$minimumIntegersp  = $params->get('minimum_integers');
			$minimumSymbolsp   = $params->get('minimum_symbols');
			$minimumUppercasep = $params->get('minimum_uppercase');
			$meterp            = $params->get('meter');
			$thresholdp        = $params->get('threshold');

			empty($minimumLengthp) ? : $minimumLength = (int) $minimumLengthp;
			empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
			empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
			empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
			empty($meterp) ? : $meter = $meterp;
			empty($thresholdp) ? : $threshold = $thresholdp;
		}

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] === 'true' || (string) $element['required'] === 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		$valueLength = strlen($value);

		// Load language file of com_users component
		Factory::getLanguage()->load('com_users');

		// We set a maximum length to prevent abuse since it is unfiltered.
		if ($valueLength > 4096)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_MSG_PASSWORD_TOO_LONG'), 'warning');
		}

		// We don't allow white space inside passwords
		$valueTrim = trim($value);

		// Set a variable to check if any errors are made in password
		$validPassword = true;

		if (strlen($valueTrim) !== $valueLength)
		{
			Factory::getApplication()->enqueueMessage(
				Text::_('COM_USERS_MSG_SPACES_IN_PASSWORD'),
				'warning'
			);

			$validPassword = false;
		}

		// Minimum number of integers required
		if (!empty($minimumIntegers))
		{
			$nInts = preg_match_all('/[0-9]/', $value, $imatch);

			if ($nInts < $minimumIntegers)
			{
				Factory::getApplication()->enqueueMessage(
					Text::plural('COM_USERS_MSG_NOT_ENOUGH_INTEGERS_N', $minimumIntegers),
					'warning'
				);

				$validPassword = false;
			}
		}

		// Minimum number of symbols required
		if (!empty($minimumSymbols))
		{
			$nsymbols = preg_match_all('[\W]', $value, $smatch);

			if ($nsymbols < $minimumSymbols)
			{
				Factory::getApplication()->enqueueMessage(
					Text::plural('COM_USERS_MSG_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols),
					'warning'
				);

				$validPassword = false;
			}
		}

		// Minimum number of upper case ASCII characters required
		if (!empty($minimumUppercase))
		{
			$nUppercase = preg_match_all('/[A-Z]/', $value, $umatch);

			if ($nUppercase < $minimumUppercase)
			{
				Factory::getApplication()->enqueueMessage(
					Text::plural('COM_USERS_MSG_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase),
					'warning'
				);

				$validPassword = false;
			}
		}

		// Minimum length option
		if (!empty($minimumLength))
		{
			if (strlen((string) $value) < $minimumLength)
			{
				Factory::getApplication()->enqueueMessage(
					Text::plural('COM_USERS_MSG_PASSWORD_TOO_SHORT_N', $minimumLength),
					'warning'
				);

				$validPassword = false;
			}
		}

		// If valid has violated any rules above return false.
		if (!$validPassword)
		{
			return false;
		}

		return true;
	}
}
