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

jimport('joomla.html.html');
jimport('joomla.form.field');

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
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Checkbox';

	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function _getInput()
	{
		$value = $this->_element->attributes('value') !== null ? $this->_element->attributes('value') : '';
		$checked = (!empty($value) && $value == $this->value) ? 'checked="checked"' : '';
		return '<input type="checkbox" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.$value.'" '.$checked.' />';
	}
}