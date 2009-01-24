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
class JFormFieldText extends JFormField
{
	/**
	 * The field type.
	 *
	 * @access	public
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Text';

	/**
	 * Method to get the field input.
	 *
	 * @access	protected
	 * @return	string		The field input.
	 * @since	1.6
	 */
	protected function _getInput()
	{
		$size		= $this->_element->attributes('size') ? ' size="'.$this->_element->attributes('size').'"' : '';
		$class		= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="text_area"';
		$readonly	= $this->_element->attributes('readonly') == 'true' ? ' readonly="readonly"' : '';
		$onchange	= $this->_element->attributes('onchange') ? ' onchange="'.$this->_element->attributes('onchange').'"' : '';

		return '<input type="text" name="'.$this->inputName.'" id="'.$this->inputId.'" value="'.htmlspecialchars($this->value).'" '.$class.$size.$readonly.$onchange.' />';
	}
}