<?php
/**
 * @version		$Id: textarea.php 11831 2009-05-17 16:53:20Z erdsiger $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.field');

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
	public $type = 'Textarea';

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

		// convert <br /> tags so they are not visible when editing
		$value	= str_replace('<br />', "\n", $this->value);

		return '<textarea name="'.$this->inputName.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.$readonly.' id="'.$this->inputId.'" >'.htmlspecialchars($value).'</textarea>';
	}
}
