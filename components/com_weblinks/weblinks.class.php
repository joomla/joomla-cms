<?php
/**
* @version $Id: weblinks.class.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Category database table class
* @package Joomla
* @subpackage Weblinks
*/
class mosWeblink extends mosDBTable {
	/** @var int Primary key */
	var $id=null;
	/** @var int */
	var $catid=null;
	/** @var int */
	var $sid=null;
	/** @var string */
	var $title=null;
	/** @var string */
	var $url=null;
	/** @var string */
	var $description=null;
	/** @var datetime */
	var $date=null;
	/** @var int */
	var $hits=null;
	/** @var int */
	var $published=null;
	/** @var boolean */
	var $checked_out=null;
	/** @var time */
	var $checked_out_time=null;
	/** @var int */
	var $ordering=null;
	/** @var int */
	var $archived=null;
	/** @var int */
	var $approved=null;
	/** @var string */
	var $params=null;

	/**
	* @param database A database connector object
	*/
	function mosWeblink( &$db ) {
		$this->mosDBTable( '#__weblinks', 'id', $db );
	}

	/** overloaded check function */
	function check() {
		global $_LANG;

		// filter malicious code
		$ignoreList = array( 'params' );
		$this->filter( $ignoreList );

		// specific filters
		$iFilter = new InputFilter();
		if ($iFilter->badAttributeValue( array( 'href', $this->url ))) {
			$this->_error = $_LANG->_( 'WEBLINK_URL' );
			return false;
		}

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = $_LANG->_( 'WEBLINK_TITLE' );
			return false;
		}

		if ( !( eregi( 'http://', $this->url ) || ( eregi( 'https://', $this->url ) ) || ( eregi( 'ftp://', $this->url ) ) ) ) {
			$this->url = 'http://'.$this->url;
		}

		// check for existing name
		$query = "SELECT id FROM #__weblinks "
		. "\n WHERE title = '$this->title'"
		. "\n AND catid = '$this->catid'"
		;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );

		if ($xid && $xid != intval( $this->id )) {
			$this->_error = $_LANG->_( 'WEBLINK_EXIST' );
			return false;
		}
		return true;
	}

	/**
	 * Retrieves a data view.
	 *
	 * categories:
	 * list of categories and the number of links in each
	 *
	 * weblinks:
	 * list of weblinks
	 *
	 * @param string The view name
	 * @return array
	 */
	function getView( $view, $options=array() ) {
		global $my;

		$wheres = array();
		if ($published = mosGetParam( $options, 'published' )) {
			$wheres[] = 'a.published = 1';
			$wheres[] = 'a.approved = 1';
			$wheres[] = 'a.archived = 0';
		}

		switch ($view) {
			case 'categories':
				if ($published) {
					$wheres[] = 'cc.published = 1';
				}
				$query = "SELECT cc.*, COUNT( a.id ) AS numlinks"
				. "\n FROM #__categories AS cc"
				. "\n LEFT JOIN #__weblinks AS a ON a.catid = cc.id"
				. "\n WHERE " . implode( ' AND ', $wheres )
				. "\n AND section = 'com_weblinks'"
				. "\n AND cc.access <= '$my->gid' "
				. "\n GROUP BY cc.id"
				. "\n ORDER BY cc.ordering"
				;
				break;

			case 'items':
				if ($catid = mosGetParam( $options, 'published' )) {
					$wheres[] = 'a.catid = ' . intval( $catid );
				}
				$orderby = mosGetParam( $options, 'orderby' );

				$query = "SELECT a.*"
				. "\n FROM #__weblinks AS a"
				. (count( $wheres ) > 0 ? "\n WHERE " . implode( ' AND ', $wheres ) : '')
				. ($orderby ? "\n ORDER BY " . $orderby : '')
				;

			default:
				break;
		}

		$limitstart = mosGetParam( $options, 'limitstart', 0 );
		$limit = mosGetParam( $options, 'limit', 0 );

		$this->_db->setQuery( $query, $limitstart, $limit );

		return $this->_db->loadObjectList();
	}
}
?>