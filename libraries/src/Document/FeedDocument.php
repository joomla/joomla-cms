<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Feed\FeedImage;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory as CmsFactory;
use Joomla\CMS\Language\Text;

/**
 * FeedDocument class, provides an easy interface to parse and display any feed document
 *
 * @since  1.7.0
 */
class FeedDocument extends Document
{
	/**
	 * Syndication URL feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $syndicationURL = '';

	/**
	 * Image feed element
	 *
	 * optional
	 *
	 * @var    FeedImage
	 * @since  1.7.0
	 */
	public $image = null;

	/**
	 * Copyright feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $copyright = '';

	/**
	 * Published date feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $pubDate = '';

	/**
	 * Lastbuild date feed element
	 *
	 * @var    \Joomla\CMS\Date\Date
	 * @since  1.7.0
	 */
	public $lastBuildDate;

	/**
	 * Editor feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $editor = '';

	/**
	 * Docs feed element
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $docs = '';

	/**
	 * Editor email feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $editorEmail = '';

	/**
	 * Webmaster email feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $webmaster = '';

	/**
	 * Category feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $category = '';

	/**
	 * TTL feed attribute
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $ttl = '';

	/**
	 * Rating feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $rating = '';

	/**
	 * Skiphours feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $skipHours = '';

	/**
	 * Skipdays feed element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $skipDays = '';

	/**
	 * The feed items collection
	 *
	 * @var    FeedItem[]
	 * @since  1.7.0
	 */
	public $items = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since  1.7.0
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set document type
		$this->_type = 'feed';

		// Gets and sets timezone offset from site configuration
		$this->lastBuildDate = CmsFactory::getDate();
		$this->lastBuildDate->setTimezone(new \DateTimeZone(CmsFactory::getApplication()->get('offset', 'UTC')));
	}

	/**
	 * Render the document
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string The rendered data
	 *
	 * @since   1.7.0
	 * @throws  \Exception
	 * @todo    Make this cacheable
	 */
	public function render($cache = false, $params = array())
	{
		// Get the feed type
		$type = CmsFactory::getApplication()->input->get('type', 'rss');

		// Instantiate feed renderer and set the mime encoding
		$renderer = $this->loadRenderer(($type) ? $type : 'rss');

		if (!($renderer instanceof DocumentRenderer))
		{
			throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
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

		parent::render($cache, $params);

		return $data;
	}

	/**
	 * Adds a FeedItem to the feed.
	 *
	 * @param   FeedItem  $item  The feeditem to add to the feed.
	 *
	 * @return  FeedDocument  instance of $this to allow chaining
	 *
	 * @since   1.7.0
	 */
	public function addItem(FeedItem $item)
	{
		$item->source = $this->link;
		$this->items[] = $item;

		return $this;
	}
}
