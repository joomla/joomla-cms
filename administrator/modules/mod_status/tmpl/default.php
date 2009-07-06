<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	mod_status
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$output = array();

// Print the Preview link to Main site.
	$output[] = "<span class=\"viewsite\"><a href=\"".JURI::root()."\" target=\"_blank\">".JText::_('View site')."</a></span>";

//  Print the inbox message.
	$output[] = "<span class=\"$inboxClass\"><a href=\"$inboxLink\">". $unread . " " . JText::_('Msgs'). "</a></span>";

// Print the logged in users.
	$output[] = "<span class=\"loggedin-users\">".$online_num."</span>";

// Print the logout link.
	$output[] = "<span class=\"logout\"><a href=\"$logoutLink\">".JText::_('Logout')."</a></span>";

// Reverse rendering order for rtl display.
if ($lang->isRTL()) {
	$output = array_reverse($output);
}

// Output the items.
foreach ($output as $item){
	echo $item;
}