<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');

/**
 * Supports an HTML select list of categories
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.6
 */
class JFormFieldOrdering extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Ordering';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$size		= $this->_element->attributes('size');
		$class		= $this->_element->attributes('class') ? 'class="'.$this->_element->attributes('class').'"' : 'class="inputbox"';
		$disabled	= $this->_element->attributes('disabled') == 'true' ? true : false;
		$readonly	= $this->_element->attributes('readonly') == 'true' ? true : false;
		$attributes	= $class;
		$attributes = ($disabled || $readonly) ? $attributes.' disabled="disabled"' : $attributes;
		$return		= null;
		$weblinkId	= (int) $this->_form->getValue('id');
		$categoryId	= (int) $this->_form->getValue('catid');
		$query		= 'SELECT ordering AS value, title AS text'
					. ' FROM #__weblinks'
					. ' WHERE catid = ' . $categoryId
					. ' ORDER BY ordering';

		// Handle a read only list.
		if ($readonly) {
			// Create a disabled list with a hidden input to store the value.
			$return .= JHTML::_('list.ordering', '', $query, $attributes, $this->value, $this->inputId, $weblinkId ? 0 : 1);
			$return	.= '<input type="hidden" name="'.$this->inputName.'" value="'.$this->value.'" />';
		}
		// Handle a regular list.
		else {
			// Create a regular list.
			$return = JHTML::_('list.ordering', $this->inputName, $query, $attributes, $this->value, $this->inputId, $weblinkId ? 0 : 1);
		}

		return $return;
	}
}