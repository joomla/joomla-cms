<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
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
class JFormFieldTextarea extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Textarea';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$rows	= $this->_element->attributes('rows');
		$cols	= $this->_element->attributes('cols');
		$class	= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="text_area"';
		$readonly	= $this->_element->attributes('readonly') == 'true' ? ' readonly="readonly"' : '';

		return '<textarea name="'.$this->inputName.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.$readonly.' id="'.$this->inputId.'" >'.htmlspecialchars($this->value).'</textarea>';
	}
}
