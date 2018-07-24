<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Menu class
 *
 * @since  1.5
 * @note   Will become abstract in Joomla 4
 */
class AbstractMenu
{
	/**
	 * Array to hold the menu items
	 *
	 * @var    MenuItem[]
	 * @since  1.5
	 * @deprecated  4.0  Will convert to $items
	 */
	protected $_items = array();

	/**
	 * Identifier of the default menu item
	 *
	 * @var    integer
	 * @since  1.5
	 * @deprecated  4.0  Will convert to $default
	 */
	protected $_default = array();

	/**
	 * Identifier of the active menu item
	 *
	 * @var    integer
	 * @since  1.5
	 * @deprecated  4.0  Will convert to $active
	 */
	protected $_active = 0;

	/**
	 * Menu instances container.
	 *
	 * @var    AbstractMenu[]
	 * @since  1.7
	 */
	protected static $instances = array();

	/**
	 * User object to check access levels for
	 *
	 * @var    \JUser
	 * @since  3.5
	 */
	protected $user;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  An array of configuration options.
	 *
	 * @since   1.5
	 */
	public function __construct($options = array())
	{
		// Load the menu items
		$this->load();

		foreach ($this->_items as $item)
		{
			if ($item->home)
			{
				$this->_default[trim($item->language)] = $item->id;
			}
		}

		$this->user = isset($options['user']) && $options['user'] instanceof \JUser ? $options['user'] : \JFactory::getUser();
	}

	/**
	 * Returns a Menu object
	 *
	 * @param   string  $client   The name of the client
	 * @param   array   $options  An associative array of options
	 *
	 * @return  AbstractMenu  A menu object.
	 *
	 * @since   1.5
	 * @throws  \Exception
	 */
	public static function getInstance($client, $options = array())
	{
		if (empty(self::$instances[$client]))
		{
			// Create a Menu object
			$classname = 'JMenu' . ucfirst($client);

			if (!class_exists($classname))
			{
				// @deprecated 4.0 Everything in this block is deprecated but the warning is only logged after the file_exists
				// Load the menu object
				$info = \JApplicationHelper::getClientInfo($client, true);

				if (is_object($info))
				{
					$path = $info->path . '/includes/menu.php';

					\JLoader::register($classname, $path);

					if (class_exists($classname))
					{
						\JLog::add('Non-autoloadable Menu subclasses are deprecated, support will be removed in 4.0.', \JLog::WARNING, 'deprecated');
					}
				}
			}

			if (!class_exists($classname))
			{
				throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_MENU_LOAD', $client), 500);
			}

			self::$instances[$client] = new $classname($options);
		}

		return self::$instances[$client];
	}

	/**
	 * Get menu item by id
	 *
	 * @param   integer  $id  The item id
	 *
	 * @return  MenuItem|null  The item object if the ID exists or null if not found
	 *
	 * @since   1.5
	 */
	public function getItem($id)
	{
		$result = null;

		if (isset($this->_items[$id]))
		{
			$result = &$this->_items[$id];
		}

		return $result;
	}

	/**
	 * Set the default item by id and language code.
	 *
	 * @param   integer  $id        The menu item id.
	 * @param   string   $language  The language code (since 1.6).
	 *
	 * @return  boolean  True if a menu item with the given ID exists
	 *
	 * @since   1.5
	 */
	public function setDefault($id, $language = '*')
	{
		if (isset($this->_items[$id]))
		{
			$this->_default[$language] = $id;

			return true;
		}

		return false;
	}

	/**
	 * Get the default item by language code.
	 *
	 * @param   string  $language  The language code, default value of * means all.
	 *
	 * @return  MenuItem|null  The item object or null when not found for given language
	 *
	 * @since   1.5
	 */
	public function getDefault($language = '*')
	{
		if (array_key_exists($language, $this->_default))
		{
			return $this->_items[$this->_default[$language]];
		}

		if (array_key_exists('*', $this->_default))
		{
			return $this->_items[$this->_default['*']];
		}

		return;
	}

	/**
	 * Set the default item by id
	 *
	 * @param   integer  $id  The item id
	 *
	 * @return  MenuItem|null  The menu item representing the given ID if present or null otherwise
	 *
	 * @since   1.5
	 */
	public function setActive($id)
	{
		if (isset($this->_items[$id]))
		{
			$this->_active = $id;

			return $this->_items[$id];
		}

		return;
	}

	/**
	 * Get menu item by id.
	 *
	 * @return  MenuItem|null  The item object if an active menu item has been set or null
	 *
	 * @since   1.5
	 */
	public function getActive()
	{
		if ($this->_active)
		{
			return $this->_items[$this->_active];
		}

		return;
	}

	/**
	 * Gets menu items by attribute
	 *
	 * @param   mixed    $attributes  The field name(s).
	 * @param   mixed    $values      The value(s) of the field. If an array, need to match field names
	 *                                each attribute may have multiple values to lookup for.
	 * @param   boolean  $firstonly   If true, only returns the first item found
	 *
	 * @return  MenuItem|MenuItem[]  An array of menu item objects or a single object if the $firstonly parameter is true
	 *
	 * @since   1.5
	 */
	public function getItems($attributes, $values, $firstonly = false)
	{
		$items = array();
		$attributes = (array) $attributes;
		$values = (array) $values;
		$count = count($attributes);

		foreach ($this->_items as $item)
		{
			if (!is_object($item))
			{
				continue;
			}

			$test = true;

			for ($i = 0; $i < $count; $i++)
			{
				if (is_array($values[$i]))
				{
					if (!in_array($item->{$attributes[$i]}, $values[$i]))
					{
						$test = false;
						break;
					}
				}
				else
				{
					if ($item->{$attributes[$i]} != $values[$i])
					{
						$test = false;
						break;
					}
				}
			}

			if ($test)
			{
				if ($firstonly)
				{
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
	 * @param   integer  $id  The item id
	 *
	 * @return  Registry
	 *
	 * @since   1.5
	 */
	public function getParams($id)
	{
		if ($menu = $this->getItem($id))
		{
			return $menu->params;
		}

		return new Registry;
	}

	/**
	 * Getter for the menu array
	 *
	 * @return  MenuItem[]
	 *
	 * @since   1.5
	 */
	public function getMenu()
	{
		return $this->_items;
	}

	/**
	 * Method to check Menu object authorization against an access control object and optionally an access extension object
	 *
	 * @param   integer  $id  The menu id
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function authorise($id)
	{
		$menu = $this->getItem($id);

		if ($menu)
		{
			return in_array((int) $menu->access, $this->user->getAuthorisedViewLevels());
		}

		return true;
	}

	/**
	 * Loads the menu items
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function load()
	{
		return array();
	}
}
