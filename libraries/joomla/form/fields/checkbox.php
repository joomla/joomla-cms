<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Form
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormFieldCheckbox extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	public $type = 'Checkbox';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	11.1
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$checked	= ((string) $this->element['value'] == $this->value) ? ' checked="checked"' : '';

		// Initialize JavaScript field attributes.
		$onclick	= $this->element['onclick'] ? ' onclick="'.(string) $this->element['onclick'].'"' : '';

		return '<input type="checkbox" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.htmlspecialchars((string) $this->element['value'], ENT_COMPAT, 'UTF-8').'"' .
				$class.$checked.$disabled.$onclick.'/>';
	}
}
