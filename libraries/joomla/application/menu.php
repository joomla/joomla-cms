<?php

/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JMenu class
 *
 * @author Louis Landry <louis@webimagery.net>
 * @package JoomlaFramework
 * @since 1.1
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
	 * Class constructor
	 * 
	 * @param string $name The menu name to load
	 * @return boolean True on success
	 * @since 1.1
	 */
	function __construct($name = 'all')
	{
		$this->_menuitems = $this->_load();

		foreach ($this->_menuitems as $item)
		{
			if ($item->menutype == $name || $name == 'all')
			{
				$this->_thismenu[] = $item;
			}
		}
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
	 * @since	1.1
	 */
	function getInstance($id = 'all')
	{
		static $instances;

		if (!isset ($instances))
		{
			$instances = array ();
		}

		if (empty ($instances[$id]))
		{
			$instances[$id] = new JMenu($id);
		}

		return $instances[$id];
	}

	function getItemid($id)
	{
		global $mainframe;

		$db = & $mainframe->getDBO();
		$Itemid = null;

		if (count($this->_menuitems))
		{

			/*
			 * Do we have a content item linked to the menu with this id?
			 */
			foreach ($this->_menuitems as $item)
			{
				if ($item->link == "index.php?option=com_content&task=view&id=$id")
				{
					return $item->id;
				}
			}

			/*
			 * Not a content item, so perhaps is it in a section that is linked
			 * to the menu?
			 */
			$query = "SELECT m.id " .
					"\n FROM #__content AS i" .
					"\n LEFT JOIN #__sections AS s ON i.sectionid = s.id" .
					"\n LEFT JOIN #__menu AS m ON m.componentid = s.id " .
					"\n WHERE (m.type = 'content_section' OR m.type = 'content_blog_section')" .
					"\n AND m.published = 1" .
					"\n AND i.id = $id";
			$db->setQuery($query);
			$Itemid = $db->loadResult();
			if ($Itemid != '')
			{
				return $Itemid;
			}

			/*
			 * Not a section either... is it in a category that is linked to the
			 * menu?
			 */
			$query = "SELECT m.id " .
					"\n FROM #__content AS i" .
					"\n LEFT JOIN #__categories AS c ON i.catid = c.id" .
					"\n LEFT JOIN #__menu AS m ON m.componentid = c.id " .
					"\n WHERE (m.type = 'content_blog_category' OR m.type = 'content_category')" .
					"\n AND m.published = 1" .
					"\n AND i.id = $id";
			$db->setQuery($query);
			$Itemid = $db->loadResult();
			if ($Itemid != '')
			{
				return $Itemid;
			}

			/*
			 * Once we have exhausted all our options for finding the Itemid in
			 * the content structure, lets see if maybe we have a global blog
			 * section in the menu we can put it under.
			 */
			foreach ($this->_menuitems as $item)
			{
				if ($item->type == "content_blog_section" && $item->componentid == "0")
				{
					return $item->id;
				}
			}
		}

		if ($Itemid != '')
		{
			return $Itemid;
		}
		else
		{
			return JRequest::getVar('Itemid', 9999, '', 'int');
		}
	}

	function getMenu()
	{
		return $this->_thismenu;
	}

	function getItem($id)
	{
		return $this->_menuitems[$id];
	}

	function getItems($attribute, $value)
	{
		$items = array ();
		foreach ($this->_menuitems as $item)
		{
			if ($item->$attribute == $value)
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	function _load()
	{
		global $mainframe;

		static $menus;

		if (isset ($menus))
		{
			return $menus;
		}
		/*
		 * Initialize some variables
		 */
		$db = & $mainframe->getDBO();
		$user = & $mainframe->getUser();

		$sql = "SELECT *" .
				"\n FROM #__menu" .
				"\n WHERE published = 1";

		$db->setQuery($sql);
		if (!($menus = $db->loadObjectList('id')))
		{
			JError::raiseWarning('SOME_ERROR_CODE', "Error loading Menus: ".$db->getErrorMsg());
			return false;
		}

		return $menus;
	}
}
?>