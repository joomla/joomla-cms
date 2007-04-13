<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Pagination Class.  Provides a common interface for content pagination for the
 * Joomla! Framework
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPagination extends JObject
{
	/**
	 * The record number to start dislpaying from
	 *
	 * @access public
	 * @var int
	 */
	var $limitstart = null;

	/**
	 * Number of rows to display per page
	 *
	 * @access public
	 * @var int
	 */
	var $limit = null;

	/**
	 * Total number of rows
	 *
	 * @access public
	 * @var int
	 */
	var $total = null;

	/**
	 * Base URL for pagination output
	 *
	 * @access protected
	 * @var string
	 */
	var $_link = null;

	/**
	 * View all flag
	 *
	 * @access protected
	 * @var boolean
	 */
	var $_viewall = false;

	/**
	 * Constructor
	 */
	function __construct($total, $limitstart, $limit, $link = null)
	{
		global $mainframe;

		// Value/Type checking
		$this->total			= (int) $total;
		$this->limitstart	= (int) max($limitstart, 0);
		$this->limit			= (int) max($limit, 1);

		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}

		if ($this->limitstart > $this->total) {
			$this->limitstart -= $this->limitstart % $this->limit;
		}

		// Set the total pages and current page values
		$this->set( 'pages.total', ceil($this->total / $this->limit));
		$this->set( 'pages.current', ceil(($this->limitstart + 1) / $this->limit));

		// Set the pagination iteration loop values
		$displayedPages	= 10;
		$this->set( 'pages.start', (floor(($this->get('pages.current') -1) / $displayedPages)) * $displayedPages +1);
		if ($this->get('pages.start') + $displayedPages -1 < $this->get('pages.total')) {
			$this->set( 'pages.stop', $this->get('pages.start') + $displayedPages -1);
		} else {
			$this->set( 'pages.stop', $this->get('pages.total'));
		}

		// Set the base link for the object
		if ($link) {
			$this->_link = $link;
		}
		else
		{
			$config = &JFactory::getConfig();
			if ($config->getValue('config.sef') && !$mainframe->isAdmin())
			{
				$this->_link = 'index.php?';
				$get = JRequest::get('get');
				$this->_link .= '&option='.$get['option'];

				foreach ($get as $k => $v)
				{
					if ($k != 'option' && $k != 'Itemid') {
						$this->_link .= '&amp;'.$k.'='.$v;
					}
				}
				$this->_link .= '&Itemid='.$get['Itemid'];
			}
			else
			{
				$this->_link = JRequest::getURI();
				if ((strpos($this->_link, 'index.php?') === false) && (strpos($this->_link, '&') === false)) {
					$this->_link .= 'index.php?';
				}
			}
			// Strip out limit and limitstart variables from base link
			$this->_link = preg_replace('#&?limit(start)?=\d+#', '', $this->_link);
		}

		// If we are viewing all records set the view all flag to true
		if (JRequest::getVar('limit', 0, '', 'int') == 0 && $this->limitstart == 0) {
			$this->_viewall = true;
		}
	}

	/**
	 * Return the rationalised offset for a row with a given index.
	 *
	 * @access	public
	 * @param	int	$index The row index
	 * @return	int Rationalised offset for a row with a given index
	 * @since	1.5
	 */
	function getRowOffset($index) {
		return $index +1 + $this->limitstart;
	}

	/**
	 * Return the pagination data object, only creating it if it doesn't already exist
	 *
	 * @access	public
	 * @return	object Pagination data object
	 * @since	1.5
	 */
	function getData()
	{
		static $data;
		if (!is_object($data)) {
			$data = $this->_buildDataObject();
		}
		return $data;
	}

	/**
	 * Create and return the pagination pages counter string, ie. Page 2 of 4
	 *
	 * @access public
	 * @return string Pagination pages counter string
	 * @since 1.5
	 */
	function getPagesCounter()
	{
		// Initialize variables
		$html = null;
		if ($this->get('pages.total') > 0) {
			$html .= JText::_('Page')." ".$this->get('pages.current')." ".JText::_('of')." ".$this->get('pages.total');
		}
		return $html;
	}

	/**
	 * Create and return the pagination result set counter string, ie. Results 1-10 of 42
	 *
	 * @access public
	 * @return string Pagination result set counter string
	 * @since 1.5
	 */
	function getResultsCounter()
	{
		// Initialize variables
		$html = null;
		$fromResult = $this->limitstart + 1;

		// If the limit is reached before the end of the list
		if ($this->limitstart + $this->limit < $this->total) {
			$toResult = $this->limitstart + $this->limit;
		} else {
			$toResult = $this->total;
		}

		// If there are results found
		if ($this->total > 0) {
			$msg = JText::sprintf('Results of', $fromResult, $toResult, $this->total);
			$html .= "\n".$msg;
		} else {
			$html .= "\n".JText::_('No records found');
		}

		return $html;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x
	 *
	 * @access public
	 * @return string Pagination page list string
	 * @since 1.0
	 */
	function getPagesLinks($link = null)
	{
		global $mainframe;

		$lang =& JFactory::getLanguage();

		// Build the page navigation list
		$data = $this->_buildDataObject($link);

		$list = array();

		$itemOverride = false;
		$listOverride = false;

		$chromePath = JPATH_BASE.'/templates/'.$mainframe->getTemplate().'/html/pagination.php';
		if (file_exists($chromePath)) {
			require_once ($chromePath);
			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive')) {
				$itemOverride = true;
			}
			if (function_exists('pagination_list_render')) {
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		} else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}
		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		} else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
	}

	/**
	 * Return the pagination footer
	 *
	 * @access public
	 * @return string Pagination footer
	 * @since 1.0
	 */
	function getListFooter()
	{
		global $mainframe;

		$list = array();
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox();
		$list['pagescounter']		= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();

		$chromePath = JPATH_BASE.'/templates/'.$mainframe->getTemplate().'/html/pagination.php';
		if (file_exists($chromePath)) {
			require_once ($chromePath);
			if (function_exists('pagination_list_footer')) {
				$listOverride = true;
			}
		}
		return ($listOverride) ? pagination_list_footer($list) : $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page
	 *
	 * @access public
	 * @return string The html for the limit # input box
	 * @since 1.0
	 */
	function getLimitBox($link = null)
	{
		global $mainframe;

		// Initialize variables
		$limits = array ();

		// Use the default link
		if ( is_null($link) ) {
			$link = $this->_link;
		}

		// Make the option list
		for ($i = 5; $i <= 30; $i += 5) {
			$limits[] = JHTMLSelect::option("$i");
		}
		$limits[] = JHTMLSelect::option('50');
		$limits[] = JHTMLSelect::option('100');
		$limits[] = JHTMLSelect::option('0', 'all');

		// Build the select list
		if ($mainframe->isAdmin()) {
			$html = JHTMLSelect::genericList($limits, 'limit', 'class="inputbox" size="1" onchange="submitform();"', 'value', 'text', $this->limit);
		} else {
			$html = JHTMLSelect::genericList($limits, 'limit', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $this->limit);
		}
		return $html;
	}

	/**
	 * Return the icon to move an item UP
	 *
	 * @access public
	 * @param int $i The row index
	 * @param boolean $condition True to show the icon
	 * @param string $task The task to fire
	 * @param string $alt The image alternate text string
	 * @return string Either the icon to move an item up or a space
	 * @since 1.0
	 */
	function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'Move Up', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if (($i > 0 || ($i + $this->limitstart > 0)) && $condition)
		{
			if($enabled) {
				$html	= '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html	.= '   <img src="images/uparrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/uparrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

	/**
	 * Return the icon to move an item DOWN
	 *
	 * @access public
	 * @param int $i The row index
	 * @param int $n The number of items in the list
	 * @param boolean $condition True to show the icon
	 * @param string $task The task to fire
	 * @param string $alt The image alternate text string
	 * @return string Either the icon to move an item down or a space
	 * @since 1.0
	 */
	function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'Move Down', $enabled = true)
	{
		$alt = JText::_($alt);

		$html = '&nbsp;';
		if (($i < $n -1 || $i + $this->limitstart < $this->total - 1) && $condition)
		{
			if($enabled) {
				$html	= '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html	.= '  <img src="images/downarrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html	.= '</a>';
			} else {
				$html	= '<img src="images/downarrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
			}
		}

		return $html;
	}

	/**
	 * Return the icon to move an item UP
	 *
	 * @access public
	 * @param int $id The row index
	 * @param int $order The ordering value for the item
	 * @param boolean $condition [Does Not Appear To Be Used]
	 * @param string $task The task to fire
	 * @param string $alt The image alternate text string
	 * @return string Either the icon to move an item up or a space
	 * @since 1.0
	 */
	function orderUpIcon2($id, $order, $condition = true, $task = 'orderup', $alt = '#')
	{
		// handling of default value
		if ($alt = '#') {
			$alt = JText::_('Move Up');
		}

		if ($order == 0) {
			$img = 'uparrow0.png';
		} else {
			if ($order < 0) {
				$img = 'uparrow-1.png';
			} else {
				$img = 'uparrow.png';
			}
		}
		$output = '<a href="javascript:void listItemTask(\'cb'.$id.'\',\'orderup\')" title="'.$alt.'">';
		$output .= '<img src="images/'.$img.'" width="16" height="16" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

		return $output;
	}

	/**
	 * Return the icon to move an item DOWN
	 *
	 * @access public
	 * @param int $id The row index
	 * @param int $order The ordering value for the item
	 * @param boolean $condition [Does Not Appear To Be Used]
	 * @param string $task The task to fire
	 * @param string $alt The image alternate text string
	 * @return string Either the icon to move an item down or a space
	 * @since 1.0
	 */
	function orderDownIcon2($id, $order, $condition = true, $task = 'orderdown', $alt = '#')
	{
		// handling of default value
		if ($alt = '#') {
			$alt = JText::_('Move Down');
		}

		if ($order == 0) {
			$img = 'downarrow0.png';
		} else {
			if ($order < 0) {
				$img = 'downarrow-1.png';
			} else {
				$img = 'downarrow.png';
			}
		}
		$output = '<a href="javascript:void listItemTask(\'cb'.$id.'\',\'orderdown\')" title="'.$alt.'">';
		$output .= '<img src="images/'.$img.'" width="16" height="16" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

		return $output;
	}

	function _list_footer($list)
	{
		// Initialize variables
		$lang =& JFactory::getLanguage();
		$html = "<div class=\"list-footer\">\n";

		if ($lang->isRTL()) {
			$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
			$html .= $list['pageslinks'];
			$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
		} else {
			$html .= "\n<div class=\"limit\">".JText::_('Display Num').$list['limitfield']."</div>";
			$html .= $list['pageslinks'];
			$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";
		}

		$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}

	function _list_render($list)
	{
		global $mainframe;

		// Initialize variables
		$lang =& JFactory::getLanguage();
		$html = null;

		// Reverse output rendering for right-to-left display
		if($lang->isRTL())
		{
			$html .=  $list['previous']['data'];
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
			$html .= '&lt;&lt; ';
			$html .= $list['start']['data'];
			$html .= ' &lt; ';
			$html .= $list['previous']['data'];
			foreach( $list['pages'] as $page ) {
				$html .= ' '.$page['data'];
			}
			$html .= ' '. $list['next']['data'];
			$html .= ' &gt;';
			$html .= ' '. $list['end']['data'];
			$html .= ' &gt;&gt;';
		}
		return $html;
	}

	function _item_active(&$item)
	{
		global $mainframe;
		if ($mainframe->isAdmin()) {
			return "<a title=\"".$item->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$item->base."; submitform();return false;\">".$item->text."</a>";
		} else {
			return "<a title=\"".$item->text."\" href=\"".$item->link."\" class=\"pagenav\">".$item->text."</a>";
		}
	}

	function _item_inactive(&$item)
	{
		global $mainframe;
		if ($mainframe->isAdmin()) {
			return "<span>".$item->text."</span>";
		} else {
			return "<span class=\"pagenav\">".$item->text."</span>";
		}
	}

	/**
	 * Create and return the pagination data object
	 *
	 * @access	public
	 * @return	object	Pagination data object
	 * @since	1.5
	 */
	function _buildDataObject($base=null)
	{
		// Initialize variables
		$data = new stdClass();
		if ($base) {
			$link = $base;
		} else {
			$base = $this->_link;
			$link = $this->_link;
		}

		$data->all	= new JPaginationObject(JText::_('View All'));
		if (!$this->_viewall) {
			$data->all->base	= '0';
			$data->all->link	= JRoute::_($base."&limitstart=0");
		}

		// Set the start and previous data objects
		$data->start	= new JPaginationObject(JText::_('Start'));
		$data->previous	= new JPaginationObject(JText::_('Prev'));

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') -2) * $this->limit;
			$data->start->base	= '0';
			$data->start->link	= JRoute::_($link."&limitstart=0");
			$data->previous->base	= $page;
			$data->previous->link	= JRoute::_($link."&limitstart=".$page);
		}

		// Set the next and end data objects
		$data->next	= new JPaginationObject(JText::_('Next'));
		$data->end	= new JPaginationObject(JText::_('End'));

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$page = $this->get('pages.current') * $this->limit;
			$endPage = ($this->get('pages.total') -1) * $this->limit;
			$data->next->base	= $page;
			$data->next->link	= JRoute::_($link."&limitstart=".$page);
			$data->end->base	= $endPage;
			$data->end->link	= JRoute::_($link."&limitstart=".$endPage);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++)
		{
			$offset = ($i -1) * $this->limit;
			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base	= $offset;
				$data->pages[$i]->link	= JRoute::_($link."&limitstart=".$offset);
			}
		}
		return $data;
	}
}

/**
 * Pagination object representing a particular item in the pagination lists
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JPaginationObject extends JObject
{
	var $text;
	var $base;
	var $link;

	function __construct($text, $base=null, $link=null)
	{
		$this->text = $text;
		$this->base = $base;
		$this->link = $link;
	}
}