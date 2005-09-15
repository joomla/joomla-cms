<?php
/**
* @version $Id: pathway.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

function pathwayMakeLink( $id, $name, $link, $parent ) {
	$mitem = new stdClass();
	$mitem->id 		= $id;
	$mitem->name 	= $mitem->name = html_entity_decode( $name );
	$mitem->link 	= $link;
	$mitem->parent 	= $parent;
	$mitem->type 	= '';

	return $mitem;
}

/**
* Outputs the pathway breadcrumbs
* @param database A database connector object
* @param int The db id field value of the current menu item
*/
function showPathway( $Itemid ) {
	global $database, $option, $task, $mainframe;
	global $QUERY_STRING, $REQUEST_URI;

	// get the home page
	$query = "SELECT *"
	. "\n FROM #__menu"
	. "\n WHERE menutype = 'mainmenu'"
	. "\n AND published = '1'"
	. "\n ORDER BY parent, ordering"
	;
	$database->setQuery( $query, 0, 1 );
	$home_menu = new mosMenu( $database );
	$database->loadObject( $home_menu );

	// the the whole menu array and index the array by the id
	$query = "SELECT id, name, link, parent, type"
	. "\n FROM #__menu"
	. "\n WHERE published = '1'"
	. "\n ORDER BY parent, ordering"
	;
	$database->setQuery( $query );
	$mitems = $database->loadObjectList( 'id' );

	$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
	$optionstring = $isWin ? $QUERY_STRING : $REQUEST_URI;

	// are we at the home page or not
	$home 		= @$mitems[$home_menu->id]->name;
	$path 		= '';

	// this is a patch job for the frontpage items! aje
	if ( ( $Itemid == $home_menu->id ) && ( $option == 'content' ) ) {
		$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		switch( $task ) {
			case 'blogsection':
				$query = "SELECT title"
				. "\n FROM #__sections"
				. "\n WHERE id = $id"
				;
				break;

			case 'blogcategory':
				$query = "SELECT title"
				. "\n FROM #__categories"
				. "\n WHERE id = $id"
				;
				break;

			default:
				$query = "SELECT title, catid"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				break;
		}
		$row = null;
		$database->setQuery( $query );
		$database->loadObject( $row );

		$id = max( array_keys( $mitems ) ) + 1;

		// add the content item
		$mitem2 		= pathwayMakeLink( $Itemid, $row->title, '', 1 );
		$mitems[$id] 	= $mitem2;
		$Itemid 		= $id;

		$home = '<a href="'. sefRelToAbs( 'index.php' ) .'" class="pathway">'. $home .'</a>';
	}

	$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

	switch( @$mitems[$Itemid]->type ) {
		case 'content_section':
			if ( ( $task == 'category' ) && $id ) {
				$query = "SELECT title"
				. "\n FROM #__categories"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$title = $database->loadResult();

				$id 			= max( array_keys( $mitems ) ) + 1;
				$link 			= 'index.php?option='. $option .'&task='. $task .'&id='. $id .'&Itemid='. $Itemid;
				$mitem 			= pathwayMakeLink( $id, $title, $link, $Itemid );
				$mitems[$id] 	= $mitem;
				$Itemid 		= $id;
			} else if ( ( $task  == 'view' ) && $id ) {
				// load the content item name and category
				$row = null;
				$query = "SELECT title, catid, id"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$database->loadObject( $row );

				// load and add the category
				$query = "SELECT c.title AS title, s.id AS sectionid "
				."FROM #__categories AS c "
				."LEFT JOIN #__sections AS s "
				."ON c.section = s.id "
				."WHERE c.id = $row->catid"
				;
				$database->setQuery( $query );
				$result = $database->loadObjectList();

				$title 		= $result[0]->title;
				$sectionid 	= $result[0]->sectionid;

				$id 			= max( array_keys( $mitems ) ) + 1;
				$link			= 'index.php?option='. $option .'&task=category&sectionid='. $sectionid .'&id='. $row->catid;
				$mitem1 		= pathwayMakeLink( $Itemid, $title, $link, $Itemid );
				$mitems[$id] 	= $mitem1;

				// add the final content item
				$id++;
				$mitem2 		= pathwayMakeLink( $Itemid, $row->title, '', $id-1 );
				$mitems[$id] 	= $mitem2;
				$Itemid 		= $id;
			}
			break;

		case 'content_category':
			if ( ( $task  == 'view' ) && $id ) {
				// load the content item name and category
				$row = null;
				$query = "SELECT title, catid"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$database->loadObject( $row );

				$id 			= max( array_keys( $mitems ) ) + 1;
				// add the final content item
				$mitem2 		= pathwayMakeLink( $Itemid,	$row->title, '', $Itemid );
				$mitems[$id] 	= $mitem2;
				$Itemid 		= $id;
			}
			break;

		case 'content_blog_category':
		case 'content_blog_section':
			if ( ( $task  == 'view' ) && $id ) {
				// load the content item name and category
				$row = null;
				$query = "SELECT title, catid"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$database->loadObject( $row );

				$id 			= max( array_keys( $mitems ) ) + 1;
				$mitem2 		= pathwayMakeLink( $Itemid,	$row->title, '', $Itemid );
				$mitems[$id] 	= $mitem2;
				$Itemid 		= $id;
			}
			break;
	}

	$i 		= count( $mitems );
	$mid 	= $Itemid;
	// get image used for pathway
	$img 	= pathwayImage();

	while ( $i-- ) {
		if ( !$mid || empty( $mitems[$mid] ) || $mid == $home_menu->id || !eregi( 'option', $optionstring ) ) {
			break;
		}
		$item =& $mitems[$mid];

		// converts & to &amp; for xtml compliance
		$itemname = ampReplace( $item->name );

		// if it is the current page, then display a non hyperlink
		if ( $item->id == $Itemid || empty( $mid ) || empty( $item->link ) ) {
			$newlink = '  '. $itemname;
		} else if ( isset( $item->type ) && $item->type == 'url' ) {
			$correctLink = eregi( 'http://', $item->link);
			if ( $correctLink == 1 ) {
				$newlink = '<a href="'. $item->link .'" target="_window" class="pathway">'. $itemname .'</a>';
			} else {
				$newlink = $itemname;
			}
		} else {
			$newlink = '<a href="'. sefRelToAbs( $item->link .'&Itemid='. $item->id ) .'" class="pathway">'. $itemname .'</a>';
		}

		$newlink = ampReplace( $newlink );

		if ( trim( $newlink ) != '' ) {
			$path = $img .' '. $newlink .' '. $path;
		} else {
			$path = '';
		}

		$mid = $item->parent;
	}

	if ( eregi( 'option', $optionstring ) && trim( $path  ) ) {
		$home = '<a href="'. sefRelToAbs( 'index.php' ) .'" class="pathway">'. $home .'</a>';
	}

	if ( $mainframe->getCustomPathWay() ){
		$path .= $img .' ';
		$path .= implode ( $img .' ', $mainframe->getCustomPathWay() );
	}

	echo '<span class="pathway">'. $home .' '. $path .'</span>';
}

/*
* checks template image directory for arrow file, to use for pathway
* if none found than default arrow image used
* if that is not found than a `>` is used instead
*/
function pathwayImage() {
	global $mainframe, $mosConfig_absolute_path, $mosConfig_live_site;

	$imgPath =  'templates/' . $mainframe->getTemplate() . '/images/arrow.png';
	if ( file_exists( $mosConfig_absolute_path .'/'. $imgPath ) ) {
		$img = '<img src="' . $mosConfig_live_site . '/' . $imgPath . '" border="0" alt="arrow" />';
	} else {
		$imgPath = '/images/M_images/arrow.png';
		if ( file_exists( $mosConfig_absolute_path . $imgPath ) ) {
			$img = '<img src="' . $mosConfig_live_site . '/images/M_images/arrow.png" alt="arrow" />';
		} else {
		    $img = '&gt;';
		}
	}

	return $img;
}

// code placed in a function to prevent messing up global variables
showPathway( $Itemid );
?>