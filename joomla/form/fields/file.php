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
class JFormFieldFile extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'File';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$size		= (string)$this->_element->attributes()->size ? ' size="'.$this->_element->attributes()->size.'"' : '';
		$class		= (string)$this->_element->attributes()->class ? ' class="'.$this->_element->attributes()->class.'"' : ' class="text_area"';
		$onchange	= (string)$this->_element->attributes()->onchange ? ' onchange="'.$this->_replacePrefix((string)$this->_element->attributes()->onchange).'"' : '';

		return '<input type="file" name="'.$this->inputName.'" id="'.$this->inputId.'" value=""'.$class.$size.$onchange.' />';
	}
}
