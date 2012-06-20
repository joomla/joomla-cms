<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hideLinks	= JRequest::getBool('hidemainmenu');
$output = array();

// Print the logged in users.
if ($params->get('show_loggedin_users', 1)) :
	$output[] = '<div class="btn-group loggedin-users">'.JText::plural('MOD_STATUS_USERS', $online_num).'</div>';
endif;

// Print the back-end logged in users.
if ($params->get('show_loggedin_users_admin', 1)) :
	$output[] = '<div class="btn-group backloggedin-users">'.JText::plural('MOD_STATUS_BACKEND_USERS', $count).'</div>';
endif;

//  Print the inbox message.
if ($params->get('show_messages', 1)) :
	$output[] = '<div class="btn-group '.$inboxClass.'">'.
			($hideLinks ? '' : '<a href="'.$inboxLink.'">').
			'<i class="icon-envelope"></i> '.
			JText::plural('MOD_STATUS_MESSAGES', $unread).
			($hideLinks ? '' : '</a>').
			'</div>';
endif;

// Output the items.
foreach ($output as $item) :
	echo $item;
endforeach;
