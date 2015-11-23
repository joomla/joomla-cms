<?php

namespace JBBCode;

require_once 'ElementNode.php';

/**
 * A DocumentElement object represents the root of a document tree. All 
 * documents represented by this document model should have one as its root.
 *
 * @author jbowens
 */
class DocumentElement extends ElementNode
{
    /**
     * Constructs the document element node
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTagName("Document");
        $this->setNodeId(0);
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.ElementNode::getAsBBCode()
     *
     * Returns the BBCode representation of this document
     *
     * @return this document's bbcode representation
     */
    public function getAsBBCode()
    {
        $s = "";
        foreach($this->getChildren() as $child){
            $s .= $child->getAsBBCode();
        }

        return $s;
    }

    /**
     * (non-PHPdoc)
     * @see JBBCode.ElementNode::getAsHTML()
     *
     * Documents don't add any html. They only exist as a container for their
     * children, so getAsHTML() simply iterates through the document's children,
     * returning their html.
     *
     * @return the HTML representation of this document
     */
    public function getAsHTML()
    {
        $s = "";
        foreach($this->getChildren() as $child)
            $s .= $child->getAsHTML();

        return $s;
    }

    public function accept(NodeVisitor $visitor)
    {
        $visitor->visitDocumentElement($this);
    }

}
