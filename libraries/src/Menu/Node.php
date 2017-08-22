<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Menu;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * A Node for MenuTree
 *
 * @see    Tree
 *
 * @since  3.8.0
 */
class Node
{
	/**
	 * Node Id
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $id = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string
	 *
	 * @since   3.8.0
	 */
	protected $class = null;

	/**
	 * Whether this node is active
	 *
	 * @var  bool
	 *
	 * @since   3.8.0
	 */
	protected $active = false;

	/**
	 * Additional custom node params
	 *
	 * @var  Registry
	 *
	 * @since   3.8.0
	 */
	protected $params;

	/**
	 * Parent node object
	 *
	 * @var  Node
	 *
	 * @since   3.8.0
	 */
	protected $parent = null;

	/**
	 * Array of Children node objects
	 *
	 * @var  Node[]
	 *
	 * @since   3.8.0
	 */
	protected $children = array();

	/**
	 * Constructor
	 *
	 * @since   3.8.0
	 */
	public function __construct()
	{
		$this->params = new Registry;
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   Node  $child  The child to be added
	 *
	 * @return  Node  The new added child
	 *
	 * @since   3.8.0
	 */
	public function addChild(Node $child)
	{
		$hash = spl_object_hash($child);

		if (isset($child->parent))
		{
			$child->parent->removeChild($child);
		}

		$child->parent         = $this;
		$this->children[$hash] = $child;

		return $child;
	}

	/**
	 * Remove a child from this node
	 *
	 * If the child exists it is unset
	 *
	 * @param   Node  $child  The child to be added
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function removeChild(Node $child)
	{
		$hash = spl_object_hash($child);

		if (isset($this->children[$hash]))
		{
			$child->parent = null;

			unset($this->children[$hash]);
		}
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  bool  True if there is a parent
	 *
	 * @since   3.8.0
	 */
	public function hasParent()
	{
		return isset($this->parent);
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  Node  The Node object's parent or null for no parent
	 *
	 * @since   3.8.0
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  bool
	 *
	 * @since   3.8.0
	 */
	public function hasChildren()
	{
		return count($this->children) > 0;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  Node[]  The children
	 *
	 * @since   3.8.0
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * Find the current node depth in the tree hierarchy
	 *
	 * @return  int  The node level in the hierarchy, where ROOT == 0, First level menu item == 1, and so on.
	 *
	 * @since   3.8.0
	 */
	public function getLevel()
	{
		return $this->hasParent() ? $this->getParent()->getLevel() + 1 : 0;
	}

	/**
	 * Check whether the object instance node is the root node
	 *
	 * @return  bool
	 *
	 * @since   3.8.0
	 */
	public function isRoot()
	{
		return !$this->hasParent();
	}

	/**
	 * Set the active state on or off
	 *
	 * @param   bool  $active  The new active state
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function setActive($active)
	{
		$this->active = (bool) $active;
	}

	/**
	 * set the params array
	 *
	 * @param   Registry  $params  The params attributes
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function setParams(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Get the param value from the node params
	 *
	 * @param   string  $key  The param name
	 *
	 * @return  mixed
	 *
	 * @since   3.8.0
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	/**
	 * Get an attribute value
	 *
	 * @param   string  $name  The attribute name
	 *
	 * @return  mixed
	 *
	 * @since   3.8.0
	 */
	public function get($name)
	{
		switch ($name)
		{
			case 'id':
			case 'class':
			case 'active':
			case 'params':
				return $this->$name;
		}

		return null;
	}
}
