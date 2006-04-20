<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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
	 * Constructor
	 */
	function __construct($total, $limitstart, $limit) {

		// Value/Type checking
		$this->total = intval($total);
		$this->limitstart = max($limitstart, 0);
		$this->limit = max($limit, 1);

		/*
		 * If we have less items then the limit set, then the start we always
		 * start from the beginning
		 */
		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}

		/*
		 * 
		 */
		if (($this->limit - 1) * $this->limitstart > $this->total) {
			$this->limitstart -= $this->limitstart % $this->limit;
		}
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
	function setTemplateVars(& $tmpl, $name = 'admin-list-footer', $link = null) {

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
	function writeLeafsCounter() {
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
	function getLimitBox($link = null) {
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
			$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"$this->limitstart\" />";
		} else {
			$link = sefRelToAbs($link.'&amp;limit=\' + this.options[selectedIndex].value + \'&amp;limitstart='.$this->limitstart);
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
	function getPagesCounter() {

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
	 * Create and return the pagination page list array
	 * 
	 * @access public
	 * @return array Pagination page list array
	 * @since 1.5
	 */
	function getPagesList($link = null) {
		global $mainframe;

		// Initialize variables
		$list = array();
		$link .= '&amp;limit='.$this->limit;

		$displayed_pages = 10;
		$total_pages = ceil($this->total / $this->limit);
		$this_page = ceil(($this->limitstart + 1) / $this->limit);
		$start_loop = (floor(($this_page -1) / $displayed_pages)) * $displayed_pages +1;

		if ($start_loop + $displayed_pages -1 < $total_pages) {
			$stop_loop = $start_loop + $displayed_pages -1;
		} else {
			$stop_loop = $total_pages;
		}

		if ($this_page > 1) {
			$page = ($this_page -2) * $this->limit;
			$list['first'] = array( 'start' => "0", 'url' => sefRelToAbs("$link&amp;limitstart=0"), 'txt' => JText::_('Start') );
			$list['prev'] = array( 'start' => "$page", 'url' => sefRelToAbs("$link&amp;limitstart=$page"), 'txt' => JText::_('Prev') );
		} else {
			$list['first'] = array( 'start' => null, 'url' => null, 'txt' => JText::_('Start') );
			$list['prev'] = array( 'start' => null, 'url' => null, 'txt' => JText::_('Prev') );
		}

		if ($this_page < $total_pages) {
			$page = $this_page * $this->limit;
			$end_page = ($total_pages -1) * $this->limit;
			$list['next'] = array( 'start' => "$page", 'url' => sefRelToAbs("$link&amp;limitstart=$page"), 'txt' => JText::_('Next') );
			$list['end'] = array( 'start' => "$end_page", 'url' => sefRelToAbs("$link&amp;limitstart=$end_page"), 'txt' => JText::_('End') );
		} else {
			$list['next'] = array( 'start' => null, 'url' => null, 'txt' => JText::_('Next') );
			$list['end'] = array( 'start' => null, 'url' => null, 'txt' => JText::_('End') );
		}

		$list['pages'] = array();
		for ($i = $start_loop; $i <= $stop_loop; $i ++) {
			$page = ($i -1) * $this->limit;
			if ($i == $this_page) {
				$list['pages'][$i] = array( 'start' => null, 'url' => null, 'txt' => "$i" );
			} else {
				$list['pages'][$i] = array( 'start' => "$page", 'url' => sefRelToAbs("$link&amp;limitstart=$page"), 'txt' => "$i" );
			}
		}
		return $list;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1 2 3 ... x
	 * 
	 * @access public
	 * @return string Pagination page list string
	 * @since 1.0
	 */
	function getPagesLinks($link = null) {
		global $mainframe;

		// Initialize variables
		$html = null;

		$displayed_pages = 10;
		$total_pages = ceil($this->total / $this->limit);
		$this_page = ceil(($this->limitstart + 1) / $this->limit);
		$start_loop = (floor(($this_page -1) / $displayed_pages)) * $displayed_pages +1;

		if ($start_loop + $displayed_pages -1 < $total_pages) {
			$stop_loop = $start_loop + $displayed_pages -1;
		} else {
			$stop_loop = $total_pages;
		}

		// Build the select list
		if ($mainframe->isAdmin()) {
			/*
			 * This is for page navigation if in administration section
			 */
			if ($this_page > 1) {
				$page = ($this_page -2) * $this->limit;
				$html .= "\n<a href=\"#beg\" class=\"pagenav\" title=\"".JText::_('first page')."\" onclick=\"javascript: document.adminForm.limitstart.value=0; document.adminForm.submit();return false;\"><< ".JText::_('Start')."</a>";
				$html .= "\n<a href=\"#prev\" class=\"pagenav\" title=\"".JText::_('previous page')."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\">< ".JText::_('Previous')."</a>";
			} else {
				$html .= "\n<span class=\"pagenav\">&lt;&lt; ".JText::_('Start')."</span>";
				$html .= "\n<span class=\"pagenav\">&lt; ".JText::_('Previous')."</span>";
			}

			for ($i = $start_loop; $i <= $stop_loop; $i ++) {
				$page = ($i -1) * $this->limit;
				if ($i == $this_page) {
					$html .= "\n<span class=\"pagenav\"> $i </span>";
				} else {
					$html .= "\n<a href=\"#$i\" class=\"pagenav\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"><strong>$i</strong></a>";
				}
			}

			if ($this_page < $total_pages) {
				$page = $this_page * $this->limit;
				$end_page = ($total_pages -1) * $this->limit;
				$html .= "\n<a href=\"#next\" class=\"pagenav\" title=\"".JText::_('next page')."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"> ".JText::_('Next')." ></a>";
				$html .= "\n<a href=\"#end\" class=\"pagenav\" title=\"".JText::_('end page')."\" onclick=\"javascript: document.adminForm.limitstart.value=$end_page; document.adminForm.submit();return false;\"> ".JText::_('End')." >></a>";
			} else {
				$html .= "\n<span class=\"pagenav\">".JText::_('Next')." &gt;</span>";
				$html .= "\n<span class=\"pagenav\">".JText::_('End')." &gt;&gt;</span>";
			}
		} else {
			/*
			 * This is for page navigation if not in the administration section
			 */
			$link .= '&amp;limit='.$this->limit;

			$pnSpace = "";
			if (JText::_('&lt') || JText::_('&gt'))
				$pnSpace = " ";

			if ($this_page > 1) {
				$page = ($this_page -2) * $this->limit;
				$html .= '<a href="'.sefRelToAbs("$link&amp;limitstart=0").'" class="pagenav" title="first page">'.JText::_('&lt').JText::_('&lt').$pnSpace.JText::_('Start').'</a> ';
				$html .= '<a href="'.sefRelToAbs("$link&amp;limitstart=$page").'" class="pagenav" title="previous page">'.JText::_('&lt').$pnSpace.JText::_('Prev').'</a> ';
			} else {
				$html .= '<span class="pagenav">'.JText::_('&lt').JText::_('&lt').$pnSpace.JText::_('Start').'</span> ';
				$html .= '<span class="pagenav">'.JText::_('&lt').$pnSpace.JText::_('Prev').'</span> ';
			}

			for ($i = $start_loop; $i <= $stop_loop; $i ++) {
				$page = ($i -1) * $this->limit;
				if ($i == $this_page) {
					$html .= '<span class="pagenav">'.$i.'</span> ';
				} else {
					$html .= '<a href="'.sefRelToAbs($link.'&amp;limitstart='.$page).'" class="pagenav"><strong>'.$i.'</strong></a> ';
				}
			}

			if ($this_page < $total_pages) {
				$page = $this_page * $this->limit;
				$end_page = ($total_pages -1) * $this->limit;
				$html .= '<a href="'.sefRelToAbs($link.'&amp;limitstart='.$page).' " class="pagenav" title="next page">'.JText::_('Next').$pnSpace.JText::_('&gt').'</a> ';
				$html .= '<a href="'.sefRelToAbs($link.'&amp;limitstart='.$end_page).' " class="pagenav" title="end page">'.JText::_('End').$pnSpace.JText::_('&gt').JText::_('&gt').'</a>';
			} else {
				$html .= '<span class="pagenav">'.JText::_('Next').$pnSpace.JText::_('&gt').'</span> ';
				$html .= '<span class="pagenav">'.JText::_('End').$pnSpace.JText::_('&gt').JText::_('&gt').'</span>';
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
	function getListFooter() {
		global $mainframe;
		
		$lang = $mainframe->getLanguage();

		$html = '<table class="adminlist"><tr><th colspan="3">';
		$html .= $this->getPagesLinks();
		$html .= '</th></tr><tr>';
		$html .= '<td nowrap="nowrap" width="48%" align="'.($lang->isRTL() ? 'left' : 'right').'">';
		$html .= JText::_('Display Num').'</td>';
		$html .= '<td>'.$this->getLimitBox().'</td>';
		$html .= '<td nowrap="nowrap" width="48%" >'.$this->getPagesCounter().'</td>';
		$html .= '</tr></table>';
		return $html;
	}

	/**
	 * Return the row number
	 * 
	 * @access public
	 * @param int $index The row index
	 * @return int Row number for given index
	 * @since 1.0
	 */
	function rowNumber($index) {
		return $index +1 + $this->limitstart;
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
	function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'Move Up') {

		$alt = JText::_($alt);

		if (($i > 0 || ($i + $this->limitstart > 0)) && $condition) {
			return '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
						<img src="images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'" /></a>';
		} else {
			return '&nbsp;';
		}
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
	function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'Move Down') {

		$alt = JText::_($alt);

		if (($i < $n -1 || $i + $this->limitstart < $this->total - 1) && $condition) {
			return '<a href="#reorder" onclick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
						<img src="images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'" /></a>';
		} else {
			return '&nbsp;';
		}
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
	function orderUpIcon2($id, $order, $condition = true, $task = 'orderup', $alt = '#') {

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
			$output .= '<img src="images/'.$img.'" width="12" height="12" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

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
	function orderDownIcon2($id, $order, $condition = true, $task = 'orderdown', $alt = '#') {

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
			$output .= '<img src="images/'.$img.'" width="12" height="12" border="0" alt="'.$alt.'" title="'.$alt.'" /></a>';

			return $output;
		} else {
			return '&nbsp;';
		}
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
}
?>