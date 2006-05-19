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
	 * Syndication URL feed element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $syndicationURL = "";
	 
	 /**
	 * Image feed element 
	 * 
	 * optional
	 * 
	 * @var		object
	 * @access	public 	
	 */
	 var $image = null;
	 
	/**
	 * Copyright feed elememnt 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $copyright = "";
	  
	 /**
	 * Published date feed element
	 * 
	 *  optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $pubDate = "";
	 
	 /**
	 * Lastbuild date feed element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $lastBuildDate = "";
	 
	 /**
	 * Editor feed element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $editor = "";
	 
	/**
     * Docs feed element
     *
     * @var       string
     * @access    public
     */
	 var $docs = "";
	 
	 /**
	 * Editor email feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $editorEmail = "";
	 
	/**
	 * Webmaster email feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $webmaster = "";
	 
	/**
	 * Category feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $category = "";
	 
	/**
	 * TTL feed attribute 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $ttl = "";
	 
	/**
	 * Rating feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $rating = "";
	 
	/**
	 * Skiphours feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $skipHours = "";
	 
	/**
	 * Skipdays feed element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $skipDays = "";
	 
	/**
	 * The feed items collection
	 * 
	 * @var array
	 * @access public
	 */
	var $items = array();
	
	/**
	 * Class constructor
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
		
		$format     = isset($params['format']) ? $params['format'] : 'RSS';
		$cache      = 0;
		$cache_time = 3600;
		$cache_path = $mainframe->getCfg('cachepath');
		$option 	= $mainframe->getOption();
		
		// set filename for rss feeds
		$file = strtolower( str_replace( '.', '', $format ) );
		$file = $cache_path.'/'. $file .'_'. $option .'.xml';
		
		$renderer = $this->loadRenderer($format);
		
		$path 	= JApplicationHelper::getPath( 'front', $option );
		$task 	= JRequest::getVar( 'task' );

		//load common language files
		$lang =& $mainframe->getLanguage();
		$lang->load($option);
		require_once( $path );
		
		//set feed information
		$this->_engine->title 		= $this->getTitle();
		$this->_engine->description = $this->getDescription();
		
		//output
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );		// HTTP/1.5
		header( 'Pragma: no-cache' );										// HTTP/1.0
		header( 'Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);
    
		// Generate prolog
		$result  = "<?xml version=\"1.0\" encoding=\"".$this->_charset."\"?>\n";
		$result .= "<!-- generator=\"".$this->getGenerator()."\" -->\n";
		
		 // Generate stylesheet links
        foreach ($this->_styleSheets as $src => $attr ) {
            $result .= "<?xml-stylesheet href=\"$src\" type=\"".$attr['mime']."\"?>\n";
        }
		
		// Render the feed
		$result .= $renderer->render( );
		
		echo $result;
	}
	
	/**
	 * Adds an JFeedItem to the feed.
	 *
	 * @param object JFeedItem $item The feeditem to add to the feed.
	 * @access public
	 */
	function addItem( &$item )
	{
		$item->source = $this->link;
		$this->items[] = $item;
	}
}

/**
 * JFeedItem is an internal class that stores feed item information
 * 
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Document
 * @since	1.5
 */
class JFeedItem extends JObject
{
	/**
	 * Title item element 
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	var $title;
	
	/**
	 * Link item element
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	  var $link;
	
	/**
	 * Description item element
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $description;
	 
	/**
	 * Author item element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $author;
	 
	 /**
	 * Author email element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $authorEmail;
	 
  
	/**
	 * Category element
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $category;
	 
	 /**
	 * Comments element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $comments;
	 
	 /**
	 * Enclosure element
	 *
	 * @var		object
	 * @access	public 	
	 */
	 var $enclosure =  null;
	 
	 /**
	 * Guid element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $guid;
	 
	/**
	 * Published date 
	 * 
	 * optional
	 * 
	 *  May be in one of the following formats:
	 *
	 *	RFC 822:
	 *	"Mon, 20 Jan 03 18:05:41 +0400"
	 *	"20 Jan 03 18:05:41 +0000"
	 *
	 *	ISO 8601:
	 *	"2003-01-20T18:05:41+04:00"
	 *
	 *	Unix:
	 *	1043082341
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $pubDate;
	 
	 /**
	 * Source element 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $source;
	

	 /**
	 * Set the JFeedEnclosure for this item
	 *
	 * @access public
	 * @param object $enclosure The JFeedItem to add to the feed.
	 */
	 function setEnclosure($enclosure)	{
		 $this->enclosure = $enclosure;
	 }
}

/**
 * JFeedEnclosure is an internal class that stores feed enclosure information
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Document
 * @since	1.5
 */
class JFeedEnclosure extends JObject
{
	/**
	 * URL enclosure element
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $url = "";
	 
	/**
	 * Lenght enclosure element
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $length = "";
	 
	 /**
	 * Type enclosure element
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $type = "";
}

/**
 * JFeedImage is an internal class that stores feed image information
 *
 * @author	Johan Janssens <johan.janssens@joomla.org>
 *
 * @package 	Joomla.Framework
 * @subpackage 	Document
 * @since	1.5
 */
class JFeedImage extends JObject
{
	/**
	 * Title image attribute 
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $title = "";
	 
	 /**
	 * URL image attribute 
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	var $url = "";
	
	/**
	 * Link image attribute 
	 * 
	 * required
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $link = "";

	 /**
	 * witdh image attribute 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $width;
	 
	 /**
	 * Title feed attribute 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $height;
	 
	 /**
	 * Title feed attribute 
	 * 
	 * optional
	 * 
	 * @var		string
	 * @access	public 	
	 */
	 var $description;
}
?>