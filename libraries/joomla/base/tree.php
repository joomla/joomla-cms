<?php
/**
 * @version		$Id:tree.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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
	protected $_root = null;

	/**
	 * Current working node
	 */
	protected $_current = null;

	public function __construct()
	{
		$this->_root = new JNode('ROOT');
		$this->_current = & $this->_root;
	}

	public function addChild(&$node, $setCurrent = false)
	{
		$this->_current->addChild($node);
		if ($setCurrent) {
			$this->_current =& $node;
		}
	}

	public function getParent()
	{
		$this->_current =& $this->_current->getParent();
	}

	public function reset()
	{
		$this->_current =& $this->_root;
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
	protected $_parent = null;

	/**
	 * Array of Children
	 */
	protected $_children = array();

	public function __construct()
	{
		return true;
	}

	public function addChild( &$node )
	{
		$node->setParent($this);
		$this->_children[] = & $node;
	}

	public function &getParent()
	{
		return $this->_parent;
	}

	public function setParent( &$node )
	{
		$this->_parent = & $node;
	}

	public function hasChildren()
	{
		return count($this->_children);
	}

	public function &getChildren()
	{
		return $this->_children;
	}
}
