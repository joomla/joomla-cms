<?php
/**
 * Implements a [ol] code definition that provides the following syntax:
 *
 * [ol]
 *   [li] first item [/li]
 *   [li] second item [/li]
 *   [li] third item [/li]
 * [/ol]
 *
 */
class OLCodeDefinition extends \JBBCode\CodeDefinition
{
 
    public function __construct()
    {
        $this->parseContent = true;
        $this->useOption = false;
        $this->setTagName('ol');
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
        return '<ol>'.implode('', $list).'</ol>';
    }
 
}