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
		return mosHTML::selectList( $limits, 'limit',
			'class="inputbox" size="1" onchange="document.location.href=\''.$link.'\';"',
			'value', 'text', $this->limit );
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
		$txt = '';
		$from_result = $this->limitstart+1;
		if ($this->limitstart + $this->limit < $this->total) {
			$to_result = $this->limitstart + $this->limit;
		} else {
			$to_result = $this->total;
		}
		if ($this->total > 0) {
        	$txt .= sprintf( JText::_( 'Results of' ), $from_result, $to_result, $this->total );
		}
		return $txt;
	}

	/**
	* Writes the html for the leafs counter, eg, Page 1 of x
	*/
	function writeLeafsCounter() {
		$txt = '';
		$page = $this->limitstart+1;
		if ($this->total > 0) {
			$txt .= JText::_( 'Page' ) ." ". $page ." ". JText::_( 'of' ) ." ". $this->total;
		}
		return $txt;
	}

	/**
	* Writes the html links for pages, eg, previous, next, 1 2 3 ... x
	* @param string The basic link to include in the href
	*/
	function writePagesLinks( $link ) {
		$txt = '';

		$displayed_pages = 10;
		$total_pages = ceil( $this->total / $this->limit );
		$this_page = ceil( ($this->limitstart+1) / $this->limit );
		$start_loop = (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
		if ($start_loop + $displayed_pages - 1 < $total_pages) {
			$stop_loop = $start_loop + $displayed_pages - 1;
		} else {
			$stop_loop = $total_pages;
		}

		$link .= '&amp;limit='. $this->limit;

        $pnSpace = "";
        if (JText::_( '&lt' ) || JText::_( '&gt' )) $pnSpace = " ";

		if ($this_page > 1) {
			$page = ($this_page - 2) * $this->limit;
			$txt .= '<a href="'. sefRelToAbs( "$link&amp;limitstart=0" ) .'" class="pagenav" title="first page">'. JText::_( '&lt' ) . JText::_( '&lt' ) . $pnSpace . JText::_( 'Start' ) .'</a> ';
			$txt .= '<a href="'. sefRelToAbs( "$link&amp;limitstart=$page" ) .'" class="pagenav" title="previous page">'. JText::_( '&lt' ) . $pnSpace . JText::_( 'Prev' ) .'</a> ';
		} else {
			$txt .= '<span class="pagenav">'. JText::_( '&lt' ) . JText::_( '&lt' ) . $pnSpace . JText::_( 'Start' ) .'</span> ';
			$txt .= '<span class="pagenav">'. JText::_( '&lt' ) . $pnSpace . JText::_( 'Prev' ) .'</span> ';
		}

		for ($i=$start_loop; $i <= $stop_loop; $i++) {
			$page = ($i - 1) * $this->limit;
			if ($i == $this_page) {
				$txt .= '<span class="pagenav">'. $i .'</span> ';
			} else {
				$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $page ) .'" class="pagenav"><strong>'. $i .'</strong></a> ';
			}
		}

		if ($this_page < $total_pages) {
			$page = $this_page * $this->limit;
			$end_page = ($total_pages-1) * $this->limit;
			$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $page ) .' " class="pagenav" title="next page">'. JText::_( 'Next' ) . $pnSpace . JText::_( '&gt' ) .'</a> ';
			$txt .= '<a href="'. sefRelToAbs( $link .'&amp;limitstart='. $end_page ) .' " class="pagenav" title="end page">'. JText::_( 'End' ) . $pnSpace . JText::_( '&gt' ) . JText::_( '&gt' ) .'</a>';
		} else {
			$txt .= '<span class="pagenav">'. JText::_( 'Next' ) . $pnSpace . JText::_( '&gt' ) .'</span> ';
			$txt .= '<span class="pagenav">'. JText::_( 'End' ) . $pnSpace . JText::_( '&gt' ) . JText::_( '&gt' ) .'</span>';
		}
		return $txt;
	}
	/**
	 * Sets the vars {PAGE_LINKS}, {PAGE_LIST_OPTIONS} and {PAGE_COUNTER} for the page navigation template
	 * @param object The patTemplate object
	 * @param string The full link to be used in the nav link, eg index.php?option=com_content
	 * @param string The name of the template to add the variables
	 */
	function setTemplateVars( &$tmpl, $link = '', $name = 'admin-list-footer' ) {
		$tmpl->addVar( $name, 'PAGE_LINKS', $this->writePagesLinks( $link ) );
		$tmpl->addVar( $name, 'PAGE_LIST_OPTIONS', $this->getLimitBox( $link ) );
		$tmpl->addVar( $name, 'PAGE_COUNTER', $this->writePagesCounter() );
	}
}
?>
