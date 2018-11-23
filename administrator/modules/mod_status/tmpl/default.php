<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hideLinks = $input->getBool('hidemainmenu');
$task      = $input->getCmd('task');
$output    = array();

// Print the Preview link to Main site.
if ($params->get('show_viewsite', 1))
{
	// Gets the FrontEnd Main page Uri
	$frontEndUri = JUri::getInstance(JUri::root());
	$frontEndUri->setScheme(((int) JFactory::getApplication()->get('force_ssl', 0) === 2) ? 'https' : 'http');

	$output[] = '<div class="btn-group viewsite">'
		. '<a href="' . $frontEndUri->toString() . '" target="_blank">'
		. '<span class="icon-out-2" aria-hidden="true"></span>' . JText::_('JGLOBAL_VIEW_SITE')
		. '</a>'
		. '<span class="btn-group separator"></span>'
		. '</div>';
}

// Print the link to open a new Administrator window.
if ($params->get('show_viewadmin', 0))
{
	$output[] = '<div class="btn-group viewsite">'
		. '<a href="' . JUri::base() . 'index.php" target="_blank">'
		. '<span class="icon-out-2" aria-hidden="true"></span>' . JText::_('MOD_STATUS_FIELD_LINK_VIEWADMIN_LABEL')
		. '</a>'
		. '<span class="btn-group separator"></span>'
		. '</div>';
}

// Print logged in user count based on the shared session state
if (JFactory::getConfig()->get('shared_session', '0'))
{
	// Print the frontend logged in  users.
	if ($params->get('show_loggedin_users', 1))
	{
		$output[] = '<div class="btn-group loggedin-users">'
			. '<span class="badge">' . $total_users . '</span>'
			. JText::plural('MOD_STATUS_TOTAL_USERS', $total_users)
			. '<span class="btn-group separator"></span>'
			. '</div>';
	}
}
else
{
	// Print the frontend logged in  users.
	if ($params->get('show_loggedin_users', 1))
	{
		$output[] = '<div class="btn-group loggedin-users">'
			. '<span class="badge">' . $online_num . '</span>'
			. JText::plural('MOD_STATUS_USERS', $online_num)
			. '<span class="btn-group separator"></span>'
			. '</div>';
	}

	// Print the backend logged in users.
	if ($params->get('show_loggedin_users_admin', 1))
	{
		$output[] = '<div class="btn-group backloggedin-users">'
			. '<span class="badge">' . $count . '</span>'
			. JText::plural('MOD_STATUS_BACKEND_USERS', $count)
			. '<span class="btn-group separator"></span>'
			. '</div>';
	}
}

//  Print the inbox message.
if ($params->get('show_messages', 1))
{
	$active   = $unread ? ' badge-warning' : '';
	$output[] = '<div class="btn-group ' . $inboxClass . '">'
		. ($hideLinks ? '' : '<a href="' . $inboxLink . '">')
		. '<span class="badge' . $active . '">' . $unread . '</span>'
		. JText::plural('MOD_STATUS_MESSAGES_LABEL', $unread)
		. ($hideLinks ? '' : '</a>')
		. '<span class="btn-group separator"></span>'
		. '</div>';
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
	$output[] = '<div class="btn-group logout">'
		. ($hideLinks ? '' : '<a href="' . $logoutLink . '">')
		. '<span class="icon-minus-2" aria-hidden="true"></span>' . JText::_('JLOGOUT')
		. ($hideLinks ? '' : '</a>')
		. '</div>';
}

// Output the items.
foreach ($output as $item)
{
	echo $item;
}
