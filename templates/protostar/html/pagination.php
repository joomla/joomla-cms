<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

/**
 * Renders the pagination footer
 *
 * @param   array   $list  Array containing pagination footer
 *
 * @return  string         HTML markup for the full pagination footer
 *
 * @since   3.0
 */
function pagination_list_footer($list)
{
	$html = "<div class=\"pagination\">\n";
	$html .= $list['pageslinks'];
	$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "limitstart\" value=\"" . $list['limitstart'] . "\" />";
	$html .= "\n</div>";

	return $html;
}

/**
 * Renders the pagination list
 *
 * @param   array   $list  Array containing pagination information
 *
 * @return  string         HTML markup for the full pagination object
 *
 * @since   3.0
 */
function pagination_list_render($list)
{
	// Calculate to display range of pages
	$currentPage = 1;
	$range = 1;
	$step = 5;
	foreach ($list['pages'] as $k => $page)
	{
		if (!$page['active'])
		{
			$currentPage = $k;
		}
	}
	if ($currentPage >= $step)
	{
		if ($currentPage % $step === 0)
		{
			$range = ceil($currentPage / $step) + 1;
		}
		else
		{
			$range = ceil($currentPage / $step);
		}
	}

	$html  = '<nav role="navigation" aria-label="' . JText::_('JLIB_HTML_PAGINATION') . '">';
	$html .= '<ul class="pagination-list">';
	$html .= $list['start']['data'];
	$html .= $list['previous']['data'];

	foreach ($list['pages'] as $k => $page)
	{
		if ($k !== $currentPage && $k !== $range * $step - $step
			&& ($k % $step === 0 || $k === $range * $step - ($step + 1))
			&& in_array($k, range($range * $step - ($step + 1), $range * $step)))
		{
			$page['data'] = preg_replace('#(<a.*?>).*?(</a>)#', '$1...$2', $page['data']);
		}

		$html .= $page['data'];
	}

	$html .= $list['next']['data'];
	$html .= $list['end']['data'];

	$html .= '</ul>';
	$html .= '</nav>';
	return $html;
}

/**
 * Renders an active item in the pagination block
 *
 * @param   JPaginationObject  $item  The current pagination object
 *
 * @return  string                    HTML markup for active item
 *
 * @since   3.0
 */
function pagination_item_active(&$item)
{
	$class = '';

	// Check for "Start" item
	if ($item->text === JText::_('JLIB_HTML_START'))
	{
		$display = '<span class="icon-first" aria-hidden="true"></span>';
		$aria    = JText::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
	}

	// Check for "Prev" item
	if ($item->text === JText::_('JPREV'))
	{
		$display = '<span class="icon-previous" aria-hidden="true"></span>';
		$aria    = JText::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
	}

	// Check for "Next" item
	if ($item->text === JText::_('JNEXT'))
	{
		$display = '<span class="icon-next" aria-hidden="true"></span>';
		$aria    = JText::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
	}

	// Check for "End" item
	if ($item->text === JText::_('JLIB_HTML_END'))
	{
		$display = '<span class="icon-last" aria-hidden="true"></span>';
		$aria    = JText::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
	}

	// If the display object isn't set already, just render the item with its text
	if (!isset($display))
	{
		$display = $item->text;
		$aria    = JText::sprintf('JLIB_HTML_GOTO_PAGE', $item->text);
		$class   = ' class="hidden-phone"';
	}

	return '<li' . $class . '><a title="' . $item->text . '" href="' . $item->link . '" class="pagenav" aria-label="' . $aria . '">' . $display . '</a></li>';

}

/**
 * Renders an inactive item in the pagination block
 *
 * @param   JPaginationObject  $item  The current pagination object
 *
 * @return  string  HTML markup for inactive item
 *
 * @since   3.0
 */
function pagination_item_inactive(&$item)
{
	// Check for "Start" item
	if ($item->text === JText::_('JLIB_HTML_START'))
	{
		return '<li class="disabled"><a><span class="icon-first" aria-hidden="true"></span></a></li>';
	}

	// Check for "Prev" item
	if ($item->text === JText::_('JPREV'))
	{
		return '<li class="disabled"><a><span class="icon-previous" aria-hidden="true"></span></a></li>';
	}

	// Check for "Next" item
	if ($item->text === JText::_('JNEXT'))
	{
		return '<li class="disabled"><a><span class="icon-next" aria-hidden="true"></span></a></li>';
	}

	// Check for "End" item
	if ($item->text === JText::_('JLIB_HTML_END'))
	{
		return '<li class="disabled"><a><span class="icon-last" aria-hidden="true"></span></a></li>';
	}

	// Check if the item is the active page
	if (isset($item->active) && $item->active)
	{
		$aria = JText::sprintf('JLIB_HTML_PAGE_CURRENT', $item->text);

		return '<li class="active hidden-phone"><a aria-current="true" aria-label="' . $aria . '">' . $item->text . '</a></li>';
	}

	// Doesn't match any other condition, render a normal item
	return '<li class="disabled hidden-phone"><a>' . $item->text . '</a></li>';
}
