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

	protected $type = 'Standard';

	/**
	 * Method to get a list of options.
	 *
	 * @param  SimpleXMLElement  $option     <option/> element
	 * @param  string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$disabled = (string) $option['disabled'];
		$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

		// Create a new option object based on the <option /> element.
		$tmp = JHtml::_(
			'select.option',
			(string) $option['value'],
			JText::_(trim((string) $option)),
			'value',
			'text',
			$disabled
		);

		// Set some option attributes.
		$tmp->class = (string) $option['class'];

		// Set some JavaScript option attributes.
		$tmp->onclick = (string) $option['onclick'];
		$tmp->onchange = (string) $option['onchange'];

		return array($tmp);
	}

}
