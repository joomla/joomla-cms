<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.password.html#input.password
 * @note        Two password fields may be validated as matching using JFormRuleEquals
 * @since       11.1
 */
class JFormFieldPassword extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Password';

	/**
	 * Method to get the field input markup for password.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size		= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '99';
		$class		= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$auto		= ((string) $this->element['autocomplete'] == 'off') ? ' autocomplete="off"' : '';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$meter		= ((string) $this->element['strengthmeter'] == 'true' ? ' $meter= 1' : ' $meter = 0');
		$required   = $this->required ? ' required="required" aria-required="true"' : '';
		$threshold	= $this->element['threshold'] ? (int) $this->element['threshold'] : 66;
		$minimumLength = $this->element['minimum_length'] ? (int) $this->element['minimum_length'] : 4;
		$minimumIntegers = $this->element['minimum_integers'] ? (int) $this->element['minimum_integers'] : 0;
		$minimumSymbols = $this->element['minimum_symbols'] ? (int) $this->element['minimum_symbols'] : 0;
		$minimumUppercase = $this->element['minimum_uppercase'] ? (int) $this->element['minimum_uppercase'] : 0;

		// If we have parameters from com_users, use those instead.
		// Some of these may be empty for legacy reasons.
		$app = JFactory::getApplication();

		if ($app->getClientId() != 2)
		{
			$params = JComponentHelper::getParams('com_users');

			if (!empty($params))
			{
				$minimumLengthp = $params->get('minimum_length');
				$minimumIntegersp = $params->get('minimum_integers');
				$minimumSymbolsp = $params->get('minimum_symbols');
				$minimumUppercasep = $params->get('minimum_uppercase');

				if (!empty($minimumLengthp))
				{
					$minimumLength = (int) $minimumLengthp;
				}

				empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
				empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
				empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
			}
		}

		$script = '';

		if ($meter == 1)
		{
			JHtml::_('script', 'system/passwordstrength.js', true, true);
			$script = '<script type="text/javascript">new Form.PasswordStrength("' . $this->id . '",
				{
					threshold: ' . $threshold . ',
					onUpdate: function(element, strength, threshold) {
						element.set("data-passwordstrength", strength);
					}
				}
			);</script>';
		}

		return '<input type="password" name="' . $this->name . '" id="' . $this->id . '"' .
			' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' .
			$auto . $class . $readonly . $disabled . $size . $maxLength . $required . '/>' . $script;
	}
}
