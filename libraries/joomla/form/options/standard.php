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
		$disabled = (string) $option['disabled'];
		$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

		$checked = (string) $option['checked'];
		$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

		// Create a new option object based on the <option /> element.
		return array(
			(object) array(
				'value' => (string) $option['value'],
				'text' => JText::_(trim((string) $option)),
				'disable' => $disabled,
				'checked' => $checked,
				'class' => (string) $option['class'],
				'onclick' => (string) $option['onclick'],
				'onchange' => (string) $option['onchange']
			)
		);
	}
}
