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
?>
