<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * DocumentFeed class, provides an easy interface to parse and display any feed document
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
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
	 */
	public $syndicationURL = "";

	/**
	 * Image feed element
	 *
	 * optional
	 *
	 * @var		object
	 */
	public $image = null;

	/**
	 * Copyright feed elememnt
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $copyright = "";

	/**
	 * Published date feed element
	 *
	 *  optional
	 *
	 * @var		string
	 */
	public $pubDate = "";

	/**
	 * Lastbuild date feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $lastBuildDate = "";

	/**
	 * Editor feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $editor = "";

	/**
	 * Docs feed element
	 *
	 * @var		string
	 */
	public $docs = "";

	/**
	 * Editor email feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $editorEmail = "";

	/**
	 * Webmaster email feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $webmaster = "";

	/**
	 * Category feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $category = "";

	/**
	 * TTL feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $ttl = "";

	/**
	 * Rating feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $rating = "";

	/**
	 * Skiphours feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $skipHours = "";

	/**
	 * Skipdays feed element
	 *
	 * optional
	 *
	 * @var		string
	 */
	public $skipDays = "";

	/**
	 * The feed items collection
	 *
	 * @var array
	 */
	public $items = array();

	/**
	 * Class constructor
	 *
	 * @param	array	$options Associative array of options
	 */
	protected function __construct($options = array())
	{
		parent::__construct($options);

		//set document type
		$this->_type = 'feed';
	}

	/**
	 * Render the document
	 *
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	public function render($cache = false, $params = array())
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

		// Output
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
	 */
	public function addItem(&$item)
	{
		$item->source = $this->link;
		$this->items[] = $item;
	}
}

/**
 * JFeedItem is an internal class that stores feed item information
 *
 * @package		Joomla.Platform
 * @subpackage		Document
 * @since	11.1
 */
class JFeedItem extends JObject
{
	/**
	 * Title item element
	 *
	 * required
	 *
	 * @var		string
	 */
	public $title;

	/**
	 * Link item element
	 *
	 * required
	 *
	 * @var		string
	 */
	public $link;

	/**
	 * Description item element
	 *
	 * required
	 *
	 * @var		string
	 */
	public  var $description;

	/**
	 * Author item element
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $author;

	 /**
	 * Author email element
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $authorEmail;


	/**
	 * Category element
	 *
	 * optional
	 *
	 * @var		array or string
	 */
	 public $category;

	 /**
	 * Comments element
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $comments;

	 /**
	 * Enclosure element
	 *
	 * @var		object
	 */
	 public $enclosure =  null;

	 /**
	 * Guid element
	 *
	 * optional
	 *
	 * @var		string
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
	 */
	 public $date;

	 /**
	 * Source element
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $source;


	 /**
	 * Set the JFeedEnclosure for this item
	 *
	 * @param object $enclosure The JFeedItem to add to the feed.
	 */
	 public function setEnclosure($enclosure)	{
		 $this->enclosure = $enclosure;
	 }
}

/**
 * JFeedEnclosure is an internal class that stores feed enclosure information
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JFeedEnclosure extends JObject
{
	/**
	 * URL enclosure element
	 *
	 * required
	 *
	 * @var		string
	 */
	 public $url = "";

	/**
	 * Length enclosure element
	 *
	 * required
	 *
	 * @var		string
	 */
	 public $length = "";

	 /**
	 * Type enclosure element
	 *
	 * required
	 *
	 * @var		string
	 */
	 public $type = "";
}

/**
 * JFeedImage is an internal class that stores feed image information
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JFeedImage extends JObject
{
	/**
	 * Title image attribute
	 *
	 * required
	 *
	 * @var		string
	 */
	 public $title = "";

	 /**
	 * URL image attribute
	 *
	 * required
	 *
	 * @var		string
	 */
	public $url = "";

	/**
	 * Link image attribute
	 *
	 * required
	 *
	 * @var		string
	 */
	 public $link = "";

	 /**
	 * Width image attribute
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $width;

	 /**
	 * Title feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $height;

	 /**
	 * Title feed attribute
	 *
	 * optional
	 *
	 * @var		string
	 */
	 public $description;
}
