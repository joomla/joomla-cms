<?php
/**
* @version $Id: admin.checkin.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Checkin
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@admin_html' );

/**
 * Utility function to the the name of a user
 * @param int The user id
 * @return int
 */
function getUserName( $id ){
	global $database;

	$row = new mosUser( $database );
	$row->load( $id );

	return $row->username;
}

/**
 * Utility function to get the name of an item
 * @param string The name of the table
 * @param string The id of the item
 * @param string The name of the primary key of the table
 * @return string
 */
function getItemName( $table, $id, $tbKey ){
	global $database, $_CONFIG;

	$dbprefix 	= $database->getPrefix();
	$coreTable 	= 0;

	switch ( $table ){
		case $dbprefix .'modules':
		case $dbprefix .'polls':
		case $dbprefix .'content':
		case $dbprefix .'categories':
			$coreTable 	= 1;
			$name_field = 'title';
			break;

		case $dbprefix .'banner':
		case $dbprefix .'sections':
		case $dbprefix .'mambots':
		case $dbprefix .'menu':
		case $dbprefix .'bannerclient':
		case $dbprefix .'contact_details':
			$coreTable 	= 1;
			$name_field = 'name';
			break;

		default:
			$coreTable 	= 0;
			$name_field = '';
			break;
	}

	if ( $coreTable == '1' ) {
		$query = "SELECT $name_field"
		. "\n FROM $table"
		. "\n WHERE $tbKey = '$id'"
		;
		$database->SetQuery( $query );
		return $database->loadResult();
	} else {
		return 'Unknown Item';
	}
}

/**
 * @package Languages
 * @subpackage Languages
 */
class checkinTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function checkinTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'checkinList' );

		// set task level access control
		$this->setAccessControl( 'com_checkin', 'manage' );
	}

	/**
	* checkinOptions
	*/
	function checkinList() {
		global $mainframe, $database, $option;
		global $_LANG;

		mosFS::load( '@pageNavigationAdmin' );
		$table			= $mainframe->getUserStateFromRequest( 'table', 'table', '0' );
		$limit			= $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mainframe->getCfg( 'list_limit' ) );
		$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
		$search			= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
		$search			= $database->getEscaped( trim( strtolower( $search ) ) );
		$filter_by		= $mainframe->getUserStateFromRequest( "filter_by{$option}", 'filter_by', '' );
		$filter_table	= $mainframe->getUserStateFromRequest( "filter_table{$option}", 'filter_table', '' );
		$orderCol		= mosGetParam( $_REQUEST, 'orderCol' , 'item');
		$orderDirn		= mosGetParam( $_REQUEST, 'orderDirn', 1 );

		$vars = array (
			'orderCol' 		=> $orderCol,
			'orderDirn' 	=> $orderDirn,
		);

		$rows = array();
		$tables = $database->getTableList();
		$allFields = $database->getTableFields( $tables );

		foreach ($tables as $table) {
			if (!preg_match( '/^' . $database->getPrefix() . '/i', $table )) {
				continue;
			}

			$fields = array_keys( $allFields[$table] );
			$tbKey = $fields[0];

			$foundCO = in_array( 'checked_out', $fields );
			$foundCOT = in_array( 'checked_out_time', $fields );

			if ($foundCO && $foundCOT) {
				$query 	= "SELECT $tbKey, checked_out, checked_out_time"
				. "\n FROM $table"
				. "\n WHERE checked_out > 0"
				;
				$database->setQuery( $query );
				$items = $database->loadObjectList();

				foreach ($items as $item) {
					$item->item				= getItemName( $table, $item->$tbKey, $tbKey );
					$item->table		 	= $table;
					$item->checked_out_by 	= getUserName( $item->checked_out );
					$item->checked_out_date	= mosFormatDate( $item->checked_out_time, '%d-%b-%Y' );
					$item->checked_out_time	= mosFormatDate( $item->checked_out_time, '%H:%m' );
					$item->tbKeyA		 	= $item->$tbKey;
					$item->tbKeyB		 	= $tbKey;
					$rows[] 				= $item;
				}

				if ( count( $items) ) {
				// used for table dropdown
					$checked_tables[] = $table;
				}
			}
		}

		// by dropdown list only of those users who have items checked out
		// limits list instead of pulling a complete list from mos_users db
		unset( $temp );
		$temp = array();
		foreach( $rows as $row ) {
			$temp[] = $row->checked_out;
		}
		sort( $temp );
		$total 	= count( $temp );
		$ids[] 	= $temp[0];
		for( $i=1; $i < $total; $i++ ) {
			$z = $i - 1;
			if ( $temp[$z] != $temp[$i] ) {
				$ids[] = $temp[$i];
			}
		}
		$by[] = mosHTML::makeOption( '', '- ' . $_LANG->_( 'Checked Out By' ) . ' -' );
		foreach( $ids as $id ) {
			$by[] = mosHTML::makeOption( $id, getUserName( $id ) );
		}
		$lists['by'] = mosHTML::selectList( $by, 'filter_by', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_by );

		// table dropdown list only of those tables who have items checked out
		// limits list instead of pulling a complete list of tables db
		unset( $tables );
		$tables[] = mosHTML::makeOption( '', '- ' . $_LANG->_( 'Tables' ) . ' -' );
		foreach( $checked_tables as $table ) {
			$tables[] = mosHTML::makeOption( $table, $table );
		}
		$lists['table'] = mosHTML::selectList( $tables, 'filter_table', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_table );

		$lists['search'] = $search;

		// search filtering
		$temp = array();
		if ($search) {
			foreach( $rows as $row ) {
				if ( strstr( strtolower( $row->item ), $search ) ) {
					$temp[] = $row;
				}
			}
			$rows = $temp;
		}
		unset( $temp );
		// by filtering
		$temp = array();
		if ($filter_by) {
			foreach( $rows as $row ) {
				if ( $row->checked_out == $filter_by ) {
					$temp[] = $row;
				}
			}
			$rows = $temp;
		}
		unset( $temp );
		// table filtering
		$temp = array();
		if ($filter_table) {
			foreach( $rows as $row ) {
				if ( $row->table == $filter_table ) {
					$temp[] = $row;
				}
			}
			$rows = $temp;
		}

		$total = count( $rows );
		$pageNav = new mosPageNav( $total, $limitstart, $limit );
		$rows = array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

		// sort array of objects by ordering
		if ( !$orderDirn ) {
			$orderDirn = -1;
		}
		SortArrayObjects( $rows, $orderCol, $orderDirn );

		checkinScreens::checkinList( $rows, $pageNav, $lists, $vars );
	}

	function checkin(){
		global $database, $_LANG, $mosConfig_zero_date;

		$cid = mosGetParam( $_POST, 'cid', array(0) );

		if (!is_array( $cid )) {
			$cid = array(0);
		}

		foreach ( $cid as $item ) {
			$parts = explode( ',', $item );

			$query = "UPDATE $parts[0]"
			. "\n SET checked_out = 0, checked_out_time = '$mosConfig_zero_date'"
			. "\n WHERE $parts[1] = $parts[2]"
			;
			$database->setQuery( $query );
			if ($database->query()) {
				echo ' ok';
			}
		}

		$msg = $_LANG->_( 'DESCITEMCHECKEDIN' );
		$this->setRedirect( 'index2.php?option=com_checkin', $msg );
	}
}

$tasker = new checkinTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>