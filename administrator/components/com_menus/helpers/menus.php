<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menus component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.6
 */
class MenusHelper
{
	/**
	 * Defines the valid request variables for the reverse lookup.
	 */
	protected static $_filter = array('option', 'view', 'layout');

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_MENUS_SUBMENU_MENUS'),
			'index.php?option=com_menus&view=menus',
			$vName == 'menus'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_MENUS_SUBMENU_ITEMS'),
			'index.php?option=com_menus&view=items',
			$vName == 'items'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The menu ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($parentId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($parentId)) {
			$assetName = 'com_menus';
		} else {
			$assetName = 'com_menus.item.'.(int) $parentId;
		}

		$actions = JAccess::getActions('com_menus');

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets a standard form of a link for lookups.
	 *
	 * @param	mixed	A link string or array of request variables.
	 *
	 * @return	mixed	A link in standard option-view-layout form, or false if the supplied response is invalid.
	 */
	public static function getLinkKey($request)
	{
		if (empty($request)) {
			return false;
		}

		// Check if the link is in the form of index.php?...
		if (is_string($request))
		{
			$args = array();
			if (strpos($request, 'index.php') === 0) {
				parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
			}
			else {
				parse_str($request, $args);
			}
			$request = $args;
		}

		// Only take the option, view and layout parts.
		foreach ($request as $name => $value)
		{
			if (!in_array($name, self::$_filter))
			{
				// Remove the variables we want to ignore.
				unset($request[$name]);
			}
		}

		ksort($request);

		return 'index.php?'.http_build_query($request, '', '&');
	}

	/**
	 * Get the menu list for create a menu module
	 *
	 * @return		array	The menu array list
	 * @since		1.6
	 */
	public static function getMenuTypes()
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT a.menutype FROM #__menu_types AS a');
		return $db->loadColumn();
	}

	/**
	 * Get a list of menu links for one or all menus.
	 *
	 * @param	string	An option menu to filter the list on, otherwise all menu links are returned as a grouped array.
	 * @param	int		An optional parent ID to pivot results around.
	 * @param	int		An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
	 * @param	array	An optional array of states
	 */
	public static function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published=array(), $languages=array())
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text, a.level, a.menutype, a.type, a.template_style_id, a.checked_out');
		$query->from('#__menu AS a');
		$query->join('LEFT', $db->quoteName('#__menu').' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter by the type
		if ($menuType) {
			$query->where('(a.menutype = '.$db->quote($menuType).' OR a.parent_id = 0)');
		}

		if ($parentId) {
			if ($mode == 2) {
				// Prevent the parent and children from showing.
				$query->join('LEFT', '#__menu AS p ON p.id = '.(int) $parentId);
				$query->where('(a.lft <= p.lft OR a.rgt >= p.rgt)');
			}
		}

		if (!empty($languages)) {
			if (is_array($languages)) {
				$languages = '(' . implode(',', array_map(array($db, 'quote'), $languages)) . ')';
			}
			$query->where('a.language IN ' . $languages);
		}

		if (!empty($published)) {
			if (is_array($published)) $published = '(' . implode(',', $published) .')';
			$query->where('a.published IN ' . $published);
		}

		$query->where('a.published != -2');
		$query->group('a.id, a.title, a.level, a.menutype, a.type, a.template_style_id, a.checked_out, a.lft');
		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$links = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}

		// Pad the option text with spaces using depth level as a multiplier.
		foreach ($links as &$link) {
			$link->text = str_repeat('- ', $link->level).$link->text;
		}

		if (empty($menuType)) {
			// If the menutype is empty, group the items by menutype.
			$query->clear();
			$query->select('*');
			$query->from('#__menu_types');
			$query->where('menutype <> '.$db->quote(''));
			$query->order('title, menutype');
			$db->setQuery($query);

			try
			{
				$menuTypes = $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
				return false;
			}

			// Create a reverse lookup and aggregate the links.
			$rlu = array();
			foreach ($menuTypes as &$type) {
				$rlu[$type->menutype] = &$type;
				$type->links = array();
			}

			// Loop through the list of menu links.
			foreach ($links as &$link) {
				if (isset($rlu[$link->menutype])) {
					$rlu[$link->menutype]->links[] = &$link;

					// Cleanup garbage.
					unset($link->menutype);
				}
			}

			return $menuTypes;
		} else {
			return $links;
		}
	}
	static public function getAssociations($pk)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__menu as m');
		$query->innerJoin('#__associations as a ON a.id=m.id AND a.context='.$db->quote('com_menus.item'));
		$query->innerJoin('#__associations as a2 ON a.key=a2.key');
		$query->innerJoin('#__menu as m2 ON a2.id=m2.id');
		$query->where('m.id=' . (int) $pk);
		$query->select('m2.language, m2.id');
		$db->setQuery($query);

		try
		{
			$menuitems = $db->loadObjectList('language');
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			return false;
		}
		foreach ($menuitems as $tag => $item)
		{
			$associations[$tag] = $item->id;
		}
		return $associations;
	}
}
