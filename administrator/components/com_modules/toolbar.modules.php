<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Modules
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

require_once( $mainframe->getPath( 'toolbar_html' ) );

$client = mosGetParam( $_REQUEST, 'client', 'site' );

switch ($task) {

	case 'editA':
	case 'edit':
		$cid = mosGetParam( $_POST, 'cid', 0 );
		if ( !is_array( $cid ) ){
			$mid = mosGetParam( $_POST, 'id', 0 );;
		} else {
			$mid = $cid[0];
		}

		$published = 0;
		if ( $mid ) {
			$query = "SELECT published"
			. "\n FROM #__modules"
			. "\n WHERE id = $mid"
			;
			$database->setQuery( $query );
			$published = $database->loadResult();
		}
		$cur_template = $mainframe->getTemplate();
		TOOLBAR_modules::_EDIT( $cur_template, $published, $client );
		break;

	case 'new':
		TOOLBAR_modules::_NEW($client);
		break;

	default:
		TOOLBAR_modules::_DEFAULT($client);
		break;
}
?>