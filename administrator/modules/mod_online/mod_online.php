<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$db			=& JFactory::getDBO();
$session		=& JFactory::getSession();

$session_id = $session->getId();

// Get no. of users online not including current session
$query = 'SELECT COUNT( session_id )'
. ' FROM #__session'
. ' WHERE session_id <> '.$db->Quote($session_id)
;
$db->setQuery($query);
$online_num = intval( $db->loadResult() );

echo $online_num . ' <img src="images/users.png" align="middle" alt="'. JText::_( 'Users Online' ) .'" />';