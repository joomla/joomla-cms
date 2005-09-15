<?php
/**
* @version $Id: mod_rss.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo_4.5.3
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modRssData {

	function &getVars( &$params ){
		global $mosConfig_absolute_path, $_LANG;

		$cacheDir = $mosConfig_absolute_path .'/cache/';

		//check if cache diretory is writable as cache files will be created for the feed
		if ( !is_writable( $cacheDir ) ) {
			echo $_LANG->_( 'Please make cache directory writable.' );

			exit;
		}

		// module params
		$rssurl 			= $params->def( 'rssurl' );
		$rssitems 			= $params->def( 'rssitems', 5 );
		$rssdesc 			= $params->def( 'rssdesc', 1 );
		$rssimage 			= $params->def( 'rssimage', 1 );
		$rssitemdesc		= $params->def( 'rssitemdesc', 1 );
		$words 				= $params->def( 'word_count', 0 );

		$lists->show_descrip	= $rssdesc;
		$lists->show_image		= $rssimage;
		$lists->show_idescrip	= $rssitemdesc;

		$LitePath = $mosConfig_absolute_path .'/includes/Cache/Lite.php';
		mosFS::load( 'includes/domit/xml_domit_rss.php' );

		$rssDoc = new xml_domit_rss_document();
		$rssDoc->useCacheLite(true, $LitePath, $cacheDir, 3600);
		$rssDoc->loadRSS( $rssurl );

		$totalChannels 	= $rssDoc->getChannelCount();

		for ( $i = 0; $i < $totalChannels; $i++ ) {
			$currChannel =& $rssDoc->getChannel($i);
			$elements 	= $currChannel->getElementList();
			$iUrl		= 0;
			$iTitle		= $_LANG->_( 'Feed Image' );
			foreach ( $elements as $element ) {
				//image handling
				if ( $element == 'image' ) {
					$image =& $currChannel->getElement( DOMIT_RSS_ELEMENT_IMAGE );
					$iUrl	= $image->getUrl();
					$iTitle	= $image->getTitle();
				}
			}

			$lists->link 			= $currChannel->getLink();
			// feed title
			$lists->title 			= $currChannel->getTitle();
			// feed description
			$lists->description 	= $currChannel->getDescription();
			// feed image
			$lists->image_link 		= $iUrl;
			$lists->image_title 	= $iTitle;

			$actualItems 	= $currChannel->getItemCount();
			$setItems 		= $rssitems;

			if ( $setItems > $actualItems ) {
				$totalItems = $actualItems;
			} else {
				$totalItems = $setItems;
			}

			for ( $j = 0; $j < $totalItems; $j++ ) {
				$currItem =& $currChannel->getItem($j);
				// item title

				// item description
				$text = html_entity_decode( $currItem->getDescription() );
				// item description
				if ( $rssitemdesc ) {
					// word limit check
					if ( $words ) {
						$texts = explode( ' ', $text );
						$count = count( $texts );
						if ( $count > $words ) {
							$text = '';
							for( $i=0; $i < $words; $i++ ) {
								$text .= ' '. $texts[$i];
							}
							$text .= '...';
						}
					}
				}

				$rows[$j]->link 	= $currItem->getLink();
				$rows[$j]->title 	= $currItem->getTitle();
				$rows[$j]->text 	= $text;
			}
		}

		return array( $lists, $rows );
	}
}


class modRss {

	function show ( &$params ) {
		$cache  = mosFactory::getCache( "mod_rss" );

		$cache->setCaching($params->get('cache', 1));
		$cache->setCacheValidation(false);

		$cache->callId( "modRss::_display", array( $params ), "mod_rss" );
	}

	function _display( &$params ) {

		$vars = modRssData::getVars( $params );
		$lists = $vars[0];
		$rows = $vars[1];

		$tmpl =& moduleScreens::createTemplate( 'mod_rss.html' );

		$tmpl->addVar( 'mod_rss', 'class', 		$params->get( 'moduleclass_sfx' ) );

		$tmpl->addObject( 'mod_rss', 			$lists );
		$tmpl->addObject( 'body-list-rows', 	$rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_rss' );
	}
}

modRss::show( $params );
?>