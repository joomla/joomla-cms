<?php
/** 
 * @package     Minima
 * @author      Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

function pagination_list_footer($list)
{
    static $instancetest = 0;

    // Initialise variables.
    $lang = JFactory::getLanguage();
    $html = "<div class=\"list-footer\">\n";

    //$html .= "\n<div class=\"limit\">".JText::_('JGLOBAL_DISPLAY_NUM').$list['limitfield']."</div>";

    $html .= $list['pageslinks'];
    $html .= "<span>".Jtext::_('TPL_MINIMA_TOTAL')." ".$list['total']." ".Jtext::_('TPL_MINIMA_ITEMS')."</span>";

    //$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

    //$html .= "\n<input id=\"limit\" type=\"hidden\" name=\"limit\" value=\"15\" />";

    if ($instancetest == 0) {
        $html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"".$list['limitstart']."\" />";
    }

    $instancetest = 1;

    $html .= "\n</div>";

    return $html;
}

function pagination_list_render($list)
{
    // Initialize variables
    $lang = JFactory::getLanguage();

    //var_dump($lang);
    //JText::_('TPL_MINIMA_LAQUO');

    $html = "<ul class=\"pagination\">";

    if ($list['previous']['active']) {
        $html .= "<li class=\"prev\">".$list['previous']['data']."</li>";
    } else {
        $html .= "<li class=\"prev off\">".$list['previous']['data']."</li>";
    }

    foreach($list['pages'] as $page) {
        $html .= "<li>".$page['data']."</li>";
    }

    if ($list['next']['active']) {
        $html .= "<li class=\"next\">".$list['next']['data']."</li>";
    } else {
        $html .= "<li class=\"next off\">".$list['next']['data']."</li>";
    }

    $html .= "</ul>";
    return $html;
}

function pagination_item_active(&$item)
{
    if ($item->base>0)
        return "<a href=\"#\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm." . $item->prefix . "limitstart.value=".$item->base."; Joomla.submitform();return false;\">".$item->text."</a>";
    else
        return "<a href=\"#\" title=\"".$item->text."\" onclick=\"javascript: document.adminForm." . $item->prefix . "limitstart.value=0; Joomla.submitform();return false;\">".$item->text."</a>";
}

function pagination_item_inactive(&$item)
{
//return "<span class=\"currentpage\">".$item->text."</span>";
return $item->text;
}
?>
