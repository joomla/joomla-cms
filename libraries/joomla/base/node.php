<?php

/**
 * @version		$Id: tree.php 6961 2007-03-15 16:06:53Z tcp $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Tree Node Class.
 *
 * @package 	Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JNode extends JObject
{

	/**
	 * @var Parent node
	 */
	protected $_parent = null;

	/**
	 * @var Array of Children
	 */
	protected $_children = array();

	/**
	 * Constructor
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
	 * @param JNode|null the parent to be setted
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
	 * @return array the children
	 */
	function &getChildren() 
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return JNode|null the parent
	 */
	function &getParent() 
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return bool
	 */
	function hasChildren() 
	{
		return count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return bool
	 */
	function hasParent() 
	{
		return $this->getParent() != null;
	}
}

