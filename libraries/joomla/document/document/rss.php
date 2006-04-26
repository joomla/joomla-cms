<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * DocumentRSS class, provides an easy interface to parse and display an rss document
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

class JDocumentRSS extends JDocument
{
	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	string	$type 		(either html or tex)
	 * @param	array	$attributes Associative array of attributes
	 */
	function __construct($attributes = array())
	{
		parent::__construct($attributes);
		
		// load feed creator class
		jimport('bitfolge.feedcreator');
		$this->_engine = new UniversalFeedCreator();

		//set mime type
		$this->_mime = 'text/xml';
	}

	/**
	 * Outputs the document to the browser.
	 *
	 * @access public
	 * @param string 	$template	The name of the template
	 * @param boolean 	$file		If true, compress the output using Zlib compression
	 * @param boolean 	$compress	If true, will display information about the placeholders
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $template, $file, $compress = false, $params = array())
	{
		global $mainframe;

		$option = $mainframe->getOption();

		$path 	= JApplicationHelper::getPath( 'front', $option );
		$task 	= JRequest::getVar( 'task' );

		//load common language files
		$lang =& $mainframe->getLanguage();
		$lang->load($option);
		require_once( $path );
	}
	
	function createFeed( $rows, $format, $title)
	{
		global $mainframe;

		$option = $mainframe->getOption();

		// parameter intilization
		$info[ 'date' ] 			= date( 'r' );
		$info[ 'year' ] 			= date( 'Y' );
		$info[ 'link' ] 			= htmlspecialchars( $mainframe->getBaseURL() );
		$info[ 'cache' ] 			= 1;
		$info[ 'cache_time' ] 		= 3600;
		$info[ 'count' ]			= 5;
		$info[ 'orderby' ] 			= '';
		$info[ 'title' ] 			= $mainframe->getCfg('sitename') .' - '. $title;
		$info[ 'description' ] 		= $mainframe->getCfg('sitename') .' - '. $title .' Section';
		$info[ 'limit_text' ] 		= 1;
		$info[ 'text_length' ] 		= 20;
		$info[ 'feed' ] 			= $format;

		// set filename for rss feeds
		$info[ 'file' ]   = strtolower( str_replace( '.', '', $info[ 'feed' ] ) );
		$info[ 'file' ]   = $mainframe->getCfg('cachepath') .'/'. $info[ 'file' ] .'_'. $option .'.xml';

		// loads cache file
		if ( $info[ 'cache' ] ) {
			$this->_engine->useCached( $info[ 'feed' ], $info[ 'file' ], $info[ 'cache_time' ] );
		}

		$this->_engine->title 			= $info[ 'title' ];
		$this->_engine->description 	= $info[ 'description' ];
		$this->_engine->link 			= $info[ 'link' ];
		$this->_engine->syndicationURL 	= $info[ 'link' ];
		$this->_engine->cssStyleSheet 	= NULL;
		$this->_engine->encoding 		= 'UTF-8';

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
			$item_description = $row->description;

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
			$this->_engine->addItem( $item );
		}
		
		// save feed file
		$this->_engine->saveFeed( $info[ 'feed' ], $info[ 'file' ]);
	}
}
?>