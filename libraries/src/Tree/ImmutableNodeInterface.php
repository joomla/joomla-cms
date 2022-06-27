<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tree;

/**
 * Interface for an immutable node class
 *
 * @since  4.0.0
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
     * @since   4.0.0
     */
    public function &getChildren($recursive = false);

    /**
     * Get the parent of this node
     *
     * @return  NodeInterface|null
     *
     * @since   4.0.0
     */
    public function getParent();

    /**
     * Get the root of the tree
     *
     * @return  ImmutableNodeInterface
     *
     * @since   4.0.0
     */
    public function getRoot();

    /**
     * Test if this node has children
     *
     * @return  boolean  True if there is a child
     *
     * @since   4.0.0
     */
    public function hasChildren();

    /**
     * Test if this node has a parent
     *
     * @return  boolean  True if there is a parent
     *
     * @since   4.0.0
     */
    public function hasParent();

    /**
     * Returns the right or left sibling of a node
     *
     * @param   boolean  $right  If set to false, returns the left sibling
     *
     * @return  NodeInterface|null  NodeInterface object of the sibling.
     *
     * @since   4.0.0
     */
    public function getSibling($right = true);
}
