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

function pathwayMakeLink( $id, $name, $link, $parent ) {
	$mitem = new stdClass();
	$mitem->id 		= $id;
	$mitem->name 	= html_entity_decode( $name );
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

	// get the home page
	$query = "SELECT id, name, link, parent, type"
	. "\n FROM #__menu"
	. "\n WHERE menutype = 'mainmenu'"
	. "\n AND published = 1"
	. "\n ORDER BY parent, ordering"
	. "\n LIMIT 1"
	;
	$database->setQuery( $query );
	$home_menu = new mosMenu( $database );
	$database->loadObject( $home_menu );

	// the the whole menu array and index the array by the id
	$query = "SELECT id, name, link, parent, type"
	. "\n FROM #__menu"
	. "\n WHERE published = 1"
	. "\n ORDER BY parent, ordering"
	;
	$database->setQuery( $query );
	$mitems = $database->loadObjectList( 'id' );

	$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
	$optionstring = $isWin ? $_SERVER['QUERY_STRING'] : $_SERVER['REQUEST_URI'];

	// are we at the home page or not
	$homekeys 	= array_keys( $mitems );
	$home 		= @$mitems[$home_menu->id]->name;
	$path 		= '';

	// this is a patch job for the frontpage items! aje
	if ($Itemid == $home_menu->id) {
		switch ($option) {
			case 'content':
			$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );
			if ($task=='blogsection'){

				$query = "SELECT title, id"
				. "\n FROM #__sections"
				. "\n WHERE id = $id"
				;
			} else if ( $task=='blogcategory' ) {
				$query = "SELECT title, id"
				. "\n FROM #__categories"
				. "\n WHERE id = $id"
				;
			} else {
				$query = "SELECT title, catid, id"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
			}
			$database->setQuery( $query );

			$row = null;
			$database->loadObject( $row );

			$id = max( array_keys( $mitems ) ) + 1;

			// add the content item
			$mitem2 = pathwayMakeLink(
				$Itemid,
				$row->title,
				'',
				1
			);
			$mitems[$id] = $mitem2;
			$Itemid = $id;

			$home = '<a href="'. sefRelToAbs( 'index.php' ) .'" class="pathway">'. $home .'</a>';
			break;

		}
	}

	switch( @$mitems[$Itemid]->type ) {
		case 'content_section':
		$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		switch ($task) {
			case 'category':
			if ($id) {
				$query = "SELECT title, id"
				. "\n FROM #__categories"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$title = $database->loadResult();

				$id = max( array_keys( $mitems ) ) + 1;
				$mitem = pathwayMakeLink(
					$id,
					$title,
					'index.php?option='. $option .'&task='. $task .'&id='. $id .'&Itemid='. $Itemid,
					$Itemid
				);

				$mitems[$id] = $mitem;
				$Itemid = $id;
			}
			break;

			case 'view':
			if ($id) {
				// load the content item name and category
				$query = "SELECT title, catid, id"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$row = null;
				$database->loadObject( $row );

				// load and add the category
				$query = "SELECT c.title AS title, s.id AS sectionid, c.id AS id"
				. "\n FROM #__categories AS c"
				. "\n LEFT JOIN #__sections AS s"
				. "\n ON c.section = s.id"
				. "\n WHERE c.id = $row->catid"
				;
				$database->setQuery( $query );
				$result = $database->loadObjectList();

				$title = $result[0]->title;
				$sectionid = $result[0]->sectionid;

				$id = max( array_keys( $mitems ) ) + 1;
				$mitem1 = pathwayMakeLink(
					$Itemid,
					$title,
					'index.php?option='. $option .'&task=category&sectionid='. $sectionid .'&id='. $row->catid,
					$Itemid
				);

				$mitems[$id] = $mitem1;

				// add the final content item
				$id++;
				$mitem2 = pathwayMakeLink(
					$Itemid,
					$row->title,
					'',
					$id-1
				);

				$mitems[$id] = $mitem2;
				$Itemid = $id;

			}
			break;
		}
		break;

		case 'content_category':
		$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		switch ($task) {

			case 'view':
			if ($id) {
				// load the content item name and category
				$query = "SELECT title, catid, id"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$row = null;
				$database->loadObject( $row );

				$id = max( array_keys( $mitems ) ) + 1;
				// add the final content item
				$mitem2 = pathwayMakeLink(
					$Itemid,
					$row->title,
					'',
					$Itemid
				);

				$mitems[$id] = $mitem2;
				$Itemid = $id;

			}
			break;
		}
		break;

		case 'content_blog_category':
		case 'content_blog_section':
		switch ($task) {
			case 'view':
			$id = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

			if ($id) {
				// load the content item name and category

				$query = "SELECT title, catid, id"
				. "\n FROM #__content"
				. "\n WHERE id = $id"
				;
				$database->setQuery( $query );
				$row = null;
				$database->loadObject( $row );

				$id = max( array_keys( $mitems ) ) + 1;
				$mitem2 = pathwayMakeLink(
					$Itemid,
					$row->title,
					'',
					$Itemid
				);
				$mitems[$id] = $mitem2;
				$Itemid = $id;

			}
			break;
		}
		break;
	}

	$i = count( $mitems );
	$mid = $Itemid;

	$imgPath =  'templates/' . $mainframe->getTemplate() . '/images/arrow.png';
	if (file_exists( JPATH_SITE . "/$imgPath" )){
		$img = '<img src="' . JURL_SITE . '/' . $imgPath . '" border="0" alt="arrow" />';
	} else {
		$imgPath = '/images/M_images/arrow.png';
		if (file_exists( JPATH_SITE . $imgPath )){
			$img = '<img src="' . JURL_SITE . '/images/M_images/arrow.png" alt="arrow" />';
		} else {
			$img = '&gt;';
		}
	}

	while ($i--) {
		if (!$mid || empty( $mitems[$mid] ) || $mid == 1 || !eregi("option", $optionstring)) {
			break;
		}
		$item =& $mitems[$mid];

		// converts & to &amp; for xtml compliance
		$itemname = ampReplace( $item->name );

		// if it is the current page, then display a non hyperlink
		if ($item->id == $Itemid || empty( $mid ) || empty($item->link)) {
			$newlink = "  $itemname";
		} else if (isset($item->type) && $item->type == 'url') {
			$correctLink = eregi( 'http://', $item->link);
			if ($correctLink==1) {
				$newlink = '<a href="'. $item->link .'" target="_window" class="pathway">'. $itemname .'</a>';
			} else {
				$newlink = $itemname;
			}
		} else {
			$newlink = '<a href="'. sefRelToAbs( $item->link .'&Itemid='. $item->id ) .'" class="pathway">'. $itemname .'</a>';
		}

		$newlink = ampReplace( $newlink );

		if (trim($newlink)!="") {
			$path = $img .' '. $newlink .' '. $path;
		} else {
			$path = '';
		}

		$mid = $item->parent;
	}

	if ( eregi( 'option', $optionstring ) && trim( $path  ) ) {
		$home = '<a href="'. sefRelToAbs( 'index.php' ) .'" class="pathway">'. $home .'</a>';
	}

	if ($mainframe->getCustomPathWay()){
		$path .= $img . ' ';
		$path .= implode ( "$img " ,$mainframe->getCustomPathWay());
	}

	echo '<span class="pathway">'. $home .' '. $path .'</span>';
}

// code placed in a function to prevent messing up global variables
showPathway( $Itemid );
?>