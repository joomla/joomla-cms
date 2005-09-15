<?php
/** module to display newsfeeds
* version $Id: newsfeeds.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* modified by brian & rob
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Newsfeeds
 * @subpackage Newsfeeds
 */
class newsfeedsTasks_Front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function newsfeedsTasks_Front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'displaylist' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	function displaylist() {
		global $mainframe, $database, $my;
		global $mosConfig_live_site;
		global $Itemid;

		$catid = intval( mosGetParam( $_REQUEST ,'catid', 0 ) );

		$rows 		= array();
		$currentcat = NULL;
		if ( $catid ) {
			$type = 'category';
		} else {
			$type = 'section';
		}

		/* Query to retrieve all categories that belong under the contacts section and that are published. */
		$query = "SELECT cc.*, a.catid, COUNT(a.id) AS numlinks"
		. "\n FROM #__categories AS cc"
		. "\n LEFT JOIN #__newsfeeds AS a ON a.catid = cc.id"
		. "\n WHERE a.published = '1'"
		. "\n AND cc.section = 'com_newsfeeds'"
		. "\n AND cc.published = '1'"
		. "\n AND cc.access <= '$my->gid'"
		. "\n GROUP BY cc.id"
		. "\n ORDER BY cc.ordering"
		;
		$database->setQuery( $query );
		$categories = $database->loadObjectList();

		$count = count( $categories );
		for ( $i = 0; $i < $count; $i++ ) {
			$link 					= 'index.php?option=com_newsfeeds&amp;catid='. $categories[$i]->catid .'&amp;Itemid='. $Itemid;
			$categories[$i]->link	= sefRelToAbs( $link );
		}

		if ( $catid ) {
			// url links info for category
			$query = "SELECT *"
			. "\n FROM #__newsfeeds"
			. "\n WHERE catid = '$catid'"
			 . "\n AND published='1'"
			. "\n ORDER BY ordering"
			;
			$database->setQuery( $query );
			$rows = $database->loadObjectList();

			// used to show table rows in alternating colours
			$tabclass = array( 'sectiontableentry1', 'sectiontableentry2' );
			$count = count( $rows );
			$n = 0;
			for ( $i = 0; $i < $count; $i++ ) {
				 $link 				= 'index.php?option=com_newsfeeds&amp;task=view&amp;feedid='. $rows[$i]->id .'&amp;Itemid='. $Itemid;
				 $rows[$i]->url		= sefRelToAbs( $link );
				 $rows[$i]->class	= $tabclass[$n];
				 $n = 1 - $n;
			}

			// current category info
			$query = "SELECT name, description, image, image_position"
			. "\n FROM #__categories"
			. "\n WHERE id = '$catid'"
			. "\n AND published = '1'"
			. "\n AND access <= '$my->gid'"
			;
			$database->setQuery( $query );
			$database->loadObject( $currentcat );
		}

		// Parameters
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );

		$params->def( 'page_title', 		1 );
		$params->def( 'header', 			$menu->name );
		$params->def( 'pageclass_sfx', 		'' );
		$params->def( 'headings', 			1 );
		$params->def( 'back_button',		$mainframe->getCfg( 'back_button' ) );
		$params->def( 'description_text', 	'' );
		$params->def( 'image', 				-1 );
		$params->def( 'image_align', 		'right' );
		$params->def( 'other_cat_section', 	1 );
		// Category List Display control
		$params->def( 'other_cat', 			1 );
		$params->def( 'cat_description', 	1 );
		$params->def( 'cat_items', 			1 );
		// Table Display control
		$params->def( 'headings', 			1 );
		$params->def( 'name', 				1 );
		$params->def( 'articles', 			1 );
		$params->def( 'link', 				1 );
		$params->def( 'meta_key', 			'' );
		$params->def( 'meta_descrip', 		'' );
		$params->set( 'catid', 				$catid );

		$params->set( 'cat', 0 );
		if ( ( $type == 'category' ) && $params->get( 'other_cat' ) ) {
			$params->set( 'cat', 1 );
		} else if ( ( $type == 'section' ) && $params->get( 'other_cat_section' ) ) {
			$params->set( 'cat', 1 );
		}

		// page description
		$currentcat->descrip = '';
		if( ( @$currentcat->description ) <> '' ) {
			$currentcat->descrip = $currentcat->description;
		} else if ( !$catid ) {
			// show description
			if ( $params->get( 'description' ) ) {
				$currentcat->descrip = $params->get( 'description_text' );
			}
		}
		// page image
		$currentcat->img = '';
		$path = $mosConfig_live_site .'/images/stories/';
		if ( ( @$currentcat->image ) <> '' ) {
			$currentcat->img = $path . $currentcat->image;
			$currentcat->align = $currentcat->image_position;
		} else if ( !$catid ) {
			if ( $params->get( 'image' ) <> -1 ) {
				$currentcat->img 	= $path . $params->get( 'image' );
				$currentcat->align 	= $params->get( 'image_align' );
			}
		}
		// page header
		$currentcat->header = '';
		if ( @$currentcat->name <> '' ) {
			$currentcat->header = $currentcat->name;
		} else {
			$currentcat->header = $params->get( 'header' );
		}

		mosFS::load( '@front_html' );

		if ( $catid ) {
			$params->def( 'seo_title', $menu->name .' :: '. $currentcat->header );

			newsfeedsScreens_front::table_category( $params, $currentcat, $categories, $rows );
		} else {
			$params->def( 'seo_title', $menu->name );

			newsfeedsScreens_front::list_section( $params, $currentcat, $categories );
		}

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );
	}

	function view() {
		global $database, $mainframe, $mosConfig_absolute_path, $Itemid;

		$feedid = intval( mosGetParam( $_REQUEST ,'feedid', 0 ) );

		// full RSS parser used to access image information
		mosFS::load( 'includes/domit/xml_domit_rss.php' );
		$cacheDir = $mosConfig_absolute_path . '/cache/';
		$LitePath = $mosConfig_absolute_path . '/includes/Cache/Lite.php';

		// Adds parameter handling
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );
		$params = new mosParameters( $menu->params );
		$params->def( 'page_title', 	1 );
		$params->def( 'header', 		$menu->name );
		$params->def( 'pageclass_sfx', 	'' );
		$params->def( 'back_button', 	$mainframe->getCfg( 'back_button' ) );
		// Feed Display control
		$params->def( 'feed_image', 	1 );
		$params->def( 'feed_descr', 	1 );
		$params->def( 'item_descr', 	1 );
		$params->def( 'word_count', 	0 );
		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );

		if ( !$params->get( 'page_title' ) ) {
			$params->set( 'header', '' );
		}

		$and = '';
		if ( $feedid ) {
			$and = "\n AND id = '$feedid'";
		}

		$query = "SELECT name, link, numarticles, cache_time"
		. "\n FROM #__newsfeeds"
		. "\n WHERE published = '1'"
		. "\n AND checked_out = '0'"
		. $and
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		$newsfeeds = $database->loadObjectList();

		foreach ( $newsfeeds as $newsfeed ) {
			// full RSS parser used to access image information
			$rssDoc = new xml_domit_rss_document();
			$rssDoc->useCacheLite( true, $LitePath, $cacheDir, $newsfeed->cache_time );
			$rssDoc->loadRSS( $newsfeed->link );
			$totalChannels = $rssDoc->getChannelCount();

			for ( $i = 0; $i < $totalChannels; $i++ ) {
				$currChannel	=& $rssDoc->getChannel($i);
				$elements 		= $currChannel->getElementList();
				$descrip 		= 0;
				$iUrl			= 0;

				foreach ( $elements as $element ) {
					//image handling
					if ( $element == 'image' ) {
						$image =& $currChannel->getElement( DOMIT_RSS_ELEMENT_IMAGE );
						$iUrl	= $image->getUrl();
						$iTitle	= $image->getTitle();
					}
					if ( $element == 'description' ) {
						$descrip = 1;
						// hide com_rss descrip in 4.5.0 feeds
						if ( $currChannel->getDescription() == 'com_rss' ) {
							$descrip = 0;
						}
					}
				}
				// feed title
				$feed_title = $currChannel->getTitle();
				$feed_title = html_entity_decode( $feed_title );

				$rows[0]->link 	= $currChannel->getLink();
				$rows[0]->title = $feed_title;

				// feed description
				if ( $descrip && $params->get( 'feed_descr' ) ) {
					$description = $currChannel->getDescription();
					$description = html_entity_decode( $description );

					$rows[0]->description = $description;
				}
				// feed image
				if ( $iUrl && $params->get( 'feed_image' ) ) {
					$rows[0]->image		 	= $iUrl;
					$rows[0]->image_title 	= $iTitle;
				}

				$actualItems 	= $currChannel->getItemCount();
				$setItems 		= $newsfeed->numarticles;
				if ( $setItems > $actualItems ) {
					$totalItems = $actualItems;
				} else {
					$totalItems = $setItems;
				}

				for ( $j = 0; $j < $totalItems; $j++ ) {
					$currItem =& $currChannel->getItem($j);
					// item title
					$title 	= $currItem->getTitle();
					$title 	= html_entity_decode( $title );

					$text = '';
					// item description
					if ( $params->get( 'item_descr' ) ) {
						$text 	= html_entity_decode( $currItem->getDescription() );
						$num 	= $params->get( 'word_count' );

						// word limit check
						if ( $num ) {
							$texts = explode( ' ', $text );
							$count = count( $texts );
							if ( $count > $num ) {
								$text = '';
								for( $i=0; $i < $num; $i++ ) {
									$text .= ' '. $texts[$i];
								}
								$text .= '...';
							}
						}

						// decode text
						$text = html_entity_decode( $text );
					}

					$items[$j]->link 	= $currItem->getLink();
					$items[$j]->title 	= $title;
					$items[$j]->text 	= $text;
				}
			}
		}

		$params->def( 'seo_title', 		$menu->name .' :: '. $feed_title );
		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

		mosFS::load( '@front_html' );

		newsfeedsScreens_front::item( $items, $rows, $params );
	}
}

$tasker = new newsfeedsTasks_Front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>