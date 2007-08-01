<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright		Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.pagination');

$db				=& JFactory::getDBO();
$user			=& JFactory::getUser();

// TODO -  pagination needs to be completed in module
$limit 		= $mainframe->getUserStateFromRequest('limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
$limitstart = $mainframe->getUserStateFromRequest('mod_logged.limitstart', 'limitstart', 0, 'int');

// hides Administrator or Super Administrator from list depending on usertype
$and = '';
// administrator check
if ( $user->get('gid') == 24 ) {
	$and = ' AND gid != "25"';
}
// manager check
if ( $user->get('gid') == 23 ) {
	$and = ' AND gid != "25"';
	$and .= ' AND gid != "24"';
}

// get the total number of records
$query = 'SELECT COUNT(*)'
	. ' FROM #__session'
	. ' WHERE userid != 0'
	. $and
	. ' ORDER BY usertype, username'
	;
$db->setQuery( $query );
$total = $db->loadResult();

// page navigation
$pageNav = new JPagination( $total, $limitstart, $limit );

$query = 'SELECT username, time, userid, usertype, client_id'
. ' FROM #__session'
. ' WHERE userid != 0'
. $and
. ' ORDER BY usertype, username'
;
$db->setQuery( $query );
$rows = $db->loadObjectList();

require( dirname( __FILE__ ).DS.'tmpl'.DS.'default.php' );
