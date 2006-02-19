<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Initialize some variables
 */
$sid		= JSession::id();
$user	= $mainframe->getUser();
$db		= & $mainframe->getDBO();

/*
 * Get the number of unread messages in your inbox
 */
$query = "SELECT COUNT(*)"
. "\n FROM #__messages"
. "\n WHERE state = 0"
. "\n AND user_id_to = ".$user->get('id');
$db->setQuery( $query );
$unread = $db->loadResult();

/*
 * Print the inbox message
 */
if ($unread)
{
	echo "<span class=\"unread-messages\"><a href=\"index2.php?option=com_messages\">$unread</a></span>";
} else
{
	echo "<span class=\"no-unread-messages\"><a href=\"index2.php?option=com_messages\">$unread</a></span>";
}

/*
 * Get the number of logged in users not including yourself
 */
$query = "SELECT COUNT( session_id )"
. "\n FROM #__session"
. "\n WHERE session_id <> '$sid'"
;
$db->setQuery($query);
$online_num = intval( $db->loadResult() );

/*
 * Print the logged in users message
 */
echo "<span class=\"loggedin-users\">".$online_num."</span>";

/*
 * Print the logout message
 */
 echo "<span class=\"logout\"><a href=\"index2.php?option=logout\">".JText::_('Logout')."</a></span>";
 ?>