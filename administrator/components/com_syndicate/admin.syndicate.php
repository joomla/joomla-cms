<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Syndicate
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

// ensure user has access to this function
if (!$acl->acl_check( 'com_syndicate', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}

require_once( $mainframe->getPath( 'admin_html' ) );


switch ($task) {

	case 'save':
		saveSyndicate( $option );
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
	. "\n WHERE a.name = 'Syndicate'"
	;
	$database->setQuery( $query );
	$id = $database->loadResult();

	// load the row from the db table
	$row = new mosComponent( $database );
	$row->load( $id );

	// get params definitions
	$params = new mosParameters( $row->params, $mainframe->getPath( 'com_xml', $row->option ), 'component' );

	HTML_syndicate::settings( $option, $params, $id );
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveSyndicate( $option ) {
	global $database;
	global $_LANG;

	$params = mosGetParam( $_POST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ($params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$_POST['params'] = mosParameters::textareaHandling( $txt );
	}

	$id = mosGetParam( $_POST, 'id', '17' );
	$row = new mosComponent( $database );
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

	$msg = $_LANG->_( 'Settings successfully Saved' );
	mosRedirect( 'index2.php?option='. $option, $msg );
}

/**
* Cancels editing and checks in the record
*/
function cancelSyndicate(){
	mosRedirect( 'index2.php' );
}
?>