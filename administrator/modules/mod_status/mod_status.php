<?php
/**
 * @version		$Id: mod_status.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

global $task;

// Initialize some variables
$config		= &JFactory::getConfig();
$user		= &JFactory::getUser();
$db			= &JFactory::getDbo();
$lang		= &JFactory::getLanguage();
$session	= &JFactory::getSession();

$sid	= $session->getId();
$output = array();

// Print the preview button
$output[] = "<span class=\"preview\"><a href=\"".JURI::root()."\" target=\"_blank\">".JText::_('Preview')."</a></span>";

// Get the number of unread messages in your inbox
$query = 'SELECT COUNT(*)'
. ' FROM #__messages'
. ' WHERE state = 0'
. ' AND user_id_to = '.(int) $user->get('id');
$db->setQuery($query);
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
$query = 'SELECT COUNT(session_id)'
. ' FROM #__session'
. ' WHERE guest <> 1'
;
$db->setQuery($query);
$online_num = intval($db->loadResult());

//Print the logged in users message
$output[] = "<span class=\"loggedin-users\">".$online_num."</span>";

if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu')) {
	 // Print the logout message
	 $output[] = "<span class=\"logout\">".JText::_('Logout')."</span>";
} else {
	// Print the logout message
	$output[] = "<span class=\"logout\"><a href=\"index.php?option=com_login&amp;task=logout\">".JText::_('Logout')."</a></span>";
}

// reverse rendering order for rtl display
if ($lang->isRTL()) {
	$output = array_reverse($output);
}

// output the module
foreach ($output as $item){
	echo $item;
}