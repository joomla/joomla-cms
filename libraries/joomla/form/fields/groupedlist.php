<?php

/**
 * @version		$Id: list.php 13967 2010-01-03 22:22:59Z eddieajau $
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
class JFormFieldGroupedList extends JFormField
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'GroupedList';

	/**
	 * Method to get a list of groups for a list input.
	 *
	 * @return	array		An array of array JHtml options.
	 */
	protected function _getGroups()
	{
		$groups = array();

		// Init label
		$label = 0;

		// Iterate through the children and build an array of groups.
		foreach($this->_element->children() as $element)
		{
			switch ($element->getName())
			{
				case 'option':
					if (!isset($groups[$label])) $groups[$label] = array();
					$groups[$label][] = JHtml::_('select.option', (string)$element->attributes()->value, JText::_(trim((string)$element)),'value','text',(string)$option->attributes()->disabled=='true');
					break;
				case 'group':
					$groupLabel = (string)$element->attributes()->label;
					if ($groupLabel) $label = $groupLabel;
					if (!isset($groups[$label])) $groups[$label] = array();

					// Iterate through the children and build an array of options.
					foreach($element->children() as $option)
					{
						$groups[$label][] = JHtml::_('select.option', (string)$option->attributes()->value, JText::_(trim((string)$option)), 'value', 'text', (string)$option->attributes('disabled')=='true');
					}
					if ($groupLabel) $label = count($groups);
					break;
				default:
					JError::raiseError(500, JText::sprintf('JFramework_Form_Fields_GroupedList_Error_Element_Name', $element->getName()));
			}
		}
		return $groups;
	}

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		$disabled = (string)$this->_element->attributes()->readonly == 'true' ? true : false;
		$attributes = '';
		if ($v = (string)$this->_element->attributes()->size)
		{
			$attributes.= ' size="' . $v . '"';
		}
		if ($v = (string)$this->_element->attributes()->class)
		{
			$attributes.= ' class="' . $v . '"';
		}
		else
		{
			$attributes.= ' class="inputbox"';
		}
		if ((string)$this->_element->attributes()->multiple)
		{
			$attributes.= ' multiple="multiple"';
		}
		if ($v = (string)$this->_element->attributes()->onchange)
		{
			$attributes.= ' onchange="' . $this->_replacePrefix($v) . '"';
		}
		if ($disabled)
		{
			$attributes.= ' disabled="disabled"';
		}

		// Get the groups
		$groups = (array)$this->_getGroups();

		// Get the html
		$return = JHtml::_('select.groupedlist', $groups, $this->inputName, array('list.attr' => $attributes, 'id' => $this->inputId, 'list.select' => $this->value, 'group.items' => null, 'option.key.toHtml' => false, 'option.text.toHtml' => false));

		// Return the html
		return $return;
	}
}
