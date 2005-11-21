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
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$showmode 	= $params->get( 'showmode' );
if(!$showmode || $showmode =='0') $showmode=0;

$content 	= '';

if ($showmode==0 || $showmode==2) {
	$query = "SELECT COUNT( session_id ) AS guest_online"
	. "\n FROM #__session"
	. "\n WHERE guest = 1"
	. "\n AND ( usertype is NULL OR usertype = '' )";
	$database->setQuery( $query );
	$guest_array = $database->loadResult();

	$query = "SELECT COUNT( DISTINCT( username ) ) AS user_online"
	. "\n FROM #__session"
	. "\n WHERE guest = 0"
	;
	$database->setQuery( $query );
	$user_array = $database->loadResult();

	if ($guest_array<>0 && $user_array==0) {
		if ($guest_array==1) {
        	$content = sprintf( JText::_( 'We have guest online' ), $guest_array );
			eval ("\$content = \"$content\";");
		} else {
        	$content = sprintf( JText::_( 'We have guests online' ), $guest_array );
			eval ("\$content = \"$content\";");
		}
	}

	if ($guest_array==0 && $user_array<>0) {
		if ($user_array==1) {
        	$content = sprintf( JText::_( 'We have member online' ), $user_array );
			eval ("\$content = \"$content\";");
		} else {
        	$content = sprintf( JText::_( 'We have members online' ), $user_array );
			eval ("\$content = \"$content\";");
		}
	}

	if ($guest_array<>0 && $user_array<>0) {
		if ($guest_array==1) {
        	$content = sprintf( JText::_( 'We have guest and' ), $guest_array );
			eval ("\$content = \"$content\";");
		} else {
        	$content = sprintf( JText::_( 'We have guests and' ), $guest_array );
			eval ("\$content = \"$content\";");
		}

		if ($user_array==1) {
        	$content = sprintf( JText::_( 'member online' ), $user_array );
			eval ("\$content = \"$content\";");
		} else {
        	$content = sprintf( JText::_( 'members online' ), $user_array );
			eval ("\$content = \"$content\";");
		}

	}
}

if ($showmode==1 || $showmode==2) {
	$query = "SELECT DISTINCT a.username"
	."\n FROM #__session AS a"
	."\n WHERE a.guest = 0"
	;
	$database->setQuery($query);
	$rows = $database->loadObjectList();
	foreach($rows as $row) {
		$content .= "<ul>\n";
		$content .= "<li><strong>" . $row->username . "</strong></li>\n";
		$content .= "</ul>\n";
	}

	if ( !$content ) {
		echo JText::_( 'No Users Online' ) ."\n";
	} else {
		echo $content;
	}
}
?>
