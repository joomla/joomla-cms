<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.database.query');

/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 */
class modMenuHelper
{
	/**
	 * Get a list of the menu items.
	 */
	function getList(&$params)
	{
		// Initialize variables.
		$list	= array();
		$db		= JFactory::getDbo();
		$user	= JFactory::getUser();

		// Get the menu items as a tree.
		$query = new JQuery;
		$query->select('n.*');
		$query->from('#__menu AS n');
		$query->join('INNER', ' #__menu AS p ON p.lft = 0');
		$query->where('n.lft > p.lft');
		$query->where('n.lft < p.rgt');
		$query->order('n.lft');

		// Filter over the appropriate menu.
		$query->where('n.menutype = '.$db->quote($params->get('menutype', 'mainmenu')));

		// Filter over authorized access levels and publishing state.
		$query->where('n.published = 1');
		$query->where('n.access IN ('.implode(',', (array) $user->authorisedLevels()).')');

		// Get the list of menu items.
		$db->setQuery($query);
		$list = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning($db->getErrorMsg());
			return array();
		}

		// Set some values to make nested HTML rendering easier.
		for ($i=0,$n=count($list); $i<$n; $i++)
		{
			$list[$i]->deeper = (isset($list[$i+1]) && ($list[$i]->level < $list[$i+1]->level));
			$list[$i]->shallower = (isset($list[$i+1]) && ($list[$i]->level > $list[$i+1]->level));
			$list[$i]->level_diff = (isset($list[$i+1])) ? ($list[$i]->level - $list[$i+1]->level) : 0;
		}

		return $list;
	}
}
