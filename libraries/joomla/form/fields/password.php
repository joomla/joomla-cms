<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Platform.
 * Text field for passwords
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 *
 * @note        Two password fields may be validated as matching using JFormRuleEquals
 * @link        http://www.w3.org/TR/html-markup/input.password.html#input.password
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
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$auto = ((string) $this->element['autocomplete'] == 'off') ? ' autocomplete="off"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="password" name="' . $this->name . '" id="' . $this->id . '"' . ' value="' .
			 htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $auto . $class . $readonly . $disabled . $size . $maxLength . '/>';
		}
	}
