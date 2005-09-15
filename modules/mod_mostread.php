<?php
/**
* @version $Id: mod_mostread.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modMostreadData {

	function getLists( &$params ) {
		global $my, $mainframe, $database;
		global $mosConfig_live_site, $mosConfig_zero_date;

		$type 		= intval( $params->get( 'type', 1 ) );
		$count 		= intval( $params->get( 'count', 5 ) );
		$catid 		= trim( $params->get( 'catid' ) );
		$secid 		= trim( $params->get( 'secid' ) );
		$show_front	= $params->get( 'show_front', 1 );

		$now 		= $mainframe->getDateTime();
		$access 	= !$mainframe->getCfg( 'shownoauth' );

		// select between Content Items, Static Content or both
		switch ( $type ) {
			case 2:
				$query = "SELECT a.id, a.title"
				. "\n FROM #__content AS a"
				. "\n WHERE ( a.state = '1' AND a.checked_out = '0' AND a.sectionid = '0' )"
				. "\n AND ( a.publish_up = '$mosConfig_zero_date' OR a.publish_up <= '$now' )"
				. "\n AND ( a.publish_down = '$mosConfig_zero_date' OR a.publish_down >= '$now' )"
		    	. ( $access ? "\n AND a.access <= '$my->gid'" : '' )
				. "\n ORDER BY a.hits DESC"
				;
				$database->setQuery( $query, 0, $count );
				$rows = $database->loadObjectList();
				break;

			case 3:
				$query = "SELECT a.id, a.title, a.sectionid"
				. "\n FROM #__content AS a"
				. "\n WHERE ( a.state = '1' AND a.checked_out = '0' )"
				. "\n AND ( a.publish_up = '$mosConfig_zero_date' OR a.publish_up <= '$now' )"
				. "\n AND ( a.publish_down = '$mosConfig_zero_date' OR a.publish_down >= '$now' )"
		    	. ( $access ? "\n AND a.access <= '$my->gid'" : '' )
				. "\n ORDER BY a.hits DESC"
				;
				$database->setQuery( $query, 0, $count );
				$rows = $database->loadObjectList();
				break;

			case 1:
			default:
				$query = "SELECT a.id, a.title, a.sectionid, a.catid"
				. "\n FROM #__content AS a"
				. "\n LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id"
				. "\n WHERE ( a.state = '1' AND a.checked_out = '0' AND a.sectionid > '0' )"
				. "\n AND ( a.publish_up = '$mosConfig_zero_date' OR a.publish_up <= '$now' )"
				. "\n AND ( a.publish_down = '$mosConfig_zero_date' OR a.publish_down >= '$now' )"
		    	. ( $access ? "\n AND a.access <= '$my->gid'" : '' )
				. ( $catid ? "\n AND ( a.catid IN ( $catid ) )" : '' )
				. ( $secid ? "\n AND ( a.sectionid IN ( $secid ) )" : '' )
				. ( $show_front == "0" ? "\n AND f.content_id IS NULL" : '' )
				. "\n ORDER BY a.hits DESC"
				;
				$database->setQuery( $query, 0, $count );
				$rows = $database->loadObjectList();

				break;
		}

		// needed to reduce queries used by getItemid for Content Items
		if ( ( $type == 1 ) || ( $type == 3 ) ) {
			$bs 	= $mainframe->getBlogSectionCount();
			$bc 	= $mainframe->getBlogCategoryCount();
			$gbs 	= $mainframe->getGlobalBlogSectionCount();
		}

		$i = 0;
		foreach ( $rows as $row ) {
			// get Itemid
			switch ( $type ) {
				case 2:
					$query = "SELECT id"
					. "\n FROM #__menu"
					. "\n WHERE type = 'content_typed'"
					. "\n AND componentid = $row->id"
					;
					$database->setQuery( $query );
					$Itemid = $database->loadResult();
					break;

				case 3:
					if ( $row->sectionid ) {
						$Itemid = $mainframe->getItemid( $row->id, 0, 0, $bs, $bc, $gbs );
					} else {
						$query = "SELECT id"
						. "\n FROM #__menu"
						. "\n WHERE type = 'content_typed'"
						. "\n AND componentid = $row->id"
						;
						$database->setQuery( $query );
						$Itemid = $database->loadResult();
					}
					break;

				case 1:
				default:
					$Itemid = $mainframe->getItemid( $row->id, 0, 0, $bs, $bc, $gbs );
					break;
			}

			// Blank itemid checker for SEF
			if ($Itemid == NULL) {
				$Itemid = '';
			} else {
				$Itemid = '&amp;Itemid='.$Itemid;
			}

			// & xhtml compliance conversion
			$row->title = ampReplace( $row->title );

			$link = sefRelToAbs( 'index.php?option=com_content&amp;task=view&amp;id='. $row->id . $Itemid );

			$lists[$i]->link	= $link;
			$lists[$i]->text	= $row->title;
			$i++;
		}

		return $lists;
	}
}

class modMostread {

	function show ( &$params ) {
		global $my;

		$cache  = mosFactory::getCache( "mod_mostread" );

		$cache->setCaching($params->get('cache', 1));
		$cache->setLifeTime($params->get('cache_time', 900));
		$cache->setCacheValidation(true);

		$cache->callId( "modMostread::_display", array( $params ), "mod_mostread".$my->gid );
	}


	function _display( &$params ) {
		$tmpl =& moduleScreens::createTemplate( 'mod_mostread.html' );

		$lists = modMostreadData::getLists( $params );

		$tmpl->addVar( 'mod_mostread', 'class', $params->get( 'moduleclass_sfx' ) );
		$tmpl->addObject( 'mod_mostread','' );
		$tmpl->addObject( 'mod_mostread-items', $lists, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_mostread' );
	}
}

modMostread::show( $params );
?>
