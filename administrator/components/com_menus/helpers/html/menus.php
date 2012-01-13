<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 */
abstract class MenusHtmlMenus
{
	/**
	 * @param	int $itemid	The menu item id
	 */
	static function association($itemid)
	{
		// Get the associations
		$associations = MenusHelper::getAssociations($itemid);

		// Get the associated menu items
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('m.*');
		$query->select('mt.title as menu_title');
		$query->from('#__menu as m');
		$query->leftJoin('#__menu_types as mt ON mt.menutype=m.menutype');
		$query->where('m.id IN ('.implode(',', array_values($associations)).')');
		$query->leftJoin('#__languages as l ON m.language=l.lang_code');
		$query->select('l.image');
		$query->select('l.title as language_title');
		$db->setQuery($query);
		$items = $db->loadObjectList('id');

		// Check for a database error.
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		// Construct html
		$text = array();
		foreach ($associations as $tag=>$associated) {
			if ($associated != $itemid) {
				$text[] = JText::sprintf('COM_MENUS_TIP_ASSOCIATED_LANGUAGE', JHtml::_('image', 'mod_languages/'.$items[$associated]->image.'.gif', $items[$associated]->language_title, array('title'=>$items[$associated]->language_title), true), $items[$associated]->title, $items[$associated]->menu_title);
			}
		}
		return JHtml::_('tooltip', implode('<br />', $text), JText::_('COM_MENUS_TIP_ASSOCIATION'), 'menu/icon-16-links.png');
	}
}
