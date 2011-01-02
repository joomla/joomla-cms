<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * DocumentFeed class, provides an easy interface to parse and display any feed document
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

jimport('joomla.document.document');

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
	 * @var		string
	 * @access	public
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
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		//set document type
		$this->_type = 'feed';
	}

	/**
	 * Render the document
	 *
	 * @access public
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	function render($cache = false, $params = array())
	{
		global $option;

		// Get the feed type
		$type = JRequest::getCmd('type', 'rss');

		/*
		 * Cache TODO In later release
		 */
		$cache		= 0;
		$cache_time = 3600;
		$cache_path = JPATH_CACHE;

		// set filename for rss feeds
		$file = strtolower(str_replace('.', '', $type));
		$file = $cache_path.DS.$file.'_'.$option.'.xml';


		// Instantiate feed renderer and set the mime encoding
		$renderer = $this->loadRenderer(($type) ? $type : 'rss');
		if (!is_a($renderer, 'JDocumentRenderer')) {
			JError::raiseError(404, JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
		}
		$this->setMimeEncoding($renderer->getContentType());

		//output
		// Generate prolog
		$data	= "<?xml version=\"1.0\" encoding=\"".$this->_charset."\"?>\n";
		$data	.= "<!-- generator=\"".$this->getGenerator()."\" -->\n";

		 // Generate stylesheet links
		foreach ($this->_styleSheets as $src => $attr) {
			$data .= "<?xml-stylesheet href=\"$src\" type=\"".$attr['mime']."\"?>\n";
		}

		// Render the feed
		$data .= $renderer->render();

		parent::render();
		return $data;
	}

	/**
	 * Adds an JFeedItem to the feed.
	 *
	 * @param object JFeedItem $item The feeditem to add to the feed.
	 * @access public
	 */
	function addItem(&$item)
	{
		$item->source = $this->link;
		$this->items[] = $item;
	}
}

/**
 * JFeedItem is an internal class that stores feed item information
 *
 * @package		Joomla.Framework
 * @subpackage		Document
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
	 * @var		array or string
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
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
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
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
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
