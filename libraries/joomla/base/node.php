<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Tree Node Class.
 *
 * @package     Joomla.Platform
 * @subpackage  Base
 * @since       11.1
 * @deprecated  12.3
 * @codeCoverageIgnore
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
	public function __construct()
	{
		JLog::add('JNode::__construct() is deprecated.', JLog::WARNING, 'deprecated');

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
	public function addChild(&$child)
	{
		JLog::add('JNode::addChild() is deprecated.', JLog::WARNING, 'deprecated');

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
	public function setParent(&$parent)
	{
		JLog::add('JNode::setParent() is deprecated.', JLog::WARNING, 'deprecated');

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
	public function &getChildren()
	{
		JLog::add('JNode::getChildren() is deprecated.', JLog::WARNING, 'deprecated');

		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed   JNode object with the parent or null for no parent
	 *
	 * @since   11.1
	 */
	public function &getParent()
	{
		JLog::add('JNode::getParent() is deprecated.', JLog::WARNING, 'deprecated');

		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return   boolean  True if there are children
	 *
	 * @since    11.1
	 */
	public function hasChildren()
	{
		JLog::add('JNode::hasChildren() is deprecated.', JLog::WARNING, 'deprecated');

		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   11.1
	 */
	public function hasParent()
	{
		JLog::add('JNode::hasParent() is deprecated.', JLog::WARNING, 'deprecated');

		return $this->getParent() != null;
	}
}
