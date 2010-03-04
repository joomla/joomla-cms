<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCheckbox extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Checkbox';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$value =((string)$this->_element->attributes()->value !== null) ? (string)$this->_element->attributes()->value : '';
		$readonly =((string)$this->_element->attributes()->readonly == 'true') ? ' disabled="disabled"' : '';
		$checked = (!empty($value) && $value == $this->value) ? ' checked="checked"' : '';
		$attributes = '';
		if ($v = (string)$this->_element->attributes()->onclick) {
			$attributes .= ' onclick="'.$this->_replacePrefix($v).'"';
		}

		return '<input type="checkbox" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.$value.'"'.$checked.$readonly.$attributes.' />';
	}
}
