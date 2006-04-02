<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Syndicate
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_syndicate', 'manage' )) {
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );


switch ($task) {
	case 'save':
		saveSyndicate();
		break;

  case 'cancel':
		cancelSyndicate( );
		break;

	default:
		showSyndicate( $option );
		break;
}

/**
* List the records
* @param string The current GET/POST option
*/
function showSyndicate( $option ) {
	global $database, $mainframe, $mosConfig_list_limit;

	$query = "SELECT a.id"
	. "\n FROM #__components AS a"
	. "\n WHERE a.admin_menu_link LIKE( '%option=com_syndicate%' )"
	. "\n AND a.option = 'com_syndicate'"
	;
	$database->setQuery( $query );
	$id = $database->loadResult();

	// load the row from the db table
	$row =& JTable::getInstance('component', $database );
	$row->load( $id );

	// get params definitions
	$params = new JParameter( $row->params, JApplicationHelper::getPath( 'com_xml', $row->option ), 'component' );

	HTML_syndicate::settings( $option, $params, $id );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveSyndicate() {
	global $database;

	$id = JRequest::getVar( 'id', 17, 'post', 'int' );
	
	$row =& JTable::getInstance('component', $database );
	$row->load( $id );

	if (!$row->bind( $_POST )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = JText::_( 'Settings successfully Saved' );
	josRedirect( 'index2.php?option=com_syndicate&hidemainmenu=1', $msg );
}

/**
* Cancels editing and checks in the record
*/
function cancelSyndicate(){
	josRedirect( 'index2.php' );
}
?>