<?php
/**
 * @version $Id: content.php 2503 2006-02-20 14:04:42Z Jinx $
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * Base search class
 * @package Joomla
 * @since 1.1
 */
class JSearch extends JObject {
	/**
	 * @var string The text or phrase to search for
	 */
	var $text = null;

	/**
	 * @var string The ordering for the search
	 */
	var $ordering = null;

	/**
	 * @var string The match type: all|any|exact
	 */
	var $matchType = null;

	/**
	 * @var int The pagination for the complete result set
	 */
	var $limitstart = null;

	/**
	 * @var int The total number of rows to return
	 */
	var $limit = null;

	/**
	 * @var int The number of results already returned
	 */
	var $count = null;

	/**
	 * @var array An array of results
	 */
	var $results = null;

	/**
	 * @var int
	 * @access private
	 */
	var $_queryLimitStart = null;

	/**
	 * @var int
	 * @access private
	 */
	var $_queryLimit = null;

	/**
	 * Constructor
	 */
	function __construct( $text, $matchType, $ordering, $areas, $limitstart=0, $limit=0 ) {
		$this->text			= $text;
		$this->matchType	= $matchType;
		$this->ordering		= $ordering;
		$this->areas		= $areas;
		$this->limitstart	= $limitstart;
		$this->limit		= $limit;
		
		$this->count		= 0;
		$this->results		= array();
	}

	/**
	 * @param int
	 */
	function setLimitStart( $value ) {
		$this->limitstart = 0;
	}

	/**
	 * @param int
	 */
	function setLimit( $value ) {
		$this->limit = 0;
	}

	/**
	 * @return string
	 */
	function getOrdering() {
		return $this->ordering;
	}

	/**
	 * @return string
	 */
	function getMatchType() {
		return $this->matchType;
	}

	/**
	 * @return string
	 */
	function getText() {
		return $this->text;
	}

	/**
	 * @return array
	 */
	function getAreas() {
		return $this->areas;
	}

	/**
	 * Adds the total number of returns of the current search plugin to the
	 * running total and determines the limit and limitstart for the query
	 * @param int
	 */
	function addResultCount( $n ) {
		// the start and end points for this query
		$qStart = $this->count;
		$this->count += $n;
		$qEnd = $this->count;

		// the count for the start and end of the search
		$sStart	= $this->limitstart;
		$sEnd	= $this->limitstart + $this->limit;

//echo "<br>n=$n | qStart=$qStart, qEnd=$qEnd | sStart=$sStart, sEnd=$sEnd<br>";

		$this->_queryLimitStart	= 0;
		$this->_queryLimit		= 0;

		if ($n == 0) {
			// do nothing
		} else if ($qEnd < $sStart) {
			// not yet at the right page of results
			// do nothing
		} else if ($qStart >= $sEnd) {
			// we are passed the end of the page
			// do nothing
		} else if ($qStart < $sStart) {
			// these results clip the beginning of the results page
			$this->_queryLimitStart	= $sStart - $qStart;
			$this->_queryLimit		= max( $this->limit, $qEnd - $this->_queryLimitStart );
		} else if ($qStart >= $sStart ) {
			// these results start within the results page
			$this->_queryLimitStart	= $qStart;
			$this->_queryLimit		= max( $this->limit, $qEnd - $this->_queryLimitStart );
		}
	}

	/**
	 * @return int
	 */
	function getResultCount() {
		return $this->count;
	}

	/**
	 * Returns the limit start for the next search query
	 * @return int
	 */
	function getQueryLimitStart() {
		// used to skip over the paged results
		return $this->_queryLimitStart;
	}

	/**
	 * Returns the limit for the next search query
	 * @return int
	 */
	function getQueryLimit() {
		// used to return how many records to actually get from the current query
		return $this->_queryLimit;
	}

	/**
	 * @param array An array of object results
	 */
	function addResults( $array ) {
		$this->results = array_merge( $this->results, $array );
	}

	/**
	 * @return array
	 */
	function getResults() {
		return $this->results;
	}
}

/**
 * Base search class
 * @package Joomla
 * @since 1.1
 */
class JSearchHelper {
	/**
	 * @return JParameter Parameters for the serach plugin
	 */
	function &getPluginParams( $folder, $name ) {
		if ($folder && $name) {
	 		$plugin =& JPluginHelper::getPlugin($this->folder, $this->name); 
	 		$pluginParams = new JParameter( $plugin->params );
		} else {
	 		$pluginParams = new JParameter( '' );
		}

	 	return $pluginParams;
	}

	/**
	 * @param array A flat array of search areas
	 * @param array A named array of search areas for the plugin
	 * @return boolean
	 */
	function inArea( $formAreas, $pluginAreas ) {
		if (is_array( $formAreas )) {
			if (!array_intersect( $formAreas, array_keys( $pluginAreas ) )) {
				return false;
			}
		}
		return true;
	}
}
?>