<?php
/**
* @version $Id: mod_whosonline.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modWhosonlineData {

	function &getVars( &$params ){
		global $_LANG, $database;

		$showmode 			= $params->get( 'showmode' );
		$moduleclass_sfx 	= $params->get( 'moduleclass_sfx' );

		$guests 	= 0;
		$members 	= 0;
		if ( $showmode == 0 || $showmode == 2 ) {
			$query = "SELECT COUNT( session_id ) AS guestOnline"
			. "\n FROM #__session"
			. "\n WHERE guest = 1"
			. "\n AND ( usertype is NULL OR usertype = '' )"
			;
			$database->setQuery( $query );
			$guests = $database->loadResult();

			$query = "SELECT DISTINCT COUNT( username ) AS userOnline"
			. "\n FROM #__session"
			. "\n WHERE guest = 0"
    	    . "\n AND gid = 25"
			;
			$database->setQuery( $query );
			$members = $database->loadResult();
		}

		$rows = array();
		if ( $showmode == 1 || $showmode == 2 ) {
			$query = "SELECT DISTINCT a.username"
			. "\n FROM #__session AS a"
			. "\n WHERE ( a.guest = 0 )"
			;
			$database->setQuery($query);
			$rows = $database->loadObjectList();
		}

		$list->guests 			= $guests;
		$list->members 			= $members;
		$list->show_members 	= ( $showmode ? 1 : 0 );
		$list->show_count 		= ( ( $showmode == 0 || $showmode == 2 ) ? 1 : 0 );

		return array( $list, $rows );
	}
}

class modWhosonline {

	function show ( &$params ) {
		modWhosonline::_display($params);
	}

	function _display( &$params ) {

		$vars = modWhosonlineData::getVars( $params );
		$list = $vars[0];
		$rows = $vars[1];

		$tmpl =& moduleScreens::createTemplate( 'mod_whosonline.html' );

		$tmpl->addVar( 'mod_whosonline', 'class', 		$params->get( 'moduleclass_sfx' ) );

		$tmpl->addObject( 'mod_whosonline', $list );
		$tmpl->addObject( 'user-items', $rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_whosonline' );
	}
}

modWhosonline::show( $params );
?>