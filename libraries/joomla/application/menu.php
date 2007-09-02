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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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
	 * Identifier of the active menu item
	 *
	 * @access private
	 * @param integer
	 */
	var $_active = 0;


	/**
	 * Class constructor
	 *
	 * @access public
	 * @return boolean True on success
	 */
	function __construct($options = array())
	{
		$this->_items = $this->_load();

		foreach ($this->_items as $k => $item)
		{
			if ($item->home) {
				$this->_default = $item->id;
			}
		}
	}
	
	/**
	 * Returns a reference to a JMenu object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $menu = &JSite::getMenu();</pre>
	 *
	 * @access	public
	 * @param   string  $client  The name of the client
	 * @param array     $options An associative array of options
	 * @return JMenu 	A menu object.
	 * @since	1.5
	 */
	function &getInstance($client, $options = array())
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}
		
		if (empty($instances[$client]))
		{
			//Load the router object
			$info =& JApplicationHelper::getClientInfo($client, true);
			
			$path = $info->path.DS.'includes'.DS.'menu.php';
			if(file_exists($path)) 
			{
				require_once $path;
				
				// Create a JPathway object
				$classname = 'JMenu'.ucfirst($client);
				$instance = new $classname($options);
			}	 
			else 
			{
				$error = new JException( E_ERROR, 500, 'Unable to load menu: '.$client);
				return $error;
			}
			
			$instances[$client] = & $instance;
		}
		
		return $instances[$client];
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
	 * Set the default item by id
	 *
	 * @param int The item id
	 * @access public
	 * @return True, if succesfull
	 */
	function setDefault($id)
	{
		if(isset($this->_items[$id])) {
			$this->_default = $id;
			return true;
		}

		return false;
	}

	/**
	 * Get menu item by id
	 *
	 * @access public
	 *
	 * @return object The item object
	 */
	function &getDefault()
	{
		$item =& $this->_items[$this->_default];
		return $item;
	}

	/**
	 * Set the default item by id
	 *
	 * @param int The item id
	 * @access public
	 * @return If successfull the active item, otherwise null
	 */
	function &setActive($id)
	{
		if(isset($this->_items[$id]))
		{
			$this->_active = $id;
			$result = &$this->_items[$id];
			return $result;
		}

		$result = null;
		return $result;
	}

	/**
	 * Get menu item by id
	 *
	 * @access public
	 *
	 * @return object The item object
	 */
	function &getActive()
	{
		if ($this->_active) {
			$item =& $this->_items[$this->_active];
			return $item;
		}

		$result = null;
		return $result;
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @access public
	 * @param string 	The field name
	 * @param string 	The value of the field
	 * @param boolean 	If true, only returns the first item found
	 * @return array
	 */
	function getItems($attribute, $value, $firstonly = false)
	{
		$items = array ();
		foreach ($this->_items as  $item)
		{
			if ( ! is_object($item) )
				continue;

			if ($item->$attribute == $value)
			{
				if($firstonly) {
					return $item;
				}

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
	function getMenu() {
		return $this->_items;
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
		return ((isset($menu->access) ? $menu->access : 0) <= $accessid);
	}

	/**
	 * Loads the entire menu table into memory
	 *
	 * @access protected
	 * @return array
	 */
	function _load()
	{
		return array();
	}
}