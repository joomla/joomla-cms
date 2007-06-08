<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to pagination rendering.
 *
 * pagination_list_footer
 * 	Input variable $list is an array with offsets:
 * 		$list[limit]		: int
 * 		$list[limitstart]	: int
 * 		$list[total]		: int
 * 		$list[limitfield]	: string
 * 		$list[pagescounter]	: string
 * 		$list[pageslinks]	: string
 *
 * pagination_list_render
 * 	Input variable $list is an array with offsets:
 * 		$list[all]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[start]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[previous]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[next]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[end]
 * 			[data]		: string
 * 			[active]	: boolean
 * 		$list[pages]
 * 			[{PAGE}][data]		: string
 * 			[{PAGE}][active]	: boolean
 *
 * pagination_item_active
 * 	Input variable $item is an object with fields:
 * 		$item->base	: integer
 * 		$item->link	: string
 * 		$item->text	: string
 *
 * pagination_item_inactive
 * 	Input variable $item is an object with fields:
 * 		$item->base	: integer
 * 		$item->link	: string
 * 		$item->text	: string
 *
 * This gives template designers ultimate control over how pagination is rendered.
 *
 * NOTE: If you override pagination_item_active OR pagination_item_inactive you MUST override them both
 */

function pagination_list_footer($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	$html = "<del class=\"container\"><div class=\"pagination\">\n";

	if ($lang->isRTL()) {
		$html .= "\n<div class=\"limit\">".$list['pagescounter']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
	} else {
		$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"limit\">".$list['pagescounter']."</div>";
	}

	$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";
	$html .= "\n</div></del>";

	return $html;
}

function pagination_list_render($list)
{
	// Initialize variables
	$lang =& JFactory::getLanguage();
	$html = null;

	// Reverse output rendering for right-to-left display
	if($lang->isRTL())
	{
		if ($list['end']['active']) {
			$html .= "<div class=\"button2-left\"><div class=\"end\">".$list['end']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-left off\"><div class=\"end\">".$list['end']['data']."</div></div>";
		}
		if ($list['next']['active']) {
			$html .= "<div class=\"button2-left\"><div class=\"next\">".$list['next']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-left off\"><div class=\"next\">".$list['next']['data']."</div></div>";
		}

		$html .= "\n<div class=\"button2-left\"><div class=\"page\">";
		$list['pages'] = array_reverse( $list['pages'] );
		foreach( $list['pages'] as $page ) {
			$html .= $page['data'];
		}
		$html .= "\n</div></div>";

		if ($list['previous']['active']) {
			$html .= "<div class=\"button2-right\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-right off\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
		}
		if ($list['start']['active']) {
			$html .= "<div class=\"button2-right\"><div class=\"start\">".$list['start']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-right off\"><div class=\"start\">".$list['start']['data']."</div></div>";
		}



	}
	else
	{
		if ($list['start']['active']) {
			$html .= "<div class=\"button2-right\"><div class=\"start\">".$list['start']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-right off\"><div class=\"start\">".$list['start']['data']."</div></div>";
		}
		if ($list['previous']['active']) {
			$html .= "<div class=\"button2-right\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-right off\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
		}

		$html .= "\n<div class=\"button2-left\"><div class=\"page\">";
		foreach( $list['pages'] as $page ) {
			$html .= $page['data'];
		}
		$html .= "\n</div></div>";

		if ($list['next']['active']) {
			$html .= "<div class=\"button2-left\"><div class=\"next\">".$list['next']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-left off\"><div class=\"next\">".$list['next']['data']."</div></div>";
		}
		if ($list['end']['active']) {
			$html .= "<div class=\"button2-left\"><div class=\"end\">".$list['end']['data']."</div></div>";
		} else {
			$html .= "<div class=\"button2-left off\"><div class=\"end\">".$list['end']['data']."</div></div>";
		}
	}
	return $html;
}

function pagination_item_active(&$item)
{
	return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$item->base."; submitform();return false;\">".$item->text."</a>";
}

function pagination_item_inactive(&$item)
{
	return "<span>".$item->text."</span>";
}
?>