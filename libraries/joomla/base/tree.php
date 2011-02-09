<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Base
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.node');

/**
 * Tree Class.
 *
 * @package		Joomla.Platform
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

