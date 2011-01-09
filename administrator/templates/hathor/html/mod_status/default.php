<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// No direct access.
defined('_JEXEC') or die;

$output = array();

// Print the logged in users.
if ($params->get('show_loggedin_users', 1)) :
	$output[] = "<span class=\"loggedin-users\">".$online_num. " " . JText::_('MOD_STATUS_USERS') . "</span>";
endif;

// Print the back-end logged in users.
if ($params->get('show_loggedin_users_admin', 1)) :
	$output[] = "<span class=\"loggedin-users\">".$count. " " . JText::_('MOD_STATUS_BACKEND_USERS') . "</span>";
endif;

//  Print the inbox message.
if ($params->get('show_messages', 1)) :
	$output[] = "<span class=\"$inboxClass\"><a href=\"$inboxLink\">". $unread . " " . JText::_('MOD_STATUS_MESSAGES'). "</a></span>";
endif;

// Print the Preview link to Main site.
	$output[] = "<span class=\"viewsite\"><a href=\"".JURI::root()."\" target=\"_blank\">".JText::_('MOD_STATUS_VIEW_SITE')."</a></span>";

// Reverse rendering order for rtl display.
if ($lang->isRTL()) :
	$output = array_reverse($output);
endif;

// Output the items.
foreach ($output as $item) :
	echo $item;
endforeach;