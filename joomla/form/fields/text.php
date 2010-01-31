<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldText extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Text';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$size =((string)$this->_element->attributes()->size) ? ' size="'.$this->_element->attributes()->size.'"' : '';
		$class =((string)$this->_element->attributes()->class) ? ' class="'.$this->_element->attributes()->class.'"' : ' class="text_area"';
		$readonly =((string)$this->_element->attributes()->readonly == 'true') ? ' readonly="readonly"' : '';
		$onchange =((string)$this->_element->attributes()->onchange) ? ' onchange="'.$this->_replacePrefix((string)$this->_element->attributes()->onchange).'"' : '';
		$maxLength =((string)$this->_element->attributes()->maxlength) ? ' maxlength="'.$this->_element->attributes()->maxlength.'"' : '';

		return '<input type="text" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'"'.$class.$size.$readonly.$onchange.$maxLength.' />';
	}
}
