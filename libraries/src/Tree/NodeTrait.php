<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tree;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a Node Interface Trait Class.
 *
 * @since  4.0.0
 */
trait NodeTrait
{
    use ImmutableNodeTrait;

    /**
     * Set the parent of this node
     *
     * If the node already has a parent, the link is unset
     *
     * @param   NodeInterface|null  $parent  NodeInterface for the parent to be set or null
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setParent(NodeInterface $parent)
    {
        if (!\is_null($this->_parent)) {
            $key = array_search($this, $this->_parent->_children);
            unset($this->_parent->_children[$key]);
        }

        $this->_parent = $parent;

        $this->_parent->_children[] = &$this;

        if (\count($this->_parent->_children) > 1) {
            end($this->_parent->_children);
            $this->_leftSibling = prev($this->_parent->_children);
            $this->_leftSibling->_rightSibling = $this;
        }
    }

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
    public function addChild(NodeInterface $child)
    {
        $child->setParent($this);
    }

    /**
     * Remove a specific child
     *
     * @param   NodeInterface  $child  Child to remove
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function removeChild(NodeInterface $child)
    {
        $key = array_search($child, $this->_children);
        unset($this->_children[$key]);
    }

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
    public function setSibling(NodeInterface $sibling, $right = true)
    {
        if ($right) {
            $this->_rightSibling = $sibling;
        } else {
            $this->_leftSibling = $sibling;
        }
    }
}
