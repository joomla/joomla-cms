<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tree;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a node class
 *
 * @since  4.0.0
 */
interface NodeInterface extends ImmutableNodeInterface
{
    /**
     * Set the parent of this node
     *
     * If the node already has a parent, the link is unset
     *
     * @param   NodeInterface  $parent  NodeInterface for the parent to be set
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setParent(NodeInterface $parent);

    /**
     * Add child to this node
     *
     * If the child already has a parent, the link is unset
     *
     * @param   NodeInterface  $child  The child to be added.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addChild(NodeInterface $child);

    /**
     * Remove a specific child
     *
     * @param   NodeInterface  $child  Child to remove
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function removeChild(NodeInterface $child);

    /**
     * Function to set the left or right sibling of a node
     *
     * @param   NodeInterface  $sibling  NodeInterface object for the sibling
     * @param   boolean        $right    If set to false, the sibling is the left one
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setSibling(NodeInterface $sibling, $right = true);
}
