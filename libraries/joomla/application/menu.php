<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JMenu class
 *
 * @package		Joomla.Platform
 * @subpackage	Application
 * @since		11.1
 */
class JMenu extends JObject
{
	/**
	 * Array to hold the menu items
	 *
	 * @param	array
	 */
	protected $_items = array ();

	/**
	 * Identifier of the default menu item
	 *
	 * @param	integer
	 */
	protected $_default = array();

	/**
	 * Identifier of the active menu item
	 *
	 * @param	integer
	 */
	protected $_active = 0;

	/**
	 * Class constructor
	 *
	 * @param	array	$options	An array of configuration options.
	 *
	 * @return	JMenu
	 * @since	11.1
	 */
	public function __construct($options = array())
	{
		// Load the menu items
		$this->load();

		foreach ($this->_items as $k => $item)
		{
			if ($item->home) {
				$this->_default[$item->language] = $item->id;
			}

			// Decode the item params
			$result = new JRegistry;
			$result->loadJSON($item->params);
			$item->params = $result;
		}
	}

	/**
	 * Returns a JMenu object
	 *
	 * @param	string	The name of the client
	 * @param	array	An associative array of options
	 *
	 * @return	JMenu	A menu object.
	 * @since	11.1
	 */
	public static function getInstance($client, $options = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$client])) {
			//Load the router object
			$info = JApplicationHelper::getClientInfo($client, true);

			$path = $info->path.'/includes/menu.php';
			if (file_exists($path)) {
				require_once $path;

				// Create a JPathway object
				$classname = 'JMenu'.ucfirst($client);
				$instance = new $classname($options);
			}
			else {
				//$error = JError::raiseError(500, 'Unable to load menu: '.$client);
				//TODO: Solve this
				$error = null;
				return $error;
			}

			$instances[$client] = & $instance;
		}

		return $instances[$client];
	}

	/**
	 * Get menu item by id
	 *
	 * @param	int		$id	The item id
	 *
	 * @return	mixed	The item object, or null if not found
	 * @since	11.1
	 */
	public function getItem($id)
	{
		$result = null;
		if (isset($this->_items[$id])) {
			$result = &$this->_items[$id];
		}

		return $result;
	}

	/**
	 * Set the default item by id and language code.
	 *
	 * @param	int		$id			The menu item id.
	 * @param	string	$language	The language cod (since 1.6).
	 *
	 * @return	boolean	True, if succesfull
	 * @since	11.1
	 */
	public function setDefault($id, $language='')
	{
		if (isset($this->_items[$id])) {
			$this->_default[$language] = $id;
			return true;
		}

		return false;
	}

	/**
	 * Get menu item by id
	 *
	 * @param	string	$language	The language code.
	 *
	 * @return	object	The item object
	 * @since	11.1
	 */
	function getDefault($language='*')
	{
		if (array_key_exists($language, $this->_default)) {
			return $this->_items[$this->_default[$language]];
		}
		else if (array_key_exists('*', $this->_default)) {
			return $this->_items[$this->_default['*']];
		}
		else {
			return 0;
		}
	}

	/**
	 * Set the default item by id
	 *
	 * @param	int		$id	The item id
	 *
	 * @return	mixed	If successfull the active item, otherwise null
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
	 * Get menu item by id.
	 *
	 * @return	object	The item object.
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
	 * @param	string	$attributes	The field name
	 * @param	string	$values		The value of the field
	 * @param	boolean	$firstonly	If true, only returns the first item found
	 *
	 * @return	array
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$items = null;
		$attributes = (array) $attributes;
		$values = (array) $values;

		foreach ($this->_items as $item)
		{
			if (!is_object($item)) {
				continue;
			}

			$test = true;
			for ($i=0, $count = count($attributes); $i < $count; $i++)
			{
				if (is_array($values[$i])) {
					if (!in_array($item->$attributes[$i], $values[$i])) {
						$test = false;
						break;
					}
				}
				else {
					if ($item->$attributes[$i] != $values[$i]) {
						$test = false;
						break;
					}
				}
			}

			if ($test) {
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
	 * @param	int		$id		The item id
	 *
	 * @return	object	A JRegistry object
	 */
	public function getParams($id)
	{
		if ($menu = $this->getItem($id)) {
			return $menu->params;
		}
		else {
			return new JRegistry;
		}
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
	 * @param	integer	$id	The menu id
	 * @return	boolean	True if authorized
	 * @since	11.1
	 */
	public function authorise($id)
	{
		$menu	= $this->getItem($id);
		$user	= JFactory::getUser();

		if ($menu) {
			return in_array((int) $menu->access, $user->getAuthorisedViewLevels());
		}
		else {
			return true;
		}
	}

	/**
	 * Loads the menu items
	 *
	 * @return	array
	 */
	public function load()
	{
		return array();
	}
}