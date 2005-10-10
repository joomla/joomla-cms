<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Page navigation support class
* @package Joomla
*/
class mosPageNav {
	/** @var int The record number to start dislpaying from */
	var $limitstart = null;
	/** @var int Number of rows to display per page */
	var $limit 		= null;
	/** @var int Total number of rows */
	var $total 		= null;

	function mosPageNav( $total, $limitstart, $limit ) {
		$this->total 		= intval( $total );
		$this->limitstart 	= max( $limitstart, 0 );
		$this->limit 		= max( $limit, 1 );
		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}
		if (($this->limit-1)*$this->limitstart > $this->total) {
			$this->limitstart -= $this->limitstart % $this->limit;
		}
	}
	/**
	* @return string The html for the limit # input box
	*/
	function getLimitBox () {
		$limits = array();
		for ($i=5; $i <= 30; $i+=5) {
			$limits[] = mosHTML::makeOption( "$i" );
		}
		$limits[] = mosHTML::makeOption( "50" );

		// build the html select list
		$html = mosHTML::selectList( $limits, 'limit', 'class="inputbox" size="1" onchange="document.adminForm.submit();"',
		'value', 'text', $this->limit );
		$html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"$this->limitstart\" />";
		return $html;
	}
	/**
	* Writes the html limit # input box
	*/
	function writeLimitBox () {
		echo mosPageNav::getLimitBox();
	}
	function writePagesCounter() {
		echo $this->getPagesCounter();
	}
	/**
	* @return string The html for the pages counter, eg, Results 1-10 of x
	*/
	function getPagesCounter() {
		global $_LANG;

		$html = '';
		$from_result = $this->limitstart+1;
		if ($this->limitstart + $this->limit < $this->total) {
			$to_result = $this->limitstart + $this->limit;
		} else {
			$to_result = $this->total;
		}
		if ($this->total > 0) {
			$html .= "\n". $_LANG->_( 'Results' ) ." ". $from_result ." - ". $to_result ." ". $_LANG->_( 'of' ) ." ". $this->total;
		} else {
			$html .= "\n". $_LANG->_( 'No records found' );
		}
		return $html;
	}
	/**
	* Writes the html for the pages counter, eg, Results 1-10 of x
	*/
	function writePagesLinks() {
		echo $this->getPagesLinks();
	}
	/**
	* @return string The html links for pages, eg, previous, next, 1 2 3 ... x
	*/
	function getPagesLinks() {
		global $_LANG;

		$html 				= '';
		$displayed_pages 	= 10;
		$total_pages 		= ceil( $this->total / $this->limit );
		$this_page 			= ceil( ($this->limitstart+1) / $this->limit );
		$start_loop 		= (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
		if ($start_loop + $displayed_pages - 1 < $total_pages) {
			$stop_loop = $start_loop + $displayed_pages - 1;
		} else {
			$stop_loop = $total_pages;
		}

		if ($this_page > 1) {
			$page = ($this_page - 2) * $this->limit;
			$html .= "\n<a href=\"#beg\" class=\"pagenav\" title=\"". $_LANG->_( 'first page' ) ."\" onclick=\"javascript: document.adminForm.limitstart.value=0; document.adminForm.submit();return false;\"><< ". $_LANG->_( 'Start' ) ."</a>";
			$html .= "\n<a href=\"#prev\" class=\"pagenav\" title=\"". $_LANG->_( 'previous page' ) ."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\">< ". $_LANG->_( 'Previous' ) ."</a>";
		} else {
			$html .= "\n<span class=\"pagenav\"><< ". $_LANG->_( 'Start' ) ."</span>";
			$html .= "\n<span class=\"pagenav\">< ". $_LANG->_( 'Previous' ) ."</span>";
		}

		for ($i=$start_loop; $i <= $stop_loop; $i++) {
			$page = ($i - 1) * $this->limit;
			if ($i == $this_page) {
				$html .= "\n<span class=\"pagenav\"> $i </span>";
			} else {
				$html .= "\n<a href=\"#$i\" class=\"pagenav\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"><strong>$i</strong></a>";
			}
		}

		if ($this_page < $total_pages) {
			$page = $this_page * $this->limit;
			$end_page = ($total_pages-1) * $this->limit;
			$html .= "\n<a href=\"#next\" class=\"pagenav\" title=\"". $_LANG->_( 'next page' ) ."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"> ". $_LANG->_( 'Next' ) ." ></a>";
			$html .= "\n<a href=\"#end\" class=\"pagenav\" title=\"". $_LANG->_( 'end page' ) ."\" onclick=\"javascript: document.adminForm.limitstart.value=$end_page; document.adminForm.submit();return false;\"> ". $_LANG->_( 'End' ) ." >></a>";
		} else {
			$html .= "\n<span class=\"pagenav\">". $_LANG->_( 'Next' ) ." ></span>";
			$html .= "\n<span class=\"pagenav\">". $_LANG->_( 'End' ) ." >></span>";
		}
		return $html;
	}

	function getListFooter() {
		global $_LANG;

		$html = '<table class="adminlist"><tr><th colspan="3">';
		$html .= $this->getPagesLinks();
		$html .= '</th></tr><tr>';
		$html .= '<td nowrap="true" width="48%" align="right">'. $_LANG->_( 'Display Num' ) .'</td>';
		$html .= '<td>' .$this->getLimitBox() . '</td>';
		$html .= '<td nowrap="true" width="48%" align="left">' . $this->getPagesCounter() . '</td>';
		$html .= '</tr></table>';
  		return $html;
	}
/**
* @param int The row index
* @return int
*/
	function rowNumber( $i ) {
		return $i + 1 + $this->limitstart;
	}
/**
* @param int The row index
* @param string The task to fire
* @param string The alt text for the icon
* @return string
*/
	function orderUpIcon( $i, $condition=true, $task='orderup', $alt='Move Up' ) {
		global $_LANG;
        $alt = $_LANG->_( $alt );

		if (($i > 0 || ($i+$this->limitstart > 0)) && $condition) {
			return '<a href="#reorder" onClick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
				<img src="images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'">
			</a>';
  		} else {
  			return '&nbsp;';
		}
	}
/**
* @param int The row index
* @param int The number of items in the list
* @param string The task to fire
* @param string The alt text for the icon
* @return string
*/
	function orderDownIcon( $i, $n, $condition=true, $task='orderdown', $alt='Move Down' ) {
		global $_LANG;
        $alt = $_LANG->_( $alt );

		if (($i < $n-1 || $i+$this->limitstart < $this->total-1) && $condition) {
			return '<a href="#reorder" onClick="return listItemTask(\'cb'.$i.'\',\''.$task.'\')" title="'.$alt.'">
				<img src="images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'">
			</a>';
  		} else {
  			return '&nbsp;';
		}
	}

	/**
	 * @param int The row index
	 * @param string The task to fire
	 * @param string The alt text for the icon
	 * @return string
	 */
	function orderUpIcon2( $id, $order, $condition=true, $task='orderup', $alt='#' ) {
		global $_LANG;

		// handling of default value
		if ($alt = '#') {
			$alt = $_LANG->_( 'Move Up' );
		}

		if ($order == 0) {
			$img = 'uparrow0.png';
			$show = true;
		} else if ($order < 0) {
			$img = 'uparrow-1.png';
			$show = true;
		} else {
			$img = 'uparrow.png';
			$show = true;
		};
		if ($show) {
			$output = '<a href="#ordering" onClick="listItemTask(\'cb'.$id.'\',\'orderup\')" title="'. $alt .'">';
			$output .= '<img src="images/' . $img . '" width="12" height="12" border="0" alt="'. $alt .'" title="'. $alt .'" /></a>';

			return $output;
   		} else {
  			return '&nbsp;';
		}
	}

	/**
	 * @param int The row index
	 * @param int The number of items in the list
	 * @param string The task to fire
	 * @param string The alt text for the icon
	 * @return string
	 */
	function orderDownIcon2( $id, $order, $condition=true, $task='orderdown', $alt='#' ) {
		global $_LANG;

		// handling of default value
		if ($alt = '#') {
			$alt = $_LANG->_( 'Move Down' );
		}

		if ($order == 0) {
			$img = 'downarrow0.png';
			$show = true;
		} else if ($order < 0) {
			$img = 'downarrow-1.png';
			$show = true;
		} else {
			$img = 'downarrow.png';
			$show = true;
		};
		if ($show) {
			$output = '<a href="#ordering" onClick="listItemTask(\'cb'.$id.'\',\'orderdown\')" title="'. $alt .'">';
			$output .= '<img src="images/' . $img . '" width="12" height="12" border="0" alt="'. $alt .'" title="'. $alt .'" /></a>';

			return $output;
  		} else {
  			return '&nbsp;';
		}
	}

	/**
	 * Sets the vars for the page navigation template
	 */
	function setTemplateVars( &$tmpl, $name = 'admin-list-footer' ) {
		$tmpl->addVar( $name, 'PAGE_LINKS', $this->getPagesLinks() );
		$tmpl->addVar( $name, 'PAGE_LIST_OPTIONS', $this->getLimitBox() );
		$tmpl->addVar( $name, 'PAGE_COUNTER', $this->getPagesCounter() );
	}
}
?>