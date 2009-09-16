<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$db			= &JFactory::getDbo();
$session		= &JFactory::getSession();

$session_id = $session->getId();

// Get no. of users online not including current session
$query = 'SELECT COUNT(session_id)'
. ' FROM #__session'
. ' WHERE session_id <> '.$db->Quote($session_id)
;
$db->setQuery($query);
$online_num = intval($db->loadResult());

echo $online_num . ' <img src="images/users.png" alt="'. JText::_('Users Online') .'" />';