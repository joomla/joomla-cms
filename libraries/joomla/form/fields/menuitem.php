<?php

/**
 * @version		$Id: category.php 13825 2009-12-23 01:03:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Import html library
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'groupedlist.php';

/**
 * Supports an HTML select list of menu item
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldMenuItem extends JFormFieldGroupedList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'MenuItem';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getGroups()
	{

		// Get the attributes
		$menuType = (string)$this->_element->attributes()->menu_type;
		$published = (string)$this->_element->attributes()->published ? explode(',', (string)$this->_element->attributes()->published) : array();
		$disable = (string)$this->_element->attributes()->disable ? explode(',', (string)$this->_element->attributes()->disable) : array();

		// Get the com_menus helper
		require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

		// Get the items
		$items = MenusHelper::getMenuLinks($menuType, 0, 0, $published);

		// Prepare return value
		$groups = array();

		// If a menu type was set
		if ($menuType)
		{
			$groups[$menuType] = array();

			// Loop over links
			foreach($items as $link)
			{

				// Generate an option disabling it if it's the case
				$groups[$menuType][] = JHtml::_('select.option', $link->value, $link->text, 'value', 'text', in_array($link->type, $disable));
			}
		}

		// else all menu types have to be displayed
		else
		{

			// Loop over types
			foreach($items as $menu)
			{
				$groups[$menu->menutype] = array();

				// Loop over links
				foreach($menu->links as $link)
				{

					// Generate an option disabling it if it's the case
					$groups[$menu->menutype][] = JHtml::_('select.option', $link->value, $link->text, 'value', 'text', in_array($link->type, $disable));
				}
			}
		}
		// Merge any additional options in the XML definition.
		$groups = array_merge(parent::_getGroups(), $groups);
		return $groups;
	}
}
