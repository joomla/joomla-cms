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

jimport('bitfolge.feedcreator');

/**
 * DocumentFeed class, provides an easy interface to parse and display any feed document
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

class JDocumentFeed extends JDocument
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
		
		//set mime type
		$this->_mime = 'text/xml';
		
		//set document type
		$this->_type = 'feed';
		
		global $mainframe;
		$option = $mainframe->getOption();
		
		// load feed creator class
		$this->_engine = new UniversalFeedCreator();
		
		$this->_engine->link 			= htmlspecialchars( $mainframe->getBaseURL());
		$this->_engine->syndicationURL 	= htmlspecialchars( $mainframe->getBaseURL());
		$this->_engine->encoding 		= 'UTF-8';
		$this->_engine->cssStyleSheet 	= null;
	}

	/**
	 * Outputs the document to the browser.
	 *
	 * @access public
	  * @param boolean 	$cache		If true, cache the output 
	 * @param boolean 	$compress	If true, compress the output
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $cache = false, $compress = false, $params = array())
	{
		global $mainframe;
		
		$feed       = isset($params['format']) ? $params['format'] : 'RSS2.0';
		$cache      = 0;
		$cache_time = 3600;
		$cache_path = $mainframe->getCfg('cachepath');
		$option 	= $mainframe->getOption();
		
			// set filename for rss feeds
		$file = strtolower( str_replace( '.', '', $feed ) );
		$file = $cache_path.'/'. $file .'_'. $option .'.xml';
		
		// loads cache file
		if ( $cache ) {
			$this->_engine->useCached( $feed, $file, $cache_time );
		}

		$path 	= JApplicationHelper::getPath( 'front', $option );
		$task 	= JRequest::getVar( 'task' );

		//load common language files
		$lang =& $mainframe->getLanguage();
		$lang->load($option);
		require_once( $path );
		
		//set feed information
		$this->_engine->title 		= $this->getTitle();
		$this->_engine->description = $this->getDescription();
		
		// display the feed
		$this->_engine->saveFeed( $feed, $file);
	}
	
	/**
	 * Adds an FeedItem to the feed.
	 *
	 * @param object FeedItem $item The FeedItem to add to the feed.
	 * @access public
	 */
	function addItem( &$item )
	{
		$item->source = $this->_engine->link;
		$this->_engine->addItem($item);
	}
}
?>