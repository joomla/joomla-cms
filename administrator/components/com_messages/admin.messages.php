<?php
/**
* @version $Id: admin.messages.php 300 2005-10-02 05:46:21Z Levis $
* @package Joomla
* @subpackage Messages
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

require_once( $mainframe->getPath( 'admin_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$task	= mosGetParam( $_REQUEST, 'task' );
$cid	= mosGetParam( $_REQUEST, 'cid', array( 0 ) );
if (!is_array( $cid )) {
	$cid = array ( 0 );
}

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

	case 'save':
		saveMessage( $option );
		break;

	case 'remove':
		removeMessage( $cid, $option );
		break;

	case 'config':
		editConfig( $option );
		break;

	case 'saveconfig':
		saveConfig( $option );
		break;

	default:
		showMessages( $option );
		break;
}

function editConfig( $option ) {
	global $database, $my;

	$query = "SELECT cfg_name, cfg_value"
	. "\n FROM #__messages_cfg"
	. "\n WHERE user_id = $my->id"
	;
	$database->setQuery( $query );
	$data = $database->loadObjectList( 'cfg_name' );

	$vars 				= array();
	$vars['lock'] 		= mosHTML::yesnoSelectList( "vars[lock]", 'class="inputbox" size="1"', @$data['lock']->cfg_value );
	$vars['mail_on_new'] = mosHTML::yesnoSelectList( "vars[mail_on_new]", 'class="inputbox" size="1"', @$data['mail_on_new']->cfg_value );

	HTML_messages::editConfig( $vars, $option );

}

function saveConfig( $option ) {
	global $database, $my;

	$query = "DELETE FROM #__messages_cfg"
	. "\n WHERE user_id = $my->id"
	;
	$database->setQuery( $query );
	$database->query();

	$vars = mosGetParam( $_POST, 'vars', array() );
	foreach ($vars as $k=>$v) {
		$v = $database->getEscaped( $v );
		$query = "INSERT INTO #__messages_cfg"
		. "\n ( user_id, cfg_name, cfg_value )"
		. "\n VALUES ( $my->id, '$k', '$v' )"
		;
		$database->setQuery( $query );
		$database->query();
	}
	mosRedirect( "index2.php?option=$option" );
}

function newMessage( $option, $user, $subject ) {
	global $database, $mainframe, $my, $acl, $_LANG;

	// get available backend user groups
	$gid 	= $acl->get_group_id( 'Public Backend', 'ARO' );
	$gids 	= $acl->get_group_children( $gid, 'ARO', 'RECURSE' );
	$gids 	= implode( ',', $gids );

	// get list of usernames
	$recipients = array( mosHTML::makeOption( '0', '- '. JText::_( 'Select User' ) .' -' ) );
	$query = "SELECT id AS value, username AS text FROM #__users"
	. "\n WHERE gid IN ( $gids )"
	. "\n ORDER BY name"
	;
	$database->setQuery( $query );
	$recipients = array_merge( $recipients, $database->loadObjectList() );

	$recipientslist =
		mosHTML::selectList(
			$recipients,
			'user_id_to',
			'class="inputbox" size="1"',
			'value',
			'text',
			$user
		);
	HTML_messages::newMessage($option, $recipientslist, $subject );
}

function saveMessage( $option ) {
	global $database, $mainframe, $my;

	$row = new mosMessage( $database );
	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$row->send()) {
		mosRedirect( "index2.php?option=com_messages&mosmsg=" . $row->getError() );
	}
	mosRedirect( "index2.php?option=com_messages" );
}

function showMessages( $option ) {
	global $database, $mainframe, $my, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
	$search 	= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search 	= $database->getEscaped( trim( strtolower( $search ) ) );

	$wheres = array();
	$wheres[] = " a.user_id_to='$my->id'";

	if (isset($search) && $search!= "") {
		$wheres[] = "( u.username LIKE '%$search%' OR email LIKE '%$search%' OR u.name LIKE '%$search%' )";
	}

	$query = "SELECT COUNT(*)"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. ( $wheres ? " WHERE " . implode( " AND ", $wheres ) : '' )
	;
	$database->setQuery( $query );
	$total = $database->loadResult();

	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( $total, $limitstart, $limit  );

	$query = "SELECT a.*, u.name AS user_from"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. ($wheres ? "\n WHERE " . implode( " AND ", $wheres ) : "" )
	. "\n ORDER BY date_time DESC"
	. "\n LIMIT $pageNav->limitstart, $pageNav->limit"
	;
	$database->setQuery( $query );

	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		echo $database->stderr();
		return false;
	}

	HTML_messages::showMessages( $rows, $pageNav, $search, $option );
}

function viewMessage( $uid='0', $option ) {
	global $database, $my, $acl;

	$row = null;
	$query = "SELECT a.*, u.name AS user_from"
	. "\n FROM #__messages AS a"
	. "\n INNER JOIN #__users AS u ON u.id = a.user_id_from"
	. "\n WHERE a.message_id = $uid"
	. "\n ORDER BY date_time DESC"
	;
	$database->setQuery( $query );
	$database->loadObject( $row );

	$query = "UPDATE #__messages"
	. "\n SET state = 1"
	. "\n WHERE message_id = $uid"
	;
	$database->setQuery( $query );
	$database->query();

	HTML_messages::viewMessage( $row, $option );
}

function removeMessage( $cid, $option ) {
	global $database;
	;

	if (!is_array( $cid ) || count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select an item to delete' ) ."'); window.history.go(-1);</script>\n";
		exit;
	}
	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__messages"
		. "\n WHERE message_id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		}
	}

	$limit 		= intval( mosGetParam( $_REQUEST, 'limit', 10 ) );
	$limitstart	= intval( mosGetParam( $_REQUEST, 'limitstart', 0 ) );

	mosRedirect( "index2.php?option=$option&limit=$limit&limitstart=$limitstart" );
}
?>