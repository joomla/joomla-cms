<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Content component
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JViewRSSCategory extends JView
{
	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Category';

	/**
	 * Name of the view.
	 * 
	 * @access	private
	 * @var		string
	 */
	function display()
	{
		global $mainframe;
		
		// Initialize some variables
		$menu	= & $this->get( 'Menu' );
		$params	= & $menu->parameters;
		$Itemid	= $menu->id;

		// Get some data from the model
		$rows = & $this->get( 'Content' );
		
		$link       = $mainframe->getBaseURL() .'index.php?option=com_content&task=view&id=';
		$format		= 'RSS2.0';
		$limit		= '10';
		
		JRequest::setVar('limit', $limit);
		$category = & $this->get( 'Category' );
		$rows 	  = & $this->get( 'Content' );
		
		$count = count( $rows );
		for ( $i=0; $i < $count; $i++ ) 
		{
			$Itemid = $mainframe->getItemid( $rows[$i]->id );
			$rows[$i]->link     = $rows[$i]->link .'&Itemid='. $Itemid; 
			$rows[$i]->category = $category->title;
		}
    	
		JViewRSSCategory::createFeed( $rows, $format, $menu->name, $params );
	}
	
	function createFeed( $rows, $format, $title, &$params ) 
	{
		global $mainframe;
	
		$option = $mainframe->getOption();
	
		// parameter intilization
		$info[ 'date' ] 			= date( 'r' );
		$info[ 'year' ] 			= date( 'Y' );
		$info[ 'link' ] 			= htmlspecialchars( $mainframe->getBaseURL() );
		$info[ 'cache' ] 			= $params->def( 'cache', 1 );
		$info[ 'cache_time' ] 		= $params->def( 'cache_time', 3600 );
		$info[ 'count' ]			= $params->def( 'count', 5 );
		$info[ 'orderby' ] 			= $params->def( 'orderby', '' );
		$info[ 'title' ] 			= $mainframe->getCfg('sitename') .' - '. $title; 
		$info[ 'description' ] 		= $mainframe->getCfg('sitename') .' - '. $title .' Section'; 
		$info[ 'limit_text' ] 		= $params->def( 'limit_text', 1 );
		$info[ 'text_length' ] 		= $params->def( 'text_length', 20 );
		$info[ 'feed' ] 			= $format;

		// set filename for rss feeds
		$info[ 'file' ]   = strtolower( str_replace( '.', '', $info[ 'feed' ] ) );
		$info[ 'file' ]   = $mainframe->getCfg('cachepath') .'/'. $info[ 'file' ] .'_'. $option .'.xml';	

		// load feed creator class
		jimport('bitfolge.feedcreator');
		$syndicate 	= new UniversalFeedCreator();

		// loads cache file
		if ( $info[ 'cache' ] ) {
			$syndicate->useCached( $info[ 'feed' ], $info[ 'file' ], $info[ 'cache_time' ] );
		}
	
		$syndicate->title 			= $info[ 'title' ];
		$syndicate->description 	= $info[ 'description' ];
		$syndicate->link 			= $info[ 'link' ];
		$syndicate->syndicationURL 	= $info[ 'link' ];
		$syndicate->cssStyleSheet 	= NULL;
		$syndicate->encoding 		= 'UTF-8';
	
		foreach ( $rows as $row ) 
		{
			// strip html from feed item title
			$item_title = htmlspecialchars( $row->title );
			$item_title = html_entity_decode( $item_title );
		
			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$_Itemid	= '';
			$itemid 	= $mainframe->getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}
		
			$item_link = 'index.php?option=com_content&task=view&id='. $row->id . $_Itemid;
			$item_link = sefRelToAbs( $item_link );
		
			// strip html from feed item description text
			$item_description = $row->introtext;
		
			if ( $info[ 'limit_text' ] ) 
			{
				if ( $info[ 'text_length' ] ) 
				{
					// limits description text to x words
					$item_description_array = split( ' ', $item_description );
					$count = count( $item_description_array );
					if ( $count > $info[ 'text_length' ] ) 
					{
						$item_description = '';
						for ( $a = 0; $a < $info[ 'text_length' ]; $a++ ) {
							$item_description .= $item_description_array[$a]. ' ';
						}
						$item_description = trim( $item_description );
						$item_description .= '...';
					}
				} 
				else  
				{
					// do not include description when text_length = 0
					$item_description = NULL;
				}
			}
		
			$item_date = ( $row->date ? date( 'r', $row->date ) : '' );
		
			// load individual item creator class
			$item = new FeedItem();
			$item->title 		= $item_title;
			$item->link 		= $item_link;
			$item->description 	= $item_description;
			$item->source 		= $info[ 'link' ];
			$item->date			= $item_date;
			$item->category   	= $row->category;
		
			// loads item info into rss array
			$syndicate->addItem( $item );
		}
	
		// save feed file
		$syndicate->saveFeed( $info[ 'feed' ], $info[ 'file' ]);
	}
}
?>