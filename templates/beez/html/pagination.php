<?php
defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to pagination rendering.
 *
 * pagination_list_footer
 *         Input variable $list is an array with offsets:
 *                 $list[limit]                : int
 *                 $list[limitstart]        : int
 *                 $list[total]                : int
 *                 $list[limitfield]        : string
 *                 $list[pagescounter]        : string
 *                 $list[pageslinks]        : string
 *
 * pagination_list_render
 *         Input variable $list is an array with offsets:
 *                 $list[all]
 *                         [data]                : string
 *                         [active]        : boolean
 *                 $list[start]
 *                         [data]                : string
 *                         [active]        : boolean
 *                 $list[previous]
 *                         [data]                : string
 *                         [active]        : boolean
 *                 $list[next]
 *                         [data]                : string
 *                         [active]        : boolean
 *                 $list[end]
 *                         [data]                : string
 *                         [active]        : boolean
 *                 $list[pages]
 *                         [{PAGE}][data]                : string
 *                         [{PAGE}][active]        : boolean
 *
 * pagination_item_active
 *         Input variable $item is an object with fields:
 *                 $item->base        : integer
 *                 $item->link        : string
 *                 $item->text        : string
 *
 * pagination_item_inactive
 *         Input variable $item is an object with fields:
 *                 $item->base        : integer
 *                 $item->link        : string
 *                 $item->text        : string
 *
 * This gives template designers ultimate control over how pagination is rendered.
 *
 * NOTE: If you override pagination_item_active OR pagination_item_inactive you MUST override them both
 */

function pagination_list_footer($list)
{
        // Initialize variables
        $lang =& JFactory::getLanguage();
        $html = "<div class=\"list-footer\">\n";

        if ($lang->isRTL())
        {
                $html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
                $html .= $list['pageslinks'];
                $html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
        }
        else
        {
                $html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
                $html .= $list['pageslinks'];
                $html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
        }

        $html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";
        $html .= "\n</div>";

        return $html;
}

function pagination_list_render($list)
{
        // Initialize variables
        $lang =& JFactory::getLanguage();
        $html = "<ul class=\"pagination\">";

        // Reverse output rendering for right-to-left display
        if($lang->isRTL())
        {
                $html .= $list['previous']['data'];
                $html .= $list['start']['data'];

                $list['pages'] = array_reverse( $list['pages'] );
                foreach( $list['pages'] as $page ) {
                        $html .= $page['data'];
                }

                $html .= $list['end']['data'];

                $html .= $list['next']['data'];
        }
        else
        {
                $html .= $list['start']['data'];
                $html .= $list['previous']['data'];

                foreach( $list['pages'] as $page )
                {
                        if($page['data']['active']) {


                        }

                        $html .= $page['data'];

                        if($page['data']['active']) {


                        }
                }

                $html .= $list['next']['data'];

                $html .= $list['end']['data'];

        }

        $html .= "</ul>";
        return $html;
}

function pagination_item_active(&$item) {
        return "<li><strong><a href=\"".JRoute::_($item->link)."\" title=\"".$item->text."\">".$item->text."</a></strong></li>";
}

function pagination_item_inactive(&$item) {
        return "<li><span>".$item->text."</span></li>";
}
?>