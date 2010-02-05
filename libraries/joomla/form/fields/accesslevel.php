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
class JFormFieldAccessLevel extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'AccessLevel';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getInput()
	{
		$attribs	= '';

		if ($v = $this->_element->attributes('size')) {
			$attribs	.= ' size="'.$v.'"';
		}
		if ($v = $this->_element->attributes('class')) {
			$attribs	.= ' class="'.$v.'"';
		} else {
			$attribs	.= ' class="inputbox"';
		}
		if ($m = $this->_element->attributes('multiple'))
		{
			$attribs	.= ' multiple="multiple"';
		}

		$options = array();

		// Iterate through the children and build an array of options.
		foreach ($this->_element->children() as $option) {
			$options[] = JHtml::_('select.option', $option->attributes('value'), JText::_(trim($option->data())));
		}

		return JHtml::_('access.level', $this->inputName, $this->value, $attribs, $options, $this->inputId);
	}
}
