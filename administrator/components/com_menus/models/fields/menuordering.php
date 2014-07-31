<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class JFormFieldMenuOrdering extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.7
	 */
	protected $type = 'MenuOrdering';

	/**
	 * Method to get the list of siblings in a menu.
	 * The method requires that parent be set.
	 *
	 * @return	array	The field option objects or false if the parent field has not been set
	 * @since	1.7
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Get the parent
		$parent_id = $this->form->getValue('parent_id', 0);
		if ( empty($parent_id))
		{
			return false;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__menu AS a');

		$query->where('a.published >= 0');
		$query->where('a.parent_id =' . (int) $parent_id);
		if ($menuType = $this->form->getValue('menutype')) {
			$query->where('a.menutype = '.$db->quote($menuType));
		}
		else {
			$query->where('a.menutype != '.$db->quote(''));
		}

		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		$options = array_merge(
		array(array ('value' =>'-1', 'text'=>JText::_('COM_MENUS_ITEM_FIELD_ORDERING_VALUE_FIRST'))),
		$options,
		array(array( 'value' =>'-2', 'text'=>JText::_('COM_MENUS_ITEM_FIELD_ORDERING_VALUE_LAST')))
		);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup.
	 * @since   1.7
	 */
	protected function getInput()
	{
		if ($this->form->getValue('id', 0) == 0)
		{
			return '<span class="readonly">' . JText::_('COM_MENUS_ITEM_FIELD_ORDERING_TEXT') . '</span>';
		}
		else
		{
			return parent::getInput();
		}
	}
}
