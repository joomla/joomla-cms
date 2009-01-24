<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

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
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Textarea';

	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function _getInput()
	{
		$rows	= $this->_element->attributes('rows');
		$cols	= $this->_element->attributes('cols');
		$class	= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="text_area"';

		// convert <br /> tags so they are not visible when editing
		$value	= str_replace('<br />', "\n", $this->value);

		return '<textarea name="'.$this->inputName.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$this->inputId.'" >'.htmlspecialchars($value).'</textarea>';
	}
}