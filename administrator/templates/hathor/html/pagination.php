<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This is a file to add template specific chrome to pagination rendering.
 *
 * pagination_list_footer
 *	Input variable $list is an array with offsets:
 *		$list[prefix]		: string
 *		$list[limit]		: int
 *		$list[limitstart]	: int
 *		$list[total]		: int
 *		$list[limitfield]	: string
 *		$list[pagescounter]	: string
 *		$list[pageslinks]	: string
 *
 * pagination_list_render
 *	Input variable $list is an array with offsets:
 *		$list[all]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[start]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[previous]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[next]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[end]
 *			[data]		: string
 *			[active]	: boolean
 *		$list[pages]
 *			[{PAGE}][data]		: string
 *			[{PAGE}][active]	: boolean
 *
 * pagination_item_active
 *	Input variable $item is an object with fields:
 *		$item->base	: integer
 *		$item->prefix	: string
 *		$item->link	: string
 *		$item->text	: string
 *
 * pagination_item_inactive
 *	Input variable $item is an object with fields:
 *		$item->base	: integer
 *		$item->prefix	: string
 *		$item->link	: string
 *		$item->text	: string
 *
 * This gives template designers ultimate control over how pagination is rendered.
 *
 * NOTE: If you override pagination_item_active OR pagination_item_inactive you MUST override them both
 */

function pagination_list_footer($list)
{
	/**
	 * Fix javascript jump menu
	 *
	 * Remove the onchange=Joomla.submitform from the select tag
	 * Add in a button with onclick instead
	 */
	$fixlimit = $list['limitfield'];
	$fixlimit = preg_replace('/onchange="Joomla.submitform\(\);"/', '', $fixlimit);

	$html = "<div class=\"containerpg\"><div class=\"pagination\">\n";

	$html .= "\n<div class=\"limit\"><label for=\"limit\">".JText::_('JGLOBAL_DISPLAY_NUM')." </label>";
	$html .= "\n".$fixlimit;
	$html .= "\n<button id=\"pagination-go\" type=\"button\" onclick=\"Joomla.submitform()\">" . JText::_('JSUBMIT') . "</button></div>";
	$html .= "\n" . $list['pageslinks'];
	$html .= "\n<div class=\"limit\">".$list['pagescounter']."</div>";

	$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"".$list['limitstart']."\" />";
	$html .= "\n<div class=\"clr\"></div></div></div>";

	return $html;
}

function pagination_list_render($list)
{
	$html = null;

	if ($list['start']['active'])
	{
		$html .= "<div class=\"button2-right\"><div class=\"start\">".$list['start']['data']."</div></div>";
	} else {
		$html .= "<div class=\"button2-right off\"><div class=\"start\">".$list['start']['data']."</div></div>";
	}
	if ($list['previous']['active'])
	{
		$html .= "<div class=\"button2-right\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
	} else {
		$html .= "<div class=\"button2-right off\"><div class=\"prev\">".$list['previous']['data']."</div></div>";
	}

	$html .= "\n<div class=\"button2-left\"><div class=\"page\">";
	foreach ($list['pages'] as $page)
	{
		$html .= $page['data'];
	}
	$html .= "\n</div></div>";

	if ($list['next']['active'])
	{
		$html .= "<div class=\"button2-left\"><div class=\"next\">".$list['next']['data']."</div></div>";
	} else {
		$html .= "<div class=\"button2-left off\"><div class=\"next\">".$list['next']['data']."</div></div>";
	}
	if ($list['end']['active'])
	{
		$html .= "<div class=\"button2-left\"><div class=\"end\">".$list['end']['data']."</div></div>";
	} else {
		$html .= "<div class=\"button2-left off\"><div class=\"end\">".$list['end']['data']."</div></div>";
	}

	return $html;
}

function pagination_item_active(&$item)
{
	if ($item->base > 0)
	{
		return "<a href=\"#\" title=\"".$item->text."\" onclick=\"document.adminForm." . $item->prefix . "limitstart.value=".$item->base."; Joomla.submitform();return false;\">".$item->text."</a>";
	}
	else
	{
		return "<a href=\"#\" title=\"".$item->text."\" onclick=\"document.adminForm." . $item->prefix . "limitstart.value=0; Joomla.submitform();return false;\">".$item->text."</a>";
	}
}

function pagination_item_inactive(&$item)
{
	if ($item->active)
	{
		$class = 'class="active"';
	}
	else
	{
		$class = '';
	}
	return '<span ' . $class . '>' . $item->text . '</span>';
}
