<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Tree\ImmutableNodeInterface;
use Joomla\Registry\Registry;

/**
 * Object representing a menu item
 *
 * @since  3.7.0
 * @note   This class will no longer extend stdClass in Joomla 4
 */
class MenuItem extends \stdClass implements ImmutableNodeInterface
{
	/**
	 * Primary key
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $id;

	/**
	 * The type of menu this item belongs to
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $menutype;

	/**
	 * The display title of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $title;

	/**
	 * The SEF alias of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $alias;

	/**
	 * A note associated with the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $note;

	/**
	 * The computed path of the menu item based on the alias field, this is populated from the `path` field in the `#__menu` table
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $route;

	/**
	 * The actual link the menu item refers to
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $link;

	/**
	 * The type of link
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $type;

	/**
	 * The relative level in the tree
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $level;

	/**
	 * The assigned language for this item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $language;

	/**
	 * The click behaviour of the link
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $browserNav;

	/**
	 * The access level required to view the menu item
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $access;

	/**
	 * The menu item parameters
	 *
	 * @var    string|Registry
	 * @since  3.7.0
	 * @note   This field is protected to require reading this field to proxy through the getter to convert the params to a Registry instance
	 */
	protected $params;

	/**
	 * Indicates if this menu item is the home or default page
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $home;

	/**
	 * The image of the menu item
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $img;

	/**
	 * The optional template style applied to this menu item
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $template_style_id;

	/**
	 * The extension ID of the component this menu item is for
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $component_id;

	/**
	 * The parent menu item in the menu tree
	 *
	 * @var    integer
	 * @since  3.7.0
	 */
	public $parent_id;

	/**
	 * The name of the component this menu item is for
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	public $component;

	/**
	 * The tree of parent menu items
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	public $tree = array();

	/**
	 * An array of the query string values for this item
	 *
	 * @var    array
	 * @since  3.7.0
	 */
	public $query = array();

	/**
	 * The constructor of this node to retrieve other parts of the tree
	 *
	 * @var    AbstractMenu
	 * @since  __DEPLOY_VERSION__
	 */
	protected $constructor;

	/**
	 * Class constructor
	 *
	 * @param   array  $data  The menu item data to load
	 *
	 * @since   3.7.0
	 */
	public function __construct($data = array())
	{
		foreach ((array) $data as $key => $value)
		{
			$this->$key = $value;
		}
	}

	/**
	 * Method to clean up the object before serialisation
	 *
	 * @return  array  An array of object vars
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __sleep()
	{
		$this->constructor = null;

		return array_keys(get_object_vars($this));
	}

	/**
	 * Method to set the constructor of this node to retrieve other parts of the tree
	 *
	 * @param   AbstractMenu  $constructor  Constructor
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setMenuConstructor(AbstractMenu $constructor)
	{
		$this->constructor = $constructor;
	}

	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  NodeInterface[]  The children
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function &getChildren($recursive = false)
	{
		$children = $this->constructor->getItems('parent_id', $this->id);

		if ($recursive)
		{
			$items = array();

			foreach ($children as $child)
			{
				$items[] = $child;
				$items = array_merge($items, $child->getChildren(true));
			}

			return $items;
		}

		return $children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  NodeInterface|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getParent()
	{
		if ($this->parent_id != 1)
		{
			return $this->constructor->getItem($this->parent_id);
		}

		return null;
	}

	/**
	 * Get the root of the tree
	 * 
	 * @return  NodeInterface
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRoot()
	{
		$root = $this->getParent();

		if (!$root)
		{
			return $this;
		}

		while ($root->getParent())
		{
			$root = $root->getParent();
		}

		return $root;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there is a child
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasChildren()
	{
		return (bool) count($this->getChildren());
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasParent()
	{
		return $this->parent_id > 1 ? true : false;
	}

	/**
	 * Returns the right or left sibling of a node
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  NodeInterface|null  NodeInterface object of the sibling.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSibling($right = true)
	{
		$children = $this->constructor->getItems('parent_id', $this->parent_id);

		$prev = null;
		$found = false;

		foreach ($children as $child)
		{
			// The previous child is our current node and we want the right node
			if ($found)
			{
				return $child;
			}

			// We found the current node
			if ($child->id == $this->id)
			{
				// We want the left node
				if (!$right)
				{
					return $prev;
				}

				$found = true;
			}

			$prev = $child;
		}

		return null;
	}

	/**
	 * Returns the menu item parameters
	 *
	 * @return  Registry
	 *
	 * @since   3.7.0
	 */
	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			try
			{
				$this->params = new Registry($this->params);
			}
			catch (\RuntimeException $e)
			{
				/*
				 * Joomla shipped with a broken sample json string for 4 years which caused fatals with new
				 * error checks. So for now we catch the exception here - but one day we should remove it and require
				 * valid JSON.
				 */
				$this->params = new Registry;
			}
		}

		return $this->params;
	}

	/**
	 * Sets the menu item parameters
	 *
	 * @param   Registry|string  $params  The data to be stored as the parameters
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 *
	 * @since   3.7.0
	 * @deprecated  4.0
	 */
	public function get($property, $default = null)
	{
		if (isset($this->$property))
		{
			return $this->$property;
		}

		return $default;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set.
	 *
	 * @return  mixed  Previous value of the property.
	 *
	 * @since   3.7.0
	 * @deprecated  4.0
	 */
	public function set($property, $value = null)
	{
		$previous = $this->$property ?? null;
		$this->$property = $value;

		return $previous;
	}
}
