<?php
/**
 * Implements a [table] code definition that provides the following syntax:
 *
 * [table]
 *   [tr]
 *   	[td]first item[/td]
 *   	[td]second item[/td]
 *   	[td]third item[/td]
 *   [/tr]
 * [/table]
 *
 */
class TableCodeDefinition extends \JBBCode\CodeDefinition
{
 
    public function __construct()
    {
        $this->parseContent = true;
        $this->useOption = false;
        $this->setTagName('table');
        $this->nestLimit = -1;
    }
 
    public function asHtml(\JBBCode\ElementNode $el)
    {
        $bodyHtml = '';
        foreach ($el->getChildren() as $child) {
            $bodyHtml .= $child->getAsHTML();
        }
        
        $trList = array();
        preg_match_all('#\[tr\](.*?)\[\/tr\]#is', $bodyHtml, $trList);
        
        $trList = array_map(function($tr)
        {
        	$tdList = array();
        	preg_match_all('#\[td\](.*?)\[\/td\]#is', $tr, $tdList);
        
        	$tdList = array_map(function($td)
        	{
        		return '<td>'.$td.'</td>';
        	}, $tdList[1]);
        
        	return '<tr>'.implode('', $tdList).'</tr>';
        }, $trList[1]);
        
        return '<table class="table table-hover table-bordered table-striped"><tbody>'.implode('', $trList).'</tbody></table>';
    }
 
}