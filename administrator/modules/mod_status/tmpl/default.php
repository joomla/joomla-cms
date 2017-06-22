<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$hideLinks = $input->getBool('hidemainmenu');
$task      = $input->getCmd('task');
$output    = array();

// Print logged in user count based on the shared session state
if (JFactory::getConfig()->get('shared_session', '0'))
{
	// Print the frontend logged in  users.
	if ($params->get('show_loggedin_users', 1))
	{
		$output[] = '<li class="px-2">'
			. '<span class="mr-1 ml-1 badge badge-pill badge-default">' . $total_users . '</span>'
			. JText::plural('MOD_STATUS_TOTAL_USERS', $total_users)
			. '</li>';
	}
}
else
{
	// Print the frontend logged in  users.
	if ($params->get('show_loggedin_users', 1))
	{
		$output[] = '<li class="px-2">'
			. '<span class="mr-1 ml-1 badge badge-pill badge-default">' . $online_num . '</span>'
			. JText::plural('MOD_STATUS_USERS', $online_num)
			. '</li>';
	}

	// Print the backend logged in users.
	if ($params->get('show_loggedin_users_admin', 1))
	{
		$output[] = '<li class="px-2">'
			. '<span class="mr-1 ml-1 badge badge-pill badge-default">' . $count . '</span>'
			. JText::plural('MOD_STATUS_BACKEND_USERS', $count)
			. '</li>';
	}
}

// Output the items.
foreach ($output as $item)
{
	echo $item;
}
