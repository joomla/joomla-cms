<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hideLinks = $input->getBool('hidemainmenu');
$task      = $input->getCmd('task');
$output    = array();

// Print the Preview link to Main site.
if ($params->get('show_viewsite', 1))
{
	$output[] = '<div class="btn-group viewsite"><a href="' . JUri::root() . '" target="_blank"><i class="icon-share-alt"></i> ' . JText::_('JGLOBAL_VIEW_SITE') . '</a></div><div class="btn-group divider">
	</div>';
}

// Print the logged in users.
if ($params->get('show_loggedin_users', 1))
{
	$output[] = '<div class="btn-group loggedin-users">' . JText::plural('MOD_STATUS_USERS', $online_num) . '</div><div class="btn-group divider">
	</div>';
}

// Print the back-end logged in users.
if ($params->get('show_loggedin_users_admin', 1))
{
	$output[] = '<div class="btn-group backloggedin-users">' . JText::plural('MOD_STATUS_BACKEND_USERS', $count) . '</div><div class="btn-group divider">
	</div>';
}

//  Print the inbox message.
if ($params->get('show_messages', 1))
{
	$output[] = '<div class="btn-group ' . $inboxClass . '">' .
			($hideLinks ? '' : '<a href="' . $inboxLink . '">') .
			'<i class="icon-envelope"></i> ' .
			JText::plural('MOD_STATUS_MESSAGES', $unread) .
			($hideLinks ? '' : '</a>') .
			'<div class="btn-group divider"></div>' .
			'</div>';
}


// Print the logout link.
if ($task == 'edit' || $task == 'editA' || $input->getInt('hidemainmenu'))
{
	$logoutLink = '';
}
else
{
	$logoutLink = JRoute::_('index.php?option=com_login&task=logout&' . JSession::getFormToken() . '=1');
}

if ($params->get('show_logout', 1))
{
	$output[] = '<div class="btn-group logout">' . ($hideLinks ? '' : '<a href="' . $logoutLink . '"><i class="icon-minus-2"></i> ') . JText::_('JLOGOUT') . ($hideLinks ? '' : '</a>') . '</div>';
}

// Output the items.
foreach ($output as $item)
{
	echo $item;
}
