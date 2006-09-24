<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Pagination Class.  Provides a common interface for content pagination for the
 * Joomla! Framework
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Presentation
 * @since		1.5
 */
class JPagination extends JObject
{

	/**
	 * The record number to start dislpaying from
	 * @access public
	 * @var int
	 */
	var $limitstart = null;

	/**
	 * Number of rows to display per page
	 * @access public
	 * @var int
	 */
	var $limit = null;

	/**
	 * Total number of rows
	 * @access public
	 * @var int
	 */
	var $total = null;

	/**
	 * Base URL for pagination output
	 * @access protected
	 * @var string
	 */
	var $_link = null;

	/**
	 * View all flag
	 * @access protected
	 * @var boolean
	 */
	var $_viewall = false;

	/**
	 * Constructor
	 */
	function __construct($total, $limitstart, $limit, $link=null)
	{
		// Value/Type checking
		$this->total		= (int) $total;
		$this->limitstart	= (int) max($limitstart, 0);
		$this->limit		= (int) max($limit, 1);

		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}
		if (($this->limit - 1) * $this->limitstart > $this->total) {
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
		} else {
			$this->_link = JRequest::getURI();
		}

		// If we are viewing all records set the view all flag to true
		if ($this->limit == 0 && $this->limitstart == 0) {
			$this->_viewall = true;
			$this->set( 'pages.current', null);
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
	function getData() {
		static $data;

		if (!is_object($data)) {
			$data = $this->_buildDataObject();
		}

		return $data;
	}

	/**
	 * Sets the vars for the page navigation template
	 *
	 * @access public
	 * @param object $tmpl PatTemplate Object to add the pagination footer template to
	 * @param string $name Name of the pagination footer template to add
	 * @return boolean True if successful
	 * @since 1.5
	 */
	function setTemplateVars(& $tmpl, $name = 'admin-list-footer', $link = null)
	{
		// Set the template variables
		$tmpl->addVar($name, 'PAGE_LINKS', $this->getPagesLinks($link));
		$tmpl->addVar($name, 'PAGE_LIST_OPTIONS', $this->getLimitBox($link));
		$tmpl->addVar($name, 'PAGE_COUNTER', $this->getPagesCounter());

		return true;
	}

	/**
	 * Writes the html for the leafs counter, eg, Page 1 of x
	 *
	 * @access public
	 */
	function writeLeafsCounter()
	{
		$html = null;
		$page = $this->limitstart + 1;
		if ($this->total > 0) {
			$html .= JText::_('Page')." ".$page." ".JText::_('of')." ".$this->total;
		}
		return $html;
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

		// Make the option list
		for ($i = 5; $i <= 30; $i += 5) {
			$limits[] = mosHTML::makeOption("$i");
		}
		$limits[] = mosHTML::makeOption('50');
		$limits[] = mosHTML::makeOption('100');

		// Build the select list
		if ($mainframe->isAdmin()) {
			$html = mosHTML::selectList($limits, 'limit', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $this->limit);
		} else {
			$link = JURI::resolve($link.'&amp;limit=\' + this.options[selectedIndex].value + \'&amp;limitstart='.$this->limitstart);
			$html = mosHTML::selectList($limits, 'limit', 'class="inputbox" size="1" onchange="document.location.href=\''.$link.'\';"', 'value', 'text', $this->limit);
		}
		return $html;
	}

	/**
	 * Create and return the pagination counter string, ie. Results 1-10 of 42
	 *
	 * @access public
	 * @return string Pagination counter string
	 * @since 1.0
	 */
	function getPagesCounter()
	{
		// Initialize variables
		$html = null;

		$from_result = $this->limitstart + 1;

		// If the limit is reached before the end of the list
		if ($this->limitstart + $this->limit < $this->total) {
			$to_result = $this->limitstart + $this->limit;
		} else {
			$to_result = $this->total;
		}
		// If there are results found
		if ($this->total > 0) {
			$msg = sprintf(JText::_('Results of'), $from_result, $to_result, $this->total);
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
		$html = null;
		$buff1 = array();
		$buff2 = array();
		$buff3 = array();

		// Build the select list
		if ($mainframe->isAdmin()) {
			if ($data->start->base !== null) {
				$buff1[] = "\n<div class=\"button2-right\"><div class=\"start\"><a title=\"".$data->start->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$data->start->base."; document.adminForm.submit();return false;\">".$data->start->text."</a></div></div>";
			} else {
				$buff1[] = "\n<div class=\"button2-right off\"><div class=\"start\"><span>".$data->start->text."</span></div></div>";
			}
			if ($data->previous->base !== null) {
				$buff1[] = "\n<div class=\"button2-right\"><div class=\"prev\"><a title=\"".$data->previous->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$data->previous->base."; document.adminForm.submit();return false;\">".$data->previous->text."</a></div></div>";
			} else {
				$buff1[] = "\n<div class=\"button2-right off\"><div class=\"prev\"><span>".$data->previous->text."</span></div></div>";
			}


			$openList = "\n<div class=\"button2-left\"><div class=\"page\">";
			$i = 1;
			while (isset($data->pages[$i])) {
				if ($data->pages[$i]->base !== null) {
					$buff2[] = "\n<a title=\"".$data->pages[$i]->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$data->pages[$i]->base."; document.adminForm.submit();return false;\">".$data->pages[$i]->text."</a>";
				} else {
					$buff2[] = "\n<span>".$data->pages[$i]->text."</span>";
				}
				$i++;
			}
			$closeList = "\n</div></div>";


			if ($data->next->base !== null) {
				$buff3[] = "\n<div class=\"button2-left\"><div class=\"next\"><a title=\"".$data->next->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$data->next->base."; document.adminForm.submit();return false;\">".$data->next->text."</a></div></div>";
			} else {
				$buff3[] = "\n<div class=\"button2-left off\"><div class=\"next\"><span>".$data->next->text."</span></div></div>";
			}
			if ($data->end->base !== null) {
				$buff3[] = "\n<div class=\"button2-left\"><div class=\"end\"><a title=\"".$data->end->text."\" onclick=\"javascript: document.adminForm.limitstart.value=".$data->end->base."; document.adminForm.submit();return false;\">".$data->end->text."</a></div></div>";
			} else {
				$buff3[] = "\n<div class=\"button2-left off\"><div class=\"end\"><span>".$data->end->text."</span></div></div>";
			}

			/*
			 * reverse output rendering for rtl display else normal rendering sequence
			 */
			if( $lang->isRTL() ){
				$buff1 = array_reverse( $buff1 );
				$buff2 = array_reverse( $buff2 );
				$buff3 = array_reverse( $buff3 );
				foreach( $buff3 as $line ) {
					$html .= $line;
				}
				$html .= $openList;
				foreach( $buff2 as $line ) {
					$html .= $line;
				}
				$html .= $closeList;
				foreach( $buff1 as $line ) {
					$html .= $line;
				}
			} else {
				foreach( $buff1 as $line ) {
					$html .= $line;
				}
				$html .= $openList;
				foreach( $buff2 as $line ) {
					$html .= $line;
				}
				$html .= $closeList;
				foreach( $buff3 as $line ) {
					$html .= $line;
				}
			}
		} else {
			/*
			 * This is for page navigation if not in the administration section
			 */
			if ($data->start->base !== null) {
				$html .= '<a href="'.$data->start->link.'" class="pagenav" title="first page">'.$data->start->text.'</a> ';
			} else {
				$html .= '<span class="pagenav">'.$data->start->text.'</span> ';
			}
			if ($data->previous->base !== null) {
				$html .= '<a href="'.$data->previous->link.'" class="pagenav" title="previous page">'.$data->previous->text.'</a> ';
			} else {
				$html .= '<span class="pagenav">'.$data->previous->text.'</span> ';
			}

			$i = 1;
			while (isset($data->pages[$i])) {
				if ($data->pages[$i]->base !== null) {
					$html .= '<a href="'.$data->pages[$i]->link.'" class="pagenav"><strong>'.$data->pages[$i]->text.'</strong></a> ';
				} else {
					$html .= '<span class="pagenav">'.$data->pages[$i]->text.'</span> ';
				}
				$i++;
			}

			if ($data->next->base !== null) {
				$html .= '<a href="'.$data->next->link.' " class="pagenav" title="next page">'.$data->next->text.'</a> ';
			} else {
				$html .= '<span class="pagenav">'.$data->next->text.'</span> ';
			}
			if ($data->end->base !== null) {
				$html .= '<a href="'.$data->end->link.' " class="pagenav" title="end page">'.$data->end->text.'</a>';
			} else {
				$html .= '<span class="pagenav">'.$data->end->text.'</span>';
			}
		}
		return $html;
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
		$lang =& JFactory::getLanguage();
		$buff = array();

		$html = "<del class=\"container\"><div class=\"pagination\">\n";

		$buff[] = "\n<div class=\"limit\">".JText::_('Display Num').$this->getLimitBox()."</div>";
		$buff[] = $this->getPagesLinks();
		$buff[] = "\n<div class=\"limit\">".$this->getPagesCounter()."</div>";

		if( $lang->isRTL() ){
			$buff = array_reverse( $buff );
		}
		foreach( $buff as $line ) {
			$html .= $line;
		}
		$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"$this->limitstart\" />";
//		$html .= "\n</div>";
		$html .= "\n</div></del>";
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
				$html  = '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html .= '   <img src="images/uparrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html .= '</a>';
			} else {
				$html  = '<img src="images/uparrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
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
				$html  = '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">';
				$html .= '  <img src="images/downarrow.png" width="16" height="16" border="0" alt="'.$alt.'" />';
				$html .= '</a>';
			} else {
				$html = '<img src="images/downarrow0.png" width="16" height="16" border="0" alt="'.$alt.'" />';
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
			$show = true;
		} else
			if ($order < 0) {
				$img = 'uparrow-1.png';
				$show = true;
			} else {
				$img = 'uparrow.png';
				$show = true;
			};
		if ($show) {
			$output = '<a href="javascript:void listItemTask(\'cb'.$id.'\',\'orderup\')" title="'.$alt.'">';
			$output .= '<img src="images/'.$img.'" width="16" height="16" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

			return $output;
		} else {
			return '&nbsp;';
		}
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
			$show = true;
		} else
			if ($order < 0) {
				$img = 'downarrow-1.png';
				$show = true;
			} else {
				$img = 'downarrow.png';
				$show = true;
			};
		if ($show) {
			$output = '<a href="javascript:void listItemTask(\'cb'.$id.'\',\'orderdown\')" title="'.$alt.'">';
			$output .= '<img src="images/'.$img.'" width="16" height="16" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

			return $output;
		} else {
			return '&nbsp;';
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
			$link = $base.'$amp;limit='.$this->limit;
		} else {
			$base = $this->_link;
			$link = $this->_link.'&amp;limit='.$this->limit;
		}

		$data->all	= new JPaginationObject(JText::_('View All'));
		if (!$this->_viewall) {
			$data->all->base	= '0';
			$data->all->link	= JURI::resolve($base."&amp;limit=0&amp;limitstart=0");
		}

		// Set the start and previous data objects
		$data->start	= new JPaginationObject(JText::_('Start'));
		$data->previous	= new JPaginationObject(JText::_('Prev'));
		if ($this->get('pages.current') > 1) {
			$page = ($this->get('pages.current') -2) * $this->limit;
			$data->start->base	= '0';
			$data->start->link	= JURI::resolve($link."&amp;limitstart=0");
			$data->previous->base	= $page;
			$data->previous->link	= JURI::resolve($link."&amp;limitstart=".$page);
		}

		// Set the next and end data objects
		$data->next	= new JPaginationObject(JText::_('Next'));
		$data->end	= new JPaginationObject(JText::_('End'));
		if ($this->get('pages.current') < $this->get('pages.total')) {
			$page = $this->get('pages.current') * $this->limit;
			$endPage = ($this->get('pages.total') -1) * $this->limit;
			$data->next->base	= $page;
			$data->next->link	= JURI::resolve($link."&amp;limitstart=".$page);
			$data->end->base	= $endPage;
			$data->end->link	= JURI::resolve($link."&amp;limitstart=".$endPage);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++) {
			$page = ($i -1) * $this->limit;
			$data->pages[$i] = new JPaginationObject($i);
			if ($i != $this->get('pages.current')) {
				$data->pages[$i]->base	= $page;
				$data->pages[$i]->link	= JURI::resolve($link."&amp;limitstart=".$page);
			}
		}
		return $data;
	}

	/**
	 * Writes the dropdown select list for number of rows to show per page
	 * Use: print $pagination->getLimitBox();
	 *
	 * @deprecated as of 1.5
	 */
	function writeLimitBox($link = null) {
		echo $this->getLimitBox($link);
	}

	/**
	 * Writes the counter string
	 * Use: print $pagination->getLimitBox();
	 *
	 * @deprecated as of 1.5
	 */
	function writePagesCounter() {
		echo $this->getPagesCounter();
	}

	/**
	 * Writes the page list string
	 * Use: print $pagination->getPagesLinks();
	 *
	 * @deprecated as of 1.5
	 */
	function writePagesLinks($link = null) {
		echo $this->getPagesLinks($link);
	}

	/**
	 * Returns the pagination offset at an index
	 * Use: $pagination->getRowOffset($index); instead
	 *
	 * @deprecated as of 1.5
	 */
	function rowNumber($index) {
		return $index +1 + $this->limitstart;
	}
}

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
?>