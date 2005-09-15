<?php
/**
* @version $Id: rss.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Syndicate
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// load feed creator utilities
mosFS::load( 'includes/mambo.feed.php' );

$info	=	null;
$rss	=	null;

switch ( $task ) {
	case 'live_bookmark':
		feedFrontpage( false );
		break;

	default:
		feedFrontpage( true );
		break;
}

/**
 * Creates feed from Content Iems associated to teh frontpage component
 * @param boolean Display the feed
 */
function feedFrontpage( $showFeed ) {
	global $database, $mainframe;
	global $mosConfig_live_site, $mosConfig_absolute_path, $mosConfig_zero_date;
	global $_LANG;

	$sectionid = mosGetParam( $_GET, 'sectionid', 0 );

	// pull id of syndication component
	$query = "SELECT a.id"
	. "\n FROM #__components AS a"
	. "\n WHERE a.name = 'Syndicate'"
	;
	$database->setQuery( $query );
	$id = $database->loadResult();

	// load syndication parameters
	$component = new mosComponent( $database );
	$component->load( $id );
	$params = new mosParameters( $component->params );

	$now 	= $mainframe->getDateTime();

	// parameter intilization
	$info['date'] 		= date( 'r' );
	$info['year'] 		= date( 'Y' );
	$info['encoding'] 	= $_LANG->iso();
	$info['link'] 		= htmlspecialchars( $mosConfig_live_site );
	$info['cache'] 		= $params->def( 'cache', 1 );
	$info['cache_time'] = $params->def( 'cache_time', 3600 );
	$info['count']		= $params->def( 'count', 5 );
	$info['orderby'] 	= $params->def( 'orderby', '' );
	$info['title'] 		= $params->def( 'title', 'Powered by Joomla! 4.5.2' );
	$info['description']= $params->def( 'description', 'Joomla! site syndication' );
	$info['image_file']	= $params->def( 'image_file', 'mambo_rss.png' );

	if ( $info['image_file'] == -1 ) {
		$info['image']	= NULL;
	} else{
		$info['image']	= $mosConfig_live_site .'/images/M_images/'. $info['image_file'];
	}
	$info['image_alt'] 	= $params->def( 'image_alt', 'Powered by Joomla! 4.5.2' );
	$info['limit_text'] 	= $params->def( 'limit_text', 1 );
	$info['text_length'] 	= $params->def( 'text_length', 20 );
	// get feed type from url
	$info['feed'] 		= mosGetParam( $_GET, 'feed', 'RSS2.0' );
	// live bookmarks
	$info['live_bookmark']	= $params->def( 'live_bookmark', '' );
	$info['bookmark_file']	= $params->def( 'bookmark_file', '' );
	// content to syndicate

	// set filename for live bookmarks feed
	if (!$showFeed & $info['live_bookmark']) {
		if ( $info['bookmark_file'] ) {
		// custom bookmark filename
			$info['file'] = $mosConfig_absolute_path .'/cache/'. $info['bookmark_file'];
		} else {
		// standard bookmark filename
			$info['file'] = $mosConfig_absolute_path .'/cache/'. $info['live_bookmark'];
		}
	} else {
	// set filename for rss feeds
		$info['file'] = strtolower( str_replace( '.', '', $info['feed'] ) );
		$info['file'] = $mosConfig_absolute_path .'/cache/'. $info['file'] .'.xml';
	}

	// load feed creator class
	//$rss 	= new UniversalFeedCreator();
	$rss 	= new MamboFeedCreator();
	// load image creator class
	$image 	= new FeedImage();

	// loads cache file
	if ($showFeed && $info['cache']) {
		$rss->useCached( $info['feed'], $info['file'], $info['cache_time'] );
	}

	$rss->title 			= $info['title'];
	$rss->description 		= $info['description'];
	$rss->link				= $info['link'];
	$rss->syndicationURL	= $info['link'];
	$rss->cssStyleSheet		= NULL;
	$rss->encoding 			= $info['encoding'];

	if ( $info['image'] ) {
		$image->url 		= $info['image'];
		$image->link 		= $info['link'];
		$image->title 		= $info['image_alt'];
		$image->description	= $info['description'];
		// loads image info into rss array
		$rss->image 		= $image;
	}

/*
testing to extend Syndication capabilities
	$join 		= '';
	$and 		= '';
	$content	= $info['content'];
	switch ( $info['content'] ) {
		case 0:
			$and 		= "\n AND a.sectionid != '0'";
			$orderby 	= 'a.sectionid, a.ordering';
			break;

		case -1:
			$join 		= "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id";
			$orderby 	= 'f.ordering';
			break;

		default:
			$and 		= "\n AND a.sectionid = '$content'";
			$and 		.= "\n AND a.sectionid != '0'";
			$orderby 	= 'a.ordering';
			break;
	}
*/
	// Determine ordering for sql
	switch (strtolower( $info['orderby'] )) {
		case 'date':
			$orderby = 'a.created';
			break;

		case 'rdate':
			$orderby = 'a.created DESC';
			break;

		case 'alpha':
			$orderby = 'a.title';
			break;

		case 'ralpha':
			$orderby = 'a.title DESC';
			break;

		case 'hits':
			$orderby = 'a.hits DESC';
			break;

		case 'rhits':
			$orderby = 'a.hits ASC';
			break;

		case 'front':
			$orderby = ( $sectionid ? 'a.ordering' : 'f.ordering' );
			break;

		default:
			$orderby = ( $sectionid ? 'a.ordering' : 'f.ordering' );
			break;
	}

	if ($sectionid) {
		$join 	= '';
		$and 	= ' AND a.sectionid = ' . intval( $sectionid );
	} else {
		$join 	= "\n INNER JOIN #__content_frontpage AS f ON f.content_id = a.id";
		$and 	= '';
	}
	$limit		= ( $info['count'] ? $info['count'] : null );

	// query of frontpage content items
	$query = "SELECT a.*, u.name AS author, u.usertype, UNIX_TIMESTAMP( a.created ) AS created_ts"
	. "\n FROM #__content AS a"
	. $join
	. "\n LEFT JOIN #__users AS u ON u.id = a.created_by"
	. "\n WHERE a.state = '1'"
	. $and
	. "\n AND a.access = 0"
	. "\n AND ( publish_up = '$mosConfig_zero_date' OR publish_up <= '$now' )"
	. "\n AND ( publish_down = '$mosConfig_zero_date' OR publish_down >= '$now' )"
	. "\n ORDER BY $orderby"
	;
	$database->setQuery( $query, 0, $limit );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}
	$rows = $database->loadObjectList();

	foreach ($rows as $row) {
		// title for particular item
		$item_title = htmlspecialchars( $row->title );
		$item_title = html_entity_decode( $item_title );

		// url link to article
		// & used instead of &amp; as this is converted by feed creator
		$item_link = 'index.php?option=com_content&task=view&id='. $row->id .'&Itemid='. $mainframe->getItemid( $row->id );
  		$item_link = sefRelToAbs( $item_link );

		// removes all formating from the intro text for the description text
		$item_description = $row->introtext;
		$item_description = rssCleanText( $item_description );
		$item_description = html_entity_decode( $item_description );

		if ($info['limit_text']) {
			if ($info['text_length']) {
				// limits description text to x words
				$item_description_array = split( ' ', $item_description );
				$count = count( $item_description_array );

				if ($count > $info['text_length']) {
					$item_description = '';

					for ($a = 0; $a < $info['text_length']; $a++) {
						$item_description .= $item_description_array[$a]. ' ';
					}
					$item_description = trim( $item_description );
					$item_description .= '...';
				}
			} else  {
				// do not include description when text_length = 0
				$item_description = NULL;
			}
		}

		// load individual item creator class
		$item = new FeedItem();
		// item info
		$item->title 		= $item_title;
		$item->link 		= $item_link;
		$item->description 	= $item_description;
		$item->source 		= $info['link'];
		$item->date			= date( 'r',$row->created_ts );

		// loads item info into rss array
		$rss->addItem( $item );
	}

	// save feed file
	$rss->saveFeed( $info['feed'], $info['file'], $showFeed, $info['encoding'] );
}
?>