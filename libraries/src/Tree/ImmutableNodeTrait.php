<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tree;

\defined('JPATH_PLATFORM') or die;

/**
 * Defines the trait for an Immutable Node Class.
 *
 * @since  4.0.0
 */
trait ImmutableNodeTrait
{
	/**
	 * Parent node object
	 *
	 * @var    NodeInterface
	 * @since  1.6
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var    NodeInterface[]
	 * @since  1.6
	 */
	protected $_children = array();

	/**
	 * Node left of this one
	 *
	 * @var    NodeInterface
	 * @since  1.6
	 */
	protected $_leftSibling = null;

	/**
	 * Node right of this one
	 *
	 * @var    NodeInterface
	 * @since  1.6
	 */
	protected $_rightSibling = null;

	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  NodeInterface[]  The children
	 *
	 * @since   4.0.0
	 */
	public function &getChildren($recursive = false)
	{
		if ($recursive)
		{
			$items = array();

			foreach ($this->_children as $child)
			{
				$items[] = $child;
				$items = array_merge($items, $child->getChildren(true));
			}

			return $items;
		}

		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  NodeInterface|null
	 *
	 * @since   4.0.0
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Get the root of the tree
	 *
	 * @return  ImmutableNodeInterface
	 *
	 * @since   4.0.0
	 */
	public function getRoot()
	{
		$root = $this->getParent();

		if (!$root)
		{
			return $this;
		}

		while ($root->hasParent())
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
	 * @since   4.0.0
	 */
	public function hasChildren()
	{
		return (bool) \count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   4.0.0
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}

	/**
	 * Returns the right or left sibling of a node
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  NodeInterface|null  NodeInterface object of the sibling.
	 *
	 * @since   4.0.0
	 */
	public function getSibling($right = true)
	{
		if ($right)
		{
			return $this->_rightSibling;
		}
		else
		{
			return $this->_leftSibling;
		}
	}
}
