<?php
/**
 * @version		$Id: radio.php 11546 2009-01-29 12:39:09Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
class JFormFieldRadio extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Radio';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$options = array();

		// Get the options for the radio list.
		foreach ($this->_element->children() as $option) {
			$options[] = JHtml::_('select.option', $option->attributes('value'), JText::_($option->data()));
		}

		return JHtml::_('select.radiolist', $options, $this->inputName, '', 'value', 'text', $this->value, $this->inputId);
	}
}