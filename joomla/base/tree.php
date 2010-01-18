<?php
/**
 * @version		$Id:tree.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Tree Class.
 *
 * @package 	Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JTree extends JObject
{
	/**
	 * Root node
	 */
	var $_root = null;

	/**
	 * Current working node
	 */
	var $_current = null;

	function __construct()
	{
		$this->_root = new JNode('ROOT');
		$this->_current = & $this->_root;
	}

	function addChild(&$node, $setCurrent = false)
	{
		$this->_current->addChild($node);
		if ($setCurrent) {
			$this->_current = &$node;
		}
	}

	function getParent()
	{
		$this->_current = &$this->_current->getParent();
	}

	function reset()
	{
		$this->_current = &$this->_root;
	}
}

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
	 * Parent node
	 */
	var $_parent = null;

	/**
	 * Array of Children
	 */
	var $_children = array();

	function __construct()
	{
		return true;
	}

	function addChild(&$node)
	{
		$node->setParent($this);
		$this->_children[] = & $node;
	}

	function &getParent()
	{
		return $this->_parent;
	}

	function setParent(&$node)
	{
		$this->_parent = & $node;
	}

	function hasChildren()
	{
		return count($this->_children);
	}

	function &getChildren()
	{
		return $this->_children;
	}
}
