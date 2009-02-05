<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * DocumentFeed class, provides an easy interface to parse and display any feed document
 *
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
	 public $syndicationURL = "";

	 /**
	 * Image feed element
	 *
	 * optional
	 *
	 * @var		object
	 * @access	public
	 */
	 public $image = null;

	/**
	 * Copyright feed elememnt
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $copyright = "";

	 /**
	 * Published date feed element
	 *
	 *  optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $pubDate = "";

	 /**
	 * Lastbuild date feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $lastBuildDate = "";

	 /**
	 * Editor feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $editor = "";

	/**
	 * Docs feed element
	 *
	 * @var		string
	 * @access	public
	 */
	 public $docs = "";

	 /**
	 * Editor email feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $editorEmail = "";

	/**
	 * Webmaster email feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $webmaster = "";

	/**
	 * Category feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $category = "";

	/**
	 * TTL feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $ttl = "";

	/**
	 * Rating feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $rating = "";

	/**
	 * Skiphours feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $skipHours = "";

	/**
	 * Skipdays feed element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $skipDays = "";

	/**
	 * The feed items collection
	 *
	 * @var array
	 * @access public
	 */
	public $items = array();

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param	array	$options Associative array of options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		//set document type
		$this->_type = 'feed';
	}

	/**
	 * Render the document
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return 	The rendered data
	 */
	public function render($cache = false, $params = array())
	{
		$component	= JApplicationHelper::getComponentName();

		// Get the feed type
		$type = JRequest::getCmd('type', 'rss');

		/*
		 * Cache TODO In later release
		 */
		$cache		= 0;
		$cache_time = 3600;
		$cache_path = JPATH_BASE.DS.'cache';

		// set filename for rss feeds
		$file = strtolower(str_replace('.', '', $type));
		$file = $cache_path.DS.$file.'_'.$component.'.xml';


		// Instantiate feed renderer and set the mime encoding
		$renderer =& $this->loadRenderer(($type) ? $type : 'rss');
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
		$data .= $renderer->render(null);

		parent::render();
		return $data;
	}

	/**
	 * Adds an JFeedItem to the feed.
	 *
	 * @param object JFeedItem $item The feeditem to add to the feed.
	 * @access public
	 */
	public function addItem(&$item)
	{
		$this->items[] = $item;
	}

	/**
	 * Get the document head data
	 *
	 * @access	public
	 * @return	array	The document head data in array form
	 */
	public function getHeadData(){
		return false;
	}

	/**
	 * Set the document head data
	 *
	 * @access	public
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data) {
		return false;
	}
}

/**
 * JFeedItem is an internal class that stores feed item information
 *
 * @package 	Joomla.Framework
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
	public $title;

	/**
	 * Link item element
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	public $link;

	/**
	 * Description item element
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	 public $description;

	/**
	 * Author item element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $author;

	 /**
	 * Author email element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $authorEmail;

	/**
	 * Date element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $date;

	/**
	 * Category element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $category;

	 /**
	 * Comments element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $comments;

	 /**
	 * Enclosure element
	 *
	 * @var		object
	 * @access	public
	 */
	 public $enclosure =  null;

	 /**
	 * Guid element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $guid;

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
	 public $pubDate;

	 /**
	 * Source element
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $source;


	 /**
	 * Set the JFeedEnclosure for this item
	 *
	 * @access public
	 * @param object $enclosure The JFeedItem to add to the feed.
	 */
	 public function setEnclosure($enclosure)	{
		 $this->enclosure = $enclosure;
	 }
}

/**
 * JFeedEnclosure is an internal class that stores feed enclosure information
 *
 * @package 	Joomla.Framework
 * @subpackage		Document
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
	 public $url = "";

	/**
	 * Lenght enclosure element
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	 public $length = "";

	 /**
	 * Type enclosure element
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	 public $type = "";
}

/**
 * JFeedImage is an internal class that stores feed image information
 *
 * @package 	Joomla.Framework
 * @subpackage		Document
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
	 public $title = "";

	 /**
	 * URL image attribute
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	public $url = "";

	/**
	 * Link image attribute
	 *
	 * required
	 *
	 * @var		string
	 * @access	public
	 */
	 public $link = "";

	 /**
	 * witdh image attribute
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $width;

	 /**
	 * Title feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $height;

	 /**
	 * Title feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 * @access	public
	 */
	 public $description;
}
