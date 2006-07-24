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
 * @package Joomla.Framework
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
		foreach ($this->_menuitems as $item) 
		{
			if ($item->menutype == $name || $name == 'all') {
				$this->_thismenu[] = $item;
				if ($item->home)
				{
					$home = $item->id;
				}
			}
		}
	}
	
	/**
	 * Returns a reference to the global JMenu object, only creating it if it
	 * doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $menu = &JMenu::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JMenu	The Menu object.
	 * @since	1.5
	 */
	function &getInstance($id = 'all')
	{
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$id])) {
			$instances[$id] =& new JMenu($id);
		}

		$instance = $instances[$id];
		return $instance;
	}

	/**
	 * Get menu item by id
	 * 
	 * @access public
	 * @param int The item id
	 * @return mixed The item, or false if not found
	 */
	function &getItem($id)
  {
  $result = null;
  if (isset($this->_menuitems[$id])) {
   $result = &$this->_menuitems[$id];
  } 
  
  return $result;
  }

	/**
	 * Gets menu items by attribute
	 * 
	 * @access public
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
	 * Gets the parameter object for a certain menu item
	 * 
	 * @access public
	 * @param int The item id
	 * @return object A JParameter object
	 */
	function &getParams($id)
	{
		if($menu =& $this->getItem($id)) {
			return new JParameter( $menu->params );
		} 
		
		return null;
	}

	/**
	 * Getter for the menu array
	 * 
	 * @return array
	 */
	function getMenu() {
		return $this->_thismenu;
	}

	/**
	 * Method to check JMenu object authorization against an access control
	 * object and optionally an access extension object
	 *
	 * @access 	public
	 * @param	integer	$itemid		The itemid
	 * @param	object	$user		The user object
	 * @return	boolean	True if authorized
	 */
	function authorize($itemid, &$user) 
	{
		// Initialize variables
		$results	= array();
		$access 	= 0;
		
		foreach ($results as $result) {
			$access = max( $access, $result->access );
		}
	
		return ($access <= $user->get('usertype'));
	}
	
	/**
	 * Loads the entire menu table into memory
	 * 
	 * @access protected
	 * @return array
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
}
?>