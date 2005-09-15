<?php
/**
* @version $Id: admin.messages.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Messages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@class' );
mosFS::load( '@admin_html' );

switch ($task) {
	case 'view':
		viewMessage( $cid[0], $option );
		break;

	case 'new':
		newMessage( $option, NULL, NULL );
		break;

	case 'reply':
		newMessage(
			$option,
			mosGetParam( $_REQUEST, 'userid', 0 ),
			mosGetParam( $_REQUEST, 'subject', '' )
		);
		break;

	case 'send':
		sendMessage( $option );
		break;

	case 'remove':
		removeMessage( $cid, $option );
		break;

	case 'config':
		editConfig( $option );
		break;

	case 'saveconfig':
	case 'applyconfig':
		saveConfig( $option );
		break;

	default:
		showMessages( $option );
		break;
}

function showMessages( $option ) {
	global $database, $mainframe, $my, $mosConfig_list_limit;

	$limit 			= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 		= $database->getEscaped( trim( strtolower( $search ) ) );
	$tOrder			= mosGetParam( $_POST, 'tOrder', 'a.date_time' );
	$tOrder_old		= mosGetParam( $_POST, 'tOrder_old', 'a.date_time' );

	// table column ordering values
	$tOrderDir = mosGetParam( $_POST, 'tOrderDir', 'DESC' );
	if ( $tOrderDir == 'ASC' ) {
		$lists['tOrderDir'] 	= 'DESC';
	} else {
		$lists['tOrderDir'] 	= 'ASC';
	}
	$lists['tOrder'] 		= $tOrder;

	$wheres = array();
	$wheres[] = " a.user_id_to = '$my->id'";

	if ( isset( $search ) && $search != '' ) {
		$wheres[] = "( u.username LIKE '%$search%' OR email LIKE '%$search%' OR u.name LIKE '%$search%' )";
	}

	// table column ordering
	switch ( $tOrder ) {
		default:
			$order = "\n ORDER BY $tOrder $tOrderDir, a.date_time DESC";
			break;
	}

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. ( $wheres ? " WHERE " . implode( " AND ", $wheres ) : '' )
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	// load navigation files
	mosFS::load( '@pageNavigationAdmin' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	// main query
	$query = "SELECT a.*, u.name AS user_from"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. ( $wheres ? "\n WHERE " . implode( " AND ", $wheres ) : "" )
	. $order
	;
	$database->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		mosErrorAlert( $database->stderr() );
	}

	HTML_messages::showMessages( $rows, $pageNav, $search, $option, $lists );
}

function editConfig( $option ) {
	global $database, $my, $mainframe;

	$mainframe->set('disableMenu', true);

	$query = "SELECT cfg_name, cfg_value"
	. "\n FROM #__messages_cfg"
	. "\n WHERE user_id = '$my->id'"
	;
	$database->setQuery( $query );
	$data = $database->loadObjectList( 'cfg_name' );

	$vars = array();
	$vars['lock'] 			= mosHTML::yesnoRadioList( 'vars[lock]', 'class="inputbox" size="1"', @$data['lock']->cfg_value );
	$vars['mail_on_new'] 	= mosHTML::yesnoRadioList( 'vars[mail_on_new]', 'class="inputbox" size="1"', @$data['mail_on_new']->cfg_value );

	HTML_messages::editConfig( $vars, $option );
}

function saveConfig( $option ) {
	global $database, $my;
	global $_LANG;

	$task = mosGetParam( $_REQUEST, 'task', '' );

	$query = "DELETE FROM #__messages_cfg"
	. "\n WHERE user_id = '$my->id'"
	;
	$database->setQuery( $query );
	$database->query();

	$vars = mosGetParam( $_POST, 'vars', array() );
	foreach ($vars as $k=>$v) {
		$v = $database->getEscaped( $v );
		$query = "INSERT INTO #__messages_cfg"
		. "\n ( user_id, cfg_name, cfg_value ) VALUES ( '$my->id', '$k', '$v' )"
		;
		$database->setQuery( $query );
		$database->query();
	}

	$msg = $_LANG->_( 'Settings Saved' );
	switch ( $task ) {
		case 'applyconfig':
			mosRedirect( 'index2.php?option=com_messages&task=config', $msg );

		case 'saveconfig':
		default:
			mosRedirect( 'index2.php?option=com_messages', $msg );
	}
}

function newMessage( $option, $user, $subject ) {
	global $database, $mainframe, $my, $acl;
	global $_LANG;

	$mainframe->set('disableMenu', true);

	// get available backend user groups
	$gid 	= $acl->get_group_id( null, 'Public Backend', 'ARO' );
	$gids 	= $acl->get_group_children( $gid, 'ARO', 'RECURSE' );
	$gids 	= implode( ',', $gids );

	// get list of usernames
	$recipients = array( mosHTML::makeOption( '0', '- '. $_LANG->_( 'Select User' ) .' -' ) );
	$query = "SELECT id AS value, username AS text FROM #__users"
	."\n WHERE gid IN ( $gids )"
	. "\n ORDER BY name"
	;
	$database->setQuery( $query );
	$recipients = array_merge( $recipients, $database->loadObjectList() );

	$recipientslist = mosHTML::selectList( $recipients, 'user_id_to', 'class="inputbox" size="1"', 'value', 'text', $user );

	HTML_messages::newMessage($option, $recipientslist, $subject );
}

function sendMessage( $option ) {
	global $database, $mainframe, $my;
	global $_LANG;

	$row = new mosMessage( $database );
	if (!$row->bind( $_POST )) {
		mosErrorAlert( $row->getError() );
	}

	if (!$row->send()) {
		mosErrorAlert( $row->getError() );
	}

	$msg = $_LANG->_( 'Message Saved' );
	mosRedirect( 'index2.php?option=com_messages', $msg );
}

function viewMessage( $uid='0', $option ) {
	global $database, $my, $acl, $mainframe;

	$mainframe->set('disableMenu', true);

	$row = null;
	$query = "SELECT a.*, u.name AS user_from"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. "\n WHERE a.message_id = '$uid'"
	. "\n ORDER BY date_time DESC"
	;
	$database->setQuery( $query );
	$database->loadObject( $row );

	$query = "UPDATE #__messages"
	. "\n SET state='1' WHERE message_id = '$uid'"
	;
	$database->setQuery( $query );
	$database->query();

	HTML_messages::viewMessage( $row, $option );
}

function removeMessage( $cid, $option ) {
	global $database;
	global $_LANG;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		mosErrorAlert( $_LANG->_( 'Select an item to delete' ) );
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__messages"
		. "\n WHERE message_id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			mosErrorAlert( $database->getErrorMsg() );
		}
	}

	$limit 		= intval( mosGetParam( $_REQUEST, 'limit', 10 ) );
	$limitstart	= intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );

	mosRedirect( "index2.php?option=$option&limit=$limit&limitstart=$limitstart" );
}

?>