<?php
/**
* @version $Id: mod_latest.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$query = "SELECT a.id, a.sectionid, a.title, a.created, u.name, a.created_by_alias, a.created_by"
. "\n FROM #__content AS a"
. "\n LEFT JOIN #__users AS u ON u.id=a.created_by"
. "\n WHERE a.state <> -2"
. "\n ORDER BY created DESC"
;
$database->setQuery( $query, 0, 10 );
$rows = $database->loadObjectList();

$i=0;
foreach ($rows as $row) {
	if ( $row->sectionid == 0 ) {
		$link = 'index2.php?option=com_typedcontent&amp;task=edit&amp;id='. $row->id;
	} else {
		$link = 'index2.php?option=com_content&amp;task=edit&amp;id='. $row->id;
	}

	$linkA = '#';
	if ( $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype ) ) {
		if ( $row->created_by_alias ) {
			$author = $row->created_by_alias;
		} else {
			$linkA 	= 'index2.php?option=com_users&amp;task=editA&amp;id='. $row->created_by;
			$author = htmlspecialchars( $row->name, ENT_QUOTES );
		}
	} else {
		if ( $row->created_by_alias ) {
			$author = $row->created_by_alias;
		} else {
			$author = htmlspecialchars( $row->name, ENT_QUOTES );
		}
	}

	$rows[$i]->num 		= $i + 1;
	$rows[$i]->link 	= $link;
	$rows[$i]->linkA 	= $linkA;
	$rows[$i]->title 	= htmlspecialchars($row->title, ENT_QUOTES);
	$rows[$i]->date 	= mosFormatDate( $row->created, $_LANG->_( 'DATE_FORMAT_LC3' ) );
	$rows[$i]->author 	= $author;

	$i++;
}
mod_latestScreens::view( $rows );


class mod_latestScreens {
	function view( &$rows ) {
		$tmpl =& moduleScreens_admin::createTemplate( 'mod_latest.html' );

		$tmpl->addObject( 'latest', $rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_latest' );
	}
}
?>