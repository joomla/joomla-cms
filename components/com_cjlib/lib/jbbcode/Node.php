<?php

namespace JBBCode;

/**
 * A node within the document tree.
 *
 * Known subclasses: TextNode, ElementNode
 *
 * @author jbowens
 */
abstract class Node
{
    /* Pointer to the parent node of this node */
    protected $parent;

    /* The node id of this node */
    protected $nodeid;

    /**
     * Returns the node id of this node. (Not really ever used. Dependent upon the parse tree the node exists within.)
     *
     * @return this node's id
     */
    public function getNodeId()
    {
        return $this->nodeid;
    }

    /**
     * Returns this node's immediate parent.
     *
     * @return the node's parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Determines if this node has a parent.
     *
     * @return true if this node has a parent, false otherwise
     */
    public function hasParent()
    {
        return $this->parent != null;
    }

    /**
     * Returns true if this is a text node. Returns false otherwise.
     * (Overridden by TextNode to return true)
     *
     * @return true if this node is a text node
     */
    public function isTextNode()
    {
        return false;
    }

    /**
     * Accepts a NodeVisitor
     *
     * @param nodeVisitor  the NodeVisitor traversing the graph
     */
    abstract public function accept(NodeVisitor $nodeVisitor);

    /**
     * Returns this node as text (without any bbcode markup)
     *
     * @return the plain text representation of this node
     */
    abstract public function getAsText();

    /**
     * Returns this node as bbcode
     *
     * @return the bbcode representation of this node
     */
    abstract public function getAsBBCode();

    /**
     * Returns this node as HTML
     *
     * @return the html representation of this node
     */
    abstract public function getAsHTML();

    /**
     * Sets this node's parent to be the given node.
     *
     * @param parent the node to set as this node's parent
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Sets this node's nodeid
     *
     * @param nodeid this node's node id
     */
    public function setNodeId($nodeid)
    {
        $this->nodeid = $nodeid;
    }

}
