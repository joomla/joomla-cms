<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JMenu class
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JMenu extends JObject
{
	/**
	 * Array to hold the menu items
	 *
	 * @param array
	 */
	protected $_items = array ();

	/**
	 * Identifier of the default menu item
	 *
	 * @param integer
	 */
	protected $_default = 0;

	/**
	 * Identifier of the active menu item
	 *
	 * @param integer
	 */
	protected $_active = 0;

	/**
	 * Class constructor
	 *
	 * @return boolean True on success
	 */
	public function __construct($options = array())
	{
		$this->load(); //load the menu items

		foreach ($this->_items as $k => $item) {
			if ($item->home) {
				$this->_default = $item->id;
			}
		}
	}

	/**
	 * Returns a JMenu object
	 *
	 * @param	string	The name of the client
	 * @param	array	An associative array of options
	 * @return	JMenu	A menu object.
	 * @since	1.5
	 */
	public static function getInstance($client, $options = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$client])) {
			//Load the router object
			$info = &JApplicationHelper::getClientInfo($client, true);

			$path = $info->path.'/includes/menu.php';
			if (file_exists($path)) {
				require_once $path;

				// Create a JPathway object
				$classname = 'JMenu'.ucfirst($client);
				$instance = new $classname($options);
			} else {
				//$error = JError::raiseError(500, 'Unable to load menu: '.$client);
				$error = null; //Jinx : need to fix this
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
	function getItem($id)
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
	 * @return True, if succesfull
	 */
	public function setDefault($id)
	{
		if (isset($this->_items[$id])) {
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
	function getDefault()
	{
		return $this->_items[$this->_default];
	}

	/**
	 * Set the default item by id
	 *
	 * @param int The item id
	 * @return If successfull the active item, otherwise null
	 */
	public function setActive($id)
	{
		if (isset($this->_items[$id])) {
			$this->_active = $id;
			$result = &$this->_items[$id];
			return $result;
		}

		return null;
	}

	/**
	 * Get menu item by id
	 *
	 * @return object The item object
	 */
	public function getActive()
	{
		if ($this->_active) {
			$item = &$this->_items[$this->_active];
			return $item;
		}

		return null;
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @param	string	The field name
	 * @param	string	The value of the field
	 * @param	boolean	If true, only returns the first item found
	 *
	 * @return	array
	 */
	public function getItems($attribute, $value, $firstonly = false)
	{
		$items = null;

		foreach ($this->_items as $item) {
			if (!is_object($item)) {
				continue;
			}

			if ($item->$attribute == $value) {
				if ($firstonly) {
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
	 * @param	int		The item id
	 * @return	object	A JParameter object
	 */
	public function getParams($id)
	{
		$ini = '';
		if ($menu = &$this->getItem($id)) {
			$ini = $menu->params;
		}

		return new JParameter($ini);
	}

	/**
	 * Getter for the menu array
	 *
	 * @return array
	 */
	public function getMenu()
	{
		return $this->_items;
	}

	/**
	 * Method to check JMenu object authorization against an access control
	 * object and optionally an access extension object
	 *
	 * @param	integer	$id			The menu id
	 * @return	boolean	True if authorized
	 */
	public function authorise($id)
	{
		$menu	= &$this->getItem($id);
		$user	= &JFactory::getUser();

		if ($menu) {
			return in_array((int) $menu->access, $user->authorisedLevels());
		} else {
			return true;
		}
	}

	/**
	 * Loads the menu items
	 *
	 * @abstract
	 * @return array
	 */
	public function load()
	{
		return array();
	}
}