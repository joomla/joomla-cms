<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.node');

/**
 * Tree Class.
 *
 * @package     Joomla.Platform
 * @subpackage  Base
 * @since       11.1
 */
class JTree extends JObject
{
	/**
	 * Root node
	 * @var
	 * @since  11.1
	 */
	protected $_root = null;

	/**
	 * Current working node
	 * @var
	 * @since  11.1
	 */
	protected $_current = null;

	/**
	 * Constructor
	 *
	 * @return  JTree
	 *
	 * @since   11.1
	 */
	function __construct()
	{
		$this->_root = new JNode('ROOT');
		$this->_current = & $this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   array   $node.
	 * @param   boolean $setCurrent
	 *
	 * @return  mixed
	 * @since   11.1
	 */
	function addChild(&$node, $setCurrent = false)
	{
		$this->_current->addChild($node);
		if ($setCurrent) {
			$this->_current = &$node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @return
	 * @since   11.1
	 */
	function getParent()
	{
		$this->_current = &$this->_current->getParent();
	}

	/**
	 * Method to get the parent
	 *
	 * @return
	 * @since   11.1
	 */
	function reset()
	{
		$this->_current = &$this->_root;
	}
}
