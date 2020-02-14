<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

function pagination_list_render($list) {
	// Initialize variables
	$html = '<ul class="pagination">';

	if ($list['start']['active']==1)   $html .= $list['start']['data'];
	if ($list['previous']['active']==1) $html .= $list['previous']['data'];

	foreach ($list['pages'] as $page) {
		$html .= $page['data'];
	}

	if ($list['next']['active']==1) $html .= $list['next']['data'];
	if ($list['end']['active']==1)  $html .= $list['end']['data'];

	$html .= '</ul>';

	return $html;
}

function pagination_item_active(&$item) {

	$cls = '';

    if ($item->text == JText::_('Next')) { $item->text = '&raquo;'; $cls = "next";}
    if ($item->text == JText::_('Prev')) { $item->text = '&laquo;'; $cls = "previous";}

	if ($item->text == JText::_('First')) { $cls = "first";}
    if ($item->text == JText::_('Last'))   { $cls = "last";}

    return '<li class="page-item"><a class="page-link ' . $cls . '" href="' . $item->link . '" title="' . $item->text . '">' . $item->text . '</a></li>';
}

function pagination_item_inactive( &$item ) {
	$cls = (int)$item->text > 0 ? 'active': 'disabled';
	return '<li class="page-item ' . $cls . '"><a class="page-link">' . $item->text . '</a></li>';
}
