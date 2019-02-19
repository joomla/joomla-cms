<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tree;

defined('JPATH_PLATFORM') or die;

/**
 * Interface for an immutable node class
 *
 * @since  __DEPLOY_VERSION__
 */
interface ImmutableNodeInterface
{
	/**
	 * Get the children of this node
	 *
	 * @param   boolean  $recursive  False by default
	 *
	 * @return  NodeInterface[]  The children
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function &getChildren($recursive = false);

	/**
	 * Get the parent of this node
	 *
	 * @return  NodeInterface|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getParent();

	/**
	 * Get the root of the tree
	 * 
	 * @return  ImmutableNodeInterface
	 * 
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRoot();

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there is a child
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasChildren();

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasParent();

	/**
	 * Returns the right or left sibling of a node
	 *
	 * @param   boolean  $right  If set to false, returns the left sibling
	 *
	 * @return  NodeInterface|null  NodeInterface object of the sibling.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSibling($right = true);
}
