<?php

namespace JBBCode;

require_once 'Node.php';

/**
 * Represents a piece of text data. TextNodes never have children.
 *
 * @author jbowens
 */
class TextNode extends Node
{
    /* The value of this text node */
    protected $value;

    /**
     * Constructs a text node from its text string
     *
     * @param string $val
     */
    public function __construct($val)
    {
        $this->value = $val;
    }

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitTextNode($this);
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.Node::isTextNode()
     *
     * returns true
     */
    public function isTextNode()
    {
        return true;
    }

    /**
     * Returns the text string value of this text node.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.Node::getAsText()
     *
     * Returns the text representation of this node.
     *
     * @return this node represented as text
     */
    public function getAsText()
    {
        return $this->getValue();
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.Node::getAsBBCode()
     *
     * Returns the bbcode representation of this node. (Just its value)
     *
     * @return this node represented as bbcode
     */
    public function getAsBBCode()
    {
        return $this->getValue();
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.Node::getAsHTML()
     *
     * Returns the html representation of this node. (Just its value)
     *
     * @return this node represented as HTML
     */
    public function getAsHTML()
    {
        return $this->getValue();
    }

    /**
     * Edits the text value contained within this text node.
     *
     * @param newValue  the new text value of the text node
     */
    public function setValue($newValue)
    {
        $this->value = $newValue;
    }

}
