<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

global $task;

// Initialize some variables
$config		=& JFactory::getConfig();
$user		=& JFactory::getUser();
$db			=& JFactory::getDBO();
$lang		=& JFactory::getLanguage();
$session	=& JFactory::getSession();

$sid	= $session->getId();
$output = array();

// Legacy Mode
if (defined('_JLEGACY')) {
	$output[] = '<span class="legacy-mode">'.JText::_('Legacy').': '._JLEGACY.'</span>';
}

// Print the preview button
$output[] = "<span class=\"preview\"><a href=\"".$mainframe->getSiteURL()."\" target=\"_blank\">".JText::_('Preview')."</a></span>";

// Get the number of unread messages in your inbox
$query = 'SELECT COUNT(*)'
. ' FROM #__messages'
. ' WHERE state = 0'
. ' AND user_id_to = '.(int) $user->get('id');
$db->setQuery( $query );
$unread = $db->loadResult();

if (JRequest::getInt('hidemainmenu')) {
	$inboxLink = '<a>';
} else {
	$inboxLink = '<a href="index.php?option=com_messages">';
}

// Print the inbox message
if ($unread) {
	$output[] = $inboxLink.'<span class="unread-messages">'.$unread.'</span></a>';
} else {
	$output[] = $inboxLink.'<span class="no-unread-messages">'.$unread.'</span></a>';
}

// Get the number of logged in users
$query = 'SELECT COUNT( session_id )'
. ' FROM #__session'
. ' WHERE guest <> 1'
;
$db->setQuery($query);
$online_num = intval( $db->loadResult() );

//Print the logged in users message
$output[] = "<span class=\"loggedin-users\">".$online_num."</span>";

if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu') ) {
	 // Print the logout message
	 $output[] = "<span class=\"logout\">".JText::_('Logout')."</span>";
} else {
	// Print the logout message
	$output[] = "<span class=\"logout\"><a href=\"index.php?option=com_login&amp;task=logout\">".JText::_('Logout')."</a></span>";
}

// reverse rendering order for rtl display
if ( $lang->isRTL() ) {
	$output = array_reverse( $output );
}

// output the module
foreach ($output as $item){
	echo $item;
}