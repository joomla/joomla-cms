<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Feed\FeedImage;
use Joomla\CMS\Document\Feed\FeedItem;

/**
 * FeedDocument class, provides an easy interface to parse and display any feed document
 *
 * @since  11.1
 */
class FeedDocument extends Document
{
	/**
	 * Syndication URL feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $syndicationURL = '';

	/**
	 * Image feed element
	 *
	 * optional
	 *
	 * @var    FeedImage
	 * @since  11.1
	 */
	public $image = null;

	/**
	 * Copyright feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $copyright = '';

	/**
	 * Published date feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $pubDate = '';

	/**
	 * Lastbuild date feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $lastBuildDate = '';

	/**
	 * Editor feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $editor = '';

	/**
	 * Docs feed element
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $docs = '';

	/**
	 * Editor email feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $editorEmail = '';

	/**
	 * Webmaster email feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $webmaster = '';

	/**
	 * Category feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $category = '';

	/**
	 * TTL feed attribute
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $ttl = '';

	/**
	 * Rating feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $rating = '';

	/**
	 * Skiphours feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $skipHours = '';

	/**
	 * Skipdays feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $skipDays = '';

	/**
	 * The feed items collection
	 *
	 * @var    FeedItem[]
	 * @since  11.1
	 */
	public $items = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since  11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set document type
		$this->_type = 'feed';
	}

	/**
	 * Render the document
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string The rendered data
	 *
	 * @since   11.1
	 * @throws  \Exception
	 * @todo    Make this cacheable
	 */
	public function render($cache = false, $params = array())
	{
		// Get the feed type
		$type = \JFactory::getApplication()->input->get('type', 'rss');

		// Instantiate feed renderer and set the mime encoding
		$renderer = $this->loadRenderer(($type) ? $type : 'rss');

		if (!($renderer instanceof DocumentRenderer))
		{
			throw new \Exception(JText::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
		}

		$this->setMimeEncoding($renderer->getContentType());

		// Output
		// Generate prolog
		$data = "<?xml version=\"1.0\" encoding=\"" . $this->_charset . "\"?>\n";
		$data .= "<!-- generator=\"" . $this->getGenerator() . "\" -->\n";

		// Generate stylesheet links
		foreach ($this->_styleSheets as $src => $attr)
		{
			$data .= "<?xml-stylesheet href=\"$src\" type=\"" . $attr['type'] . "\"?>\n";
		}

		// Render the feed
		$data .= $renderer->render();

		parent::render();

		return $data;
	}

	/**
	 * Adds a FeedItem to the feed.
	 *
	 * @param   FeedItem  $item  The feeditem to add to the feed.
	 *
	 * @return  FeedDocument  instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addItem(FeedItem $item)
	{
		$item->source = $this->link;
		$this->items[] = $item;

		return $this;
	}
}
