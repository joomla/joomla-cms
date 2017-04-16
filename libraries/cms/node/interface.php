<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Node
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * A Node interface for Joomla
 * If you are creating a system that might create something
 * similar to a tree, implement this interface for its nodes.
 *
 * @since  3.4
 */
interface JNodeInterface
{
	/**
	 * Set the parent of this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   mixed  $parent  JNodeInterface for the parent to be set or null
	 *
	 * @return  void
	 * 
	 * @since   3.4
	 */
	public function setParent($parent);

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   JNodeInterface  $child  The child to be added.
	 *
	 * @return  void
	 * 
	 * @since   3.4
	 */
	public function addChild($child);

	/**
	 * Remove a specific child
	 *
	 * @param   mixed  $child  An identifier for the child node
	 *
	 * @return  void
	 * 
	 * @since   3.4
	 */
	public function removeChild($child);

	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  JNodeInterface[]   The children
	 * 
	 * @since   3.4
	 */
	public function &getChildren($recursive = false);

	/**
	 * Get the parent of this node
	 *
	 * @return  JNodeInterface   The parent of this node or null
	 *
	 * @since   3.4
	 */
	public function getParent();

	/**
	 * Test if this node has children
	 *
	 * @return  boolean   True if there is a child
	 *
	 * @since   3.4
	 */
	public function hasChildren();

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean   True if there is a parent
	 *
	 * @since   3.4
	 */
	public function hasParent();
}
