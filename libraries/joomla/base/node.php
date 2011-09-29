<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

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
	 * Parent node
	 * @var    object
	 *
	 * @since  11.1
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var    array
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
	 * @param   JNode  &$child  The child to be added
	 *
	 * @return  void
	 *
	 * @since   11.1
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
	 * @param   mixed  &$parent  The JNode for parent to be set or null
	 *
	 * @return  void
	 *
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
	 *
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
	 *
	 * @since   11.1
	 */
	function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return   boolean  True if there are chilren
	 *
	 * @since    11.1
	 */
	function hasChildren()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   11.1
	 */
	function hasParent()
	{
		return $this->getParent() != null;
	}
}