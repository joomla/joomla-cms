<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.field');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldList extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'List';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$options = array();

		// Iterate through the children and build an array of options.
		foreach ($this->_element->children() as $option) {
			$options[] = JHtml::_('select.option', $option->attributes('value'), JText::_($option->data()));
		}

		return $options;
	}

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$class		= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="inputbox"';
		$size		= $this->_element->attributes('size') ? ' size="'.(int) $this->_element->attributes('size').'"' : '';
		$disabled	= $this->_element->attributes('disabled') == 'true' ? true : false;
		$readonly	= $this->_element->attributes('readonly') == 'true' ? true : false;
		$attributes	= $class.$size;
		$attributes = ($disabled || $readonly) ? $attributes.' disabled="disabled"' : $attributes;
		$options	= (array)$this->_getOptions();
		$return		= null;

		// Handle a disabled list.
		if ($disabled)
		{
			// Create a disabled list.
			$return .= JHtml::_('select.genericlist', $options, $this->inputName, $attributes, 'value', 'text', $this->value, $this->inputId);
		}
		// Handle a read only list.
		else if ($readonly)
		{
			// Create a disabled list with a hidden input to store the value.
			$return .= JHtml::_('select.genericlist', $options, '', $attributes, 'value', 'text', $this->value, $this->inputId);
			$return	.= '<input type="hidden" name="'.$this->inputName.'" value="'.$this->value.'" />';
		}
		// Handle a regular list.
		else
		{
			// Create a regular list.
			$return = JHtml::_('select.genericlist', $options, $this->inputName, $attributes, 'value', 'text', $this->value, $this->inputId);
		}

		return $return;
	}
}