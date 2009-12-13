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
	static function getList(&$params)
	{
		// Initialise variables.
		$list		= array();
		$db			= JFactory::getDbo();
		$user		= JFactory::getUser();
		$menu		= JSite::getMenu();
		$active		= $menu->getActive();
		$rlu		= array();
		$start		= (int) $params->get('startLevel');
		$end		= (int) $params->get('endLevel');
		$showAll	= $params->get('showAllChildren');

		// Deal with start level.
		if ($start) {
			// Need a second query to backtrace.
			$query = new JQuery;
			$query->select('m.id');
			$query->from('#__menu AS m');
			$query->join('LEFT', '#__menu AS p ON p.id = '.(int) $active->id);
			$query->where('m.level = '.$start);
			$query->where('m.lft <= p.lft');
			$query->where('m.rgt >= p.rgt');

			$db->setQuery($query);
			$startId = $db->loadResult();

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseWarning($db->getErrorMsg());
				return $list;
			}

			if (empty($startId)) {
				return $list;
			}
		}

		// Get the menu items as a tree.
		$query = new JQuery;
		$query->select('n.id, n.parent_id, n.title, n.alias, n.path, n.level, n.link, n.type, n.browserNav, n.params, n.home');
		$query->from('#__menu AS n');

		// Deal with start level.
		if ($start) {
			$query->join('INNER', '#__menu AS p ON p.id = '.(int) $startId);
		} else {
			$query->join('INNER', '#__menu AS p ON p.lft = 0');
		}

		// Deal with end level.
		if ($end) {
			$query->where('n.level <= '.$end);
		}

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
		foreach ($list as $i => &$item) {
			$rlu[$item->id]		= $i;

			// Compute tree step information.
			$item->deeper		= (isset($list[$i+1]) && ($item->level < $list[$i+1]->level));
			$item->shallower	= (isset($list[$i+1]) && ($item->level > $list[$i+1]->level));
			$item->level_diff	= (isset($list[$i+1])) ? ($item->level - $list[$i+1]->level) : 0;
			$item->active		= false;
			$item->params		= new JObject(json_decode($item->params));

			switch ($item->type)
			{
				case 'separator':
					// No further action needed.
					continue;

				case 'url':
					if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
						// If this is an internal Joomla link, ensure the Itemid is set.
						$item->link = $tmp->link.'&amp;Itemid='.$item->id;
					}
					break;

				case 'alias':
					// If this is an alias use the item id stored in the parameters to make the link.
					$item->link = 'index.php?Itemid='.$item->params->aliasoptions;
					break;

				default:
					$router = JSite::getRouter();
					if ($router->getMode() == JROUTER_MODE_SEF) {
						$item->link = 'index.php?Itemid='.$item->id;
					} else {
						$item->link .= '&Itemid='.$item->id;
					}
					break;
			}

			if ($item->home == 1)
			{
				// Correct the URL for the home page.
				$item->link = JURI::base();
			} elseif (strcasecmp(substr($item->link, 0, 4), 'http') && (strpos($item->link, 'index.php?') !== false)) {
				// This is an internal Joomla web site link.
				$item->link = JRoute::_($item->link, true, $item->params->get('secure'));
			} else {
				// Correct the & in the link.
				$item->link = str_replace('&', '&amp;', $item->link);
			}
		}

		// Set the active state of items from active to the tree root.
		$itemId		= $active->id;
		$runaway	= count($list);
		while ($itemId && $runaway--)
		{
			if (@$list[$rlu[$itemId]]) {
				$list[$rlu[$itemId]]->active = true;
				$itemId = $list[$rlu[$itemId]]->parent_id;
			} else {
				$itemId = 0;
			}
		}

		return $list;
	}
}
