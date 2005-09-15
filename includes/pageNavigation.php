<?php
/**
* @version $Id: pageNavigation.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
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
	var $limit = null;
	/** @var int Total number of rows */
	var $total = null;

	function mosPageNav( $total, $limitstart, $limit ) {
		$this->total = intval( $total );
		$this->limitstart = max( $limitstart, 0 );
		$this->limit = max( $limit, 0 );
	}

	/**
	* Returns the html limit # input box
	* @param string The basic link to include in the href
	* @return string
	*/

	function getLimitBox ( $link ) {
		$limits = array();
		for ($i=5; $i <= 30; $i+=5) {
			$limits[] = mosHTML::makeOption( "$i" );
		}
		$limits[] = mosHTML::makeOption( "50" );

		// build the html select list
		$link = sefRelToAbs($link.'&amp;limit=\' + this.options[selectedIndex].value + \'&amp;limitstart='.$this->limitstart);
		return mosHTML::selectList( $limits, 'limit', 'class="inputbox" size="1" onchange="document.location.href=\''.$link.'\';"', 'value', 'text', $this->limit );
	}

	/**
	* Writes the html limit # input box
	* @param string The basic link to include in the href
	*/
	function writeLimitBox ( $link ) {
		echo mosPageNav::getLimitBox( $link );
	}

	/**
	* Writes the html for the pages counter, eg, Results 1-10 of x
	*/
	function writePagesCounter() {
		global $_LANG;
		$txt = '';
		$from_result = $this->limitstart+1;
		if ($this->limitstart + $this->limit < $this->total) {
			$to_result = $this->limitstart + $this->limit;
		} else {
			$to_result = $this->total;
		}
		if ($this->total > 0) {
			$txt .= $_LANG->sprintf( 'Results X-Y of Z', $from_result, $to_result, $this->total );
		}
		return $txt;
	}

	/**
	* Writes the html for the leafs counter, eg, Page 1 of x
	*/
	function writeLeafsCounter() {
		global $_LANG;
		$txt = '';
		$page = $this->limitstart+1;
		if ($this->total > 0) {
			$txt .= sprintf( $_LANG->_( 'Page X of Y'), $page, $this->total );
		}
		return $txt;
	}

	/**
	* Writes the html links for pages, eg, previous, next, 1 2 3 ... x
	* @param string The basic link to include in the href
	* @param int mode of output, 1 (default) is text output, 0 is Javascript that requires a form of name adminForm
	*/
	function writePagesLinks( $link, $mode=1 ) {
		global $_LANG;
		$txt = '';

		$displayed_pages = 10;
		$total_pages = ceil( $this->total / $this->limit );
		$this_page = ceil( ($this->limitstart+1) / $this->limit );
		$start_loop = (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
		if ( $start_loop + $displayed_pages - 1 < $total_pages ) {
			$stop_loop = $start_loop + $displayed_pages - 1;
		} else {
			$stop_loop = $total_pages;
		}

		$link .= '&amp;limit='. $this->limit;

		// previous and first links
		if ( $this_page > 1 ) {
			$page = ($this_page - 2) * $this->limit;
			if ( $mode ) {
				$txt .= '<a href="'. sefRelToAbs( "$link&amp;limitstart=0" ) .'" class="pagenav" title="first page">&lt;&lt; '. $_LANG->_( 'Start' ) .'</a> ';
				$txt .= '<a href="'. sefRelToAbs( "$link&amp;limitstart=$page" ) .'" class="pagenav" title="previous page">&lt; '. $_LANG->_( 'Prev' ) .'</a> ';
			} else {
				$txt .= '<a href="#beg" class="pagenav" title="'. $_LANG->_( 'first page' ) .'" onclick="javascript: document.adminForm.limitstart.value=0; document.adminForm.submit();return false;"><< '. $_LANG->_( 'Start' ) .'</a>';
				$txt .= '<a href="#prev" class="pagenav" title="'. $_LANG->_( 'previous page' ) .'" onclick="javascript: document.adminForm.limitstart.value='. $page .'; document.adminForm.submit();return false;">< '. $_LANG->_( 'Prev' ) .'</a>';
			}
		} else {
			$txt .= '<span class="pagenav">&lt;&lt; '. $_LANG->_( 'Start' ) .'</span> ';
			$txt .= '<span class="pagenav">&lt; '. $_LANG->_( 'Prev' ) .'</span> ';
		}

		// numbering links
		for ( $i=$start_loop; $i <= $stop_loop; $i++ ) {
			$page = ($i - 1) * $this->limit;
			if ($i == $this_page) {
				$txt .= '<span class="pagenav">'. $i .'</span> ';
			} else {
				if ( $mode ) {
					$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $page ) .'" class="pagenav"><strong>'. $i .'</strong></a> ';
				} else {
					$txt .= '<a href="#'. $i .'" class="pagenav" onclick="javascript: document.adminForm.limitstart.value='. $page .'; document.adminForm.submit();return false;"><strong>'. $i .'</strong></a>';
				}
			}
		}

		// next and end links
		if ( $this_page < $total_pages ) {
			$page = $this_page * $this->limit;
			$end_page = ($total_pages-1) * $this->limit;
			if ( $mode ) {
				$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $page ) .' " class="pagenav" title="next page">'. $_LANG->_( 'Next' ) .' &gt;</a> ';
				$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $end_page ) .' " class="pagenav" title="end page">'. $_LANG->_( 'End' ) .' &gt;&gt;</a>';
			} else {
				$txt .= '<a href="#next" class="pagenav" title="'. $_LANG->_( 'next page' ) .'" onclick="javascript: document.adminForm.limitstart.value='. $page .'; document.adminForm.submit();return false;"> '. $_LANG->_( 'NEXT' ) .' ></a>';
				$txt .= '<a href="#end" class="pagenav" title="'. $_LANG->_( 'end page' ) .'" onclick="javascript: document.adminForm.limitstart.value='. $end_page .'; document.adminForm.submit();return false;"> '. $_LANG->_( 'END' ) .' >></a>';
			}
		} else {
			$txt .= '<span class="pagenav">'. $_LANG->_( 'Next' ) .' &gt;</span> ';
			$txt .= '<span class="pagenav">'. $_LANG->_( 'End' ) .' &gt;&gt;</span>';
		}
		return $txt;
	}
}
?>