<?php
/**
 * Implements a [list] code definition that provides the following syntax:
 *
 * [list]
 *   [*] first item
 *   [*] second item
 *   [*] third item
 * [/list]
 *
 */
class ULCodeDefinition extends \JBBCode\CodeDefinition
{
 
    public function __construct()
    {
        $this->parseContent = true;
        $this->useOption = false;
        $this->setTagName('ul');
        $this->nestLimit = -1;
    }
 
    public function asHtml(\JBBCode\ElementNode $el)
    {
        $bodyHtml = '';
        foreach ($el->getChildren() as $child) {
            $bodyHtml .= $child->getAsHTML();
        }

        $list = array();
        preg_match_all('#\[li\](.*?)\[\/li\]#is', $bodyHtml, $list);
        
        $list = array_map(function($li){return '<li>'.$li.'</li>';}, $list[1]);
        return '<ul>'.implode('', $list).'</ul>';
    }
 
}