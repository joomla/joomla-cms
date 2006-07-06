<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JMenu class
 *
 * @author Louis Landry <louis.landry@joomla.org>
 * @package JoomlaFramework
 * @since 1.5
 */
class JMenu extends JObject
{

	/**
	 * Array to hold the menu items
	 * @access private
	 */
	var $_menuitems = array ();

	/**
	 * Array to hold the menu items
	 * @access private
	 */
	var $_thismenu = array ();

	/**
	 * Current menu item
	 */
	var $_current_id = null;

	/**
	 * Name of the URI variable for the current menu item
	 */
	var $_current_uri_var = 'Itemid';

	/**
	 * Class constructor
	 *
	 * @param string $name The menu name to load
	 * @return boolean True on success
	 * @since 1.5
	 */
	function __construct($name = 'all')
	{
		$this->_menuitems = $this->_load();

		$home = 0;
		foreach ($this->_menuitems as $item) {
			if ($item->menutype == $name || $name == 'all') {
				$this->_thismenu[] = $item;
				if ($item->home)
				{
					$home = $item->id;
				}
			}
		}

		$this->_current_id = JRequest::getVar( $this->_current_uri_var, $home, '', 'int' );
	}

	/**
	 * Loads the entire menu table into memory
	 */
	function _load()
	{
		global $mainframe;

		static $menus;

		if (isset ($menus)) {
			return $menus;
		}
		// Initialize some variables
		$db = & $mainframe->getDBO();
		$user = & $mainframe->getUser();
		$sql = "SELECT *" .
				"\n FROM #__menu" .
				"\n WHERE published = 1".
				"\n ORDER BY parent, ordering";

		$db->setQuery($sql);
		if (!($menus = $db->loadObjectList('id'))) {
			JError::raiseWarning('SOME_ERROR_CODE', "Error loading Menus: ".$db->getErrorMsg());
			return false;
		}

		return $menus;
	}

	/**
	 * Gets the current menu item
	 */
	function &getCurrent() 
	{
		$result = &$this->getItem( $this->_current_id );
		if ($result == false)
		{
			$db = &JFactory::getDBO();
			$result = JTable::getInstance( 'menu', $db );
		}
		return $result;
	}

	/**
	 * Returns a reference to the global JMenu object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $menu = JMenu::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JMenu	The Menu object.
	 * @since	1.5
	 */
	function getInstance($id = 'all')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] = & new JMenu($id);
		}

		return $instances[$id];
	}

	/**
	 * Getter for a menu item
	 * 
	 * @param int The item id
	 * @return mixed The item, or false if not found
	 */
	function &getItem($id)
	{
		if (isset($this->_menuitems[$id])) {
			return $this->_menuitems[$id];
		} else {
			return false;
		}
	}

	/**
	 * Gets items by attribute
	 * 
	 * @param string The field name
	 * @param string The value of the field
	 * @return array
	 */
	function getItems($attribute, $value)
	{
		$items = array ();
		foreach ($this->_menuitems as $item)
		{
			if ($item->$attribute == $value) {
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 * Getter for the menu array
	 * 
	 * @return array
	 */
	function getMenu()
	{
		return $this->_thismenu;
	}

	/**
	 * Checks if the current menu, or the passed id, is the current menu
	 * 
	 * @param int A menu id (Itemid)
	 * @return boolean
	 */
	function isDefault( $id = 0 )
	{
		$menu = JMenu::getInstance();
		if ($id)
		{
			$item = $menu->getItem( $id );
		}
		else
		{
			$item = $menu->getCurrent();
		}
		if ($item)
		{
			return (boolean) $item->home;
		}
		else
		{
			return false;
		}
	}
}
?>