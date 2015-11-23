<?php
/**
 * Implements an [img=alt] tag that supports an optional class argument.
 *
 */
class ImageCodeDefinition extends \JBBCode\CodeDefinition
{
 
    public function __construct()
    {
        $this->parseContent = false;
        $this->useOption = true;
        $this->setTagName('img');
        $this->nestLimit = -1;
    }
 
    public function asHtml(\JBBCode\ElementNode $el)
    {
        $url = '';
        foreach ($el->getChildren() as $child) {
            $url .= $child->getAsText();
        }
        
        // Split the argument on the pipe character
        $argPieces = explode('|', $el->getAttribute());
        $altText = $argPieces[0];
        
        $class = 'default-class';
        if (count($argPieces) > 1) {
            $class = $argPieces[1];
        }
        
        return '<img src="' + $url + '" alt="' + $altText + '" class="' + $class + '" />';
    }
 
}