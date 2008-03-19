<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Messages
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$task	= JRequest::getCmd( 'task' );
$cid	= JRequest::getVar( 'cid', array(0), '', 'array' );
JArrayHelper::toInteger($cid, array(0));

switch ($task)
{
	case 'view':
		viewMessage( $cid[0], $option );
		break;

	case 'add':
		newMessage( $option, NULL, NULL );
		break;

	case 'reply':
		newMessage(
			$option,
			JRequest::getVar( 'userid', 0, '', 'int' ),
			JRequest::getString( 'subject' )
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

function showMessages( $option )
{
	global $mainframe;

	$db					=& JFactory::getDBO();
	$user 				=& JFactory::getUser();

	$context			= 'com_messages.list';
	$filter_order		= $mainframe->getUserStateFromRequest( $context.'.filter_order',	'filter_order',		'a.date_time',	'cmd' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( $context.'.filter_order_Dir','filter_order_Dir',	'DESC',			'word' );
	$filter_state		= $mainframe->getUserStateFromRequest( $context.'.filter_state',	'filter_state',		'',				'word' );
	$limit				= $mainframe->getUserStateFromRequest( 'global.list.limit',			'limit',			$mainframe->getCfg('list_limit'), 'int' );
	$limitstart			= $mainframe->getUserStateFromRequest( $context.'.limitstart',		'limitstart',		0,				'int' );
	$search				= $mainframe->getUserStateFromRequest( $context.'search',			'search',			'',				'string' );
	$search				= JString::strtolower( $search );

	$where = array();
	$where[] = ' a.user_id_to='.(int) $user->get('id');

	if ($search != '') {
		$searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		$where[] = '( u.username LIKE '.$searchEscaped.' OR email LIKE '.$searchEscaped.' OR u.name LIKE '.$searchEscaped.' )';
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = 'a.state = 1';
		} else if ($filter_state == 'U' ) {
			$where[] = 'a.state = 0';
		}
	}

	$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', a.date_time DESC';

	$query = 'SELECT COUNT(*)'
	. ' FROM #__messages AS a'
	. ' INNER JOIN #__users AS u ON u.id = a.user_id_from'
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = 'SELECT a.*, u.name AS user_from'
	. ' FROM #__messages AS a'
	. ' INNER JOIN #__users AS u ON u.id = a.user_id_from'
	. $where
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	// state filter
	$lists['state']	= JHTML::_('grid.state',  $filter_state, 'Read', 'Unread' );

	// table ordering
	$lists['order_Dir']	= $filter_order_Dir;
	$lists['order']		= $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_messages::showMessages( $rows, $pageNav, $option, $lists );
}

function editConfig( $option )
{
	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	$query = 'SELECT cfg_name, cfg_value'
	. ' FROM #__messages_cfg'
	. ' WHERE user_id = '.(int) $user->get('id')
	;
	$db->setQuery( $query );
	$data = $db->loadObjectList( 'cfg_name' );

	// initialize values if they do not exist
	if (!isset($data['lock']->cfg_value)) {
		$data['lock']->cfg_value 		= 0;
	}
	if (!isset($data['mail_on_new']->cfg_value)) {
		$data['mail_on_new']->cfg_value = 0;
	}
	if (!isset($data['auto_purge']->cfg_value)) {
		$data['auto_purge']->cfg_value 	= 7;
	}

	$vars 					= array();
	$vars['lock'] 			= JHTML::_('select.booleanlist',  "vars[lock]", '', $data['lock']->cfg_value, 'yes', 'no', 'varslock' );
	$vars['mail_on_new'] 	= JHTML::_('select.booleanlist',  "vars[mail_on_new]", '', $data['mail_on_new']->cfg_value, 'yes', 'no', 'varsmail_on_new' );
	$vars['auto_purge'] 	= $data['auto_purge']->cfg_value;

	HTML_messages::editConfig( $vars, $option );

}

function saveConfig( $option )
{
	global $mainframe;

	// Check for request forgeries
	JRequest::checkToken() or jexit( 'Invalid Token' );

	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	$query = 'DELETE FROM #__messages_cfg'
	. ' WHERE user_id = '.(int) $user->get('id')
	;
	$db->setQuery( $query );
	$db->query();

	$vars = JRequest::getVar( 'vars', array(), 'post', 'array' );
	foreach ($vars as $k=>$v) {
		$v = $db->getEscaped( $v );
		$query = 'INSERT INTO #__messages_cfg'
		. ' ( user_id, cfg_name, cfg_value )'
		. ' VALUES ( '.(int) $user->get('id').', '.$db->Quote($k).', '.$db->Quote($v).' )'
		;
		$db->setQuery( $query );
		$db->query();
	}
	$mainframe->redirect( "index.php?option=$option" );
}

function newMessage( $option, $user, $subject )
{
	$db		=& JFactory::getDBO();
	$acl	=& JFactory::getACL();

	// get available backend user groups
	$gid 	= $acl->get_group_id( 'Public Backend', 'ARO' );
	$gids 	= $acl->get_group_children( $gid, 'ARO', 'RECURSE' );
	JArrayHelper::toInteger($gids, array(0));
	$gids 	= implode( ',', $gids );

	// get list of usernames
	$recipients = array( JHTML::_('select.option',  '0', '- '. JText::_( 'Select User' ) .' -' ) );
	$query = 'SELECT id AS value, username AS text FROM #__users'
	. ' WHERE gid IN ( '.$gids.' )'
	. ' ORDER BY name'
	;
	$db->setQuery( $query );
	$recipients = array_merge( $recipients, $db->loadObjectList() );

	$recipientslist =
		JHTML::_('select.genericlist', $recipients, 'user_id_to', 'class="inputbox" size="1"', 'value', 'text', $user);
	HTML_messages::newMessage($option, $recipientslist, $subject );
}

function saveMessage( $option )
{
	global $mainframe;

	// Check for request forgeries
	JRequest::checkToken() or jexit( 'Invalid Token' );

	require_once(dirname(__FILE__).DS.'tables'.DS.'message.php');

	$db =& JFactory::getDBO();
	$row = new TableMessage( $db );

	if (!$row->bind(JRequest::get('post'))) {
		JError::raiseError(500, $row->getError() );
	}

	if (!$row->check()) {
		JError::raiseError(500, $row->getError() );
	}

	if (!$row->send()) {
		$mainframe->redirect( "index.php?option=com_messages", $row->getError() );
	}
	$mainframe->redirect( "index.php?option=com_messages" );
}

function viewMessage( $uid='0', $option )
{
	$db	=& JFactory::getDBO();

	$query = 'SELECT a.*, u.name AS user_from'
	. ' FROM #__messages AS a'
	. ' INNER JOIN #__users AS u ON u.id = a.user_id_from'
	. ' WHERE a.message_id = '.(int) $uid
	. ' ORDER BY date_time DESC'
	;
	$db->setQuery( $query );
	$row = $db->loadObject();

	$query = 'UPDATE #__messages'
	. ' SET state = 1'
	. ' WHERE message_id = '.(int) $uid
	;
	$db->setQuery( $query );
	$db->query();

	HTML_messages::viewMessage( $row, $option );
}

function removeMessage( $cid, $option )
{
	global $mainframe;

	// Check for request forgeries
	JRequest::checkToken() or jexit( 'Invalid Token' );

	$db =& JFactory::getDBO();

	JArrayHelper::toInteger($cid);

	if (count( $cid ) < 1) {
		JError::raiseError(500, JText::_( 'Select an item to delete' ) );
	}

	if (count( $cid ))
	{
		$cids = implode( ',', $cid );
		$query = 'DELETE FROM #__messages'
		. ' WHERE message_id IN ( '. $cids .' )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg(true)."'); window.history.go(-1); </script>\n";
		}
	}

	$limit 		= JRequest::getVar( 'limit', 10, '', 'int' );
	$limitstart	= JRequest::getVar( 'limitstart', 0, '', 'int' );

	$mainframe->redirect( 'index.php?option='.$option.'&limit='.$limit.'&limitstart='.$limitstart );
}