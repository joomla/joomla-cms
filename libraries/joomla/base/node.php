<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Tree Node Class.
 *
 * @package     Joomla.Platform
 * @subpackage  Base
 * @since       11.1
 */
class JNode extends JObject
{

	/**
	 * @var    object  Parent node.
	 * @since  11.1
	 */
	protected $_parent = null;

	/**
	 * @var    array  Array of Children
	 * @since  11.1
	 */
	protected $_children = array();

	/**
	 * Constructor
	 *
	 * @since  11.1
	 */
	function __construct()
	{
		return true;
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param JNode the child to be added
	 *
	 * @return
	 * @since       11.1
	 */
	function addChild(&$child)
	{
		if ($child instanceof Jnode)
		{
			$child->setParent($this);
		}
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param    JNode|null  The parent to be set
	 *
	 * @return
	 * @since    11.1
	 */
	function setParent(&$parent)
	{
		if ($parent instanceof JNode || is_null($parent))
		{
			$hash = spl_object_hash($this);
			if (!is_null($this->_parent))
			{
				unset($this->_parent->children[$hash]);
			}
			if (!is_null($parent))
			{
				$parent->_children[$hash] = & $this;
			}
			$this->_parent = & $parent;
		}
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array    The children
	 * @since   11.1
	 */
	function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed   JNode object with the parent or null for no parent
	 * @since   11.1
	 */
	function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return   bool
	 * @since    11.1
	 */
	function hasChildren()
	{
		return (bool)count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  bool
	 * @since   11.1
	 */
	function hasParent()
	{
		return $this->getParent() != null;
	}
}