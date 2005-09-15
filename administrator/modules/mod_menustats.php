<?php
/**
* @version $Id: mod_menustats.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$query = "SELECT menutype, COUNT( id ) AS numitems"
. "\n FROM #__menu"
. "\n WHERE published = 1"
. "\n GROUP BY menutype"
;
$database->setQuery( $query );
$rows = $database->loadObjectList();

$i = 0;
foreach ($rows as $row) {
	$link = 'index2.php?option=com_menus&amp;menutype='. $row->menutype;

	$rows[$i]->num 	= $i + 1;
	$rows[$i]->link = $link;

	$i++;
}
mod_statsScreens::view( $rows );


class mod_statsScreens {
	function view( &$rows ) {
		$tmpl =& moduleScreens_admin::createTemplate( 'mod_stats.html' );

		$tmpl->addObject( 'menus', $rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_stats' );
	}
}
?>