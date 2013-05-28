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
 * Standard Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionStandard
{
	/**
	 * The option type.
	 *
	 * @var  string
	 */
	protected $type = 'Standard';

	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$value = (string) $option['value'];
		$text = trim((string) $option) != '' ? trim((string) $option) : $value;

		$disabled = (string) $option['disabled'];
		$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
		$disabled = $disabled || ($this->readonly && $value != $this->value);

		$checked = (string) $option['checked'];
		$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

		$selected = (string) $option['selected'];
		$selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

		$tmp = array(
				'value'    => $value,
				'text'     => JText::alt($text, $fieldname),
				'disable'  => $disabled,
				'class'    => (string) $option['class'],
				'selected' => ($checked || $selected),
				'checked'  => ($checked || $selected)
			);

		// Set some event handler attributes. But really, should be using unobtrusive js.
		$tmp['onclick']  = (string) $option['onclick'];
		$tmp['onchange']  = (string) $option['onchange'];

		// Return as an array containing an object.
		return array((object) $tmp);
	}
}
