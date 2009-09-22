<?php
/**
 * @version		$Id: menuparent.php 12220 2009-06-20 12:57:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once JPATH_LIBRARIES.DS.'joomla'.DS.'form'.DS.'fields'.DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
 
 //TODO add the menu name as the first level in the list display.
class JFormFieldAliasoptions extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'AliasOptions';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$db		= &JFactory::getDbo();
		$query	= new JQuery;

		$query->select('a.id AS value,a.menutype AS menu,a.title AS text, a.level');
		$query->from('#__menu AS a');
		$query->join('LEFT', '`#__menu` AS b ON a.lft > b.lft AND a.rgt < b.rgt');
		$query->where('a.title != "Menu_Item_Root"');


		// Prevent parenting to of this item.
		if ($parentId = $this->_form->getValue('parent_id')) {
			$query->join('LEFT', '`#__menu` AS p ON p.id = '.(int) $parentId);
			$query->where('(a.lft <= p.lft OR a.rgt >= p.rgt)');
		}

		$query->group('a.id');
		$query->order('menu ASC,a.lft ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ',$options[$i]->level).$options[$i]->text;
		}
		return $options;
	}
}