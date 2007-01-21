<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * JMenu class
 *
 * @author Louis Landry   <louis.landry@joomla.org>
 * @author Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JMenu extends JObject
{
	/**
	 * Array to hold the menu items
	 *
	 * @access private
	 * @param array
	 */
	var $_items = array ();

	/**
	 * Identifier of the default menu item
	 *
	 * @access private
	 * @param integer
	 */
	var $_default = 0;


	/**
	 * Class constructor
	 *
	 * @access public
	 * @return boolean True on success
	 */
	function __construct()
	{
		$this->_items = $this->_load();

		$home = 0;
		$n = count($this->_items );
		foreach ($this->_items as $k => $item)
		{
			if ($item->home) {
				$this->_default = $item->id;
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
	function &getInstance()
	{
		static $instance;

		if (!$instance) {
			$instance = new JMenu();
		}

		return $instance;
	}

	/**
	 * Get menu item by id
	 *
	 * @access public
	 * @param int The item id
	 * @return mixed The item object, or null if not found
	 */
	function &getItem($id)
	{
		$result = null;
		if (isset($this->_items[$id])) {
			$result = &$this->_items[$id];
		}

		return $result;
	}

	/**
	 * Get menu item by id
	 *
	 * @access public
	 * @param int The item id
	 * @return object The item object
	 */
	function &getDefault()
	{
		$item =& $this->_items[$this->_default];
		return $item;
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
		foreach ($this->_items as $item)
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
		$ini = '';
		if ($menu =& $this->getItem($id)) {
			$ini = $menu->params;
		}
		$result = new JParameter( $ini );

		return $result;
	}

	/**
	 * Getter for the menu array
	 *
	 * @access public
	 * @param string $name The menu name
	 * @return array
	 */
	function getMenu($name = 'all')
	{
		$menu = array();

		foreach ($this->_items as $item ) {
			if ($item->menutype == $name || $name == 'all')  {
				$menu[] = $item;
			}
		}

		return $menu;
	}

	/**
	 * Method to check JMenu object authorization against an access control
	 * object and optionally an access extension object
	 *
	 * @access 	public
	 * @param	integer	$id			The menu id
	 * @param	integer	$accessid	The users access identifier
	 * @return	boolean	True if authorized
	 */
	function authorize($id, $accessid = 0)
	{
		$menu =& $this->getItem($id);
		return ($menu->access <= $accessid);
	}

	/**
	 * Loads the entire menu table into memory
	 *
	 * @access protected
	 * @return array
	 */
	function _load()
	{
		static $menus;

		if (isset ($menus)) {
			return $menus;
		}
		// Initialize some variables
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();
		$sql	= "SELECT *" .
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
