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

require_once( $mainframe->getPath( 'front_html', 'com_content') );

global $my, $mosConfig_shownoauth, $mosConfig_offset, $acl;

// Disable edit ability icon
$access = new stdClass();
$access->canEdit 	= 0;
$access->canEditOwn = 0;
$access->canPublish = 0;

$now = date( 'Y-m-d H:i:s', time()+$mosConfig_offset*60*60 );

$catid 				= intval( $params->get( 'catid' ) );
$style 				= $params->get( 'style' );
$image 				= $params->get( 'image' );
$readmore 			= $params->get( 'readmore' );
$items 				= intval( $params->get( 'items' ) );
$moduleclass_sfx    = $params->get( 'moduleclass_sfx' );

$params->set( 'intro_only', 1 );
$params->set( 'hide_author', 1 );
$params->set( 'hide_createdate', 0 );
$params->set( 'hide_modifydate', 1 );

if ( $items ) {
	$limit = "LIMIT $items";
} else {
	$limit = '';
}

$noauth = !$mainframe->getCfg( 'shownoauth' );
$nullDate = $database->getNullDate();

// query to determine article count
$query = "SELECT a.id"
."\n FROM #__content AS a"
."\n INNER JOIN #__categories AS b ON b.id = a.catid"
."\n WHERE a.state = 1"
. ( $noauth ? "\n AND a.access <= $my->gid AND b.access <= $my->gid" : '' )
."\n AND (a.publish_up = '$nullDate' OR a.publish_up <= '$now' ) "
."\n AND (a.publish_down = '$nullDate' OR a.publish_down >= '$now' )"
."\n AND catid = $catid"
."\n ORDER BY a.ordering"
."\n $limit"
;
$database->setQuery( $query );
$rows = $database->loadResultArray();
$numrows = count( $rows );

$row = new mosContent( $database );

switch ($style) {
	case 'horiz':
		echo '<table class="moduletable' . $moduleclass_sfx .'">';
		echo '<tr>';
		foreach ($rows as $id) {
			$row->load( $id );
			$row->text = $row->introtext;
			$row->groups = '';
			$row->readmore = (trim( $row->fulltext ) != '');
			echo '<td>';
			HTML_content::show( $row, $params, $access, 0, 'com_content' );
			echo '</td>';
		}
		echo '</tr></table>';
		break;

	case 'vert':
		foreach ($rows as $id) {
			$row->load( $id );
			$row->text = $row->introtext;
			$row->groups = '';
			$row->readmore = (trim( $row->fulltext ) != '');

			HTML_content::show( $row, $params, $access, 0, 'com_content' );
		}
		break;

	case 'flash':
		default:
		if ($numrows > 0) {
			srand ((double) microtime() * 1000000);
			$flashnum = $rows[rand( 0, $numrows-1 )];
		} else {
			$flashnum = 0;
		}
		$row->load( $flashnum );
		$row->text = $row->introtext;
		$row->groups = '';
		$row->readmore = (trim( $row->fulltext ) != '');

		HTML_content::show( $row, $params, $access, 0, 'com_content' );
		break;
}
?>