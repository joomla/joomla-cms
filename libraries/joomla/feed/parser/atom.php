<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * ATOM Feed Parser class.
 *
 * @link   http://www.atomenabled.org/developers/syndication/
 * @since  12.3
 */
class JFeedParserAtom extends JFeedParser
{
	/**
	 * @var    string  The feed format version.
	 * @since  12.3
	 */
	protected $version;

	/**
	 * Method to handle the <author> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleAuthor(JFeed $feed, SimpleXMLElement $el)
	{
		// Set the author information from the XML element.
		$feed->setAuthor((string) $el->name, (string) $el->email, (string) $el->uri);
	}

	/**
	 * Method to handle the <contributor> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleContributor(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->addContributor((string) $el->name, (string) $el->email, (string) $el->uri);
	}

	/**
	 * Method to handle the <generator> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleGenerator(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->generator = (string) $el;
	}

	/**
	 * Method to handle the <id> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleId(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->uri = (string) $el;
	}

	/**
	 * Method to handle the <link> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleLink(JFeed $feed, SimpleXMLElement $el)
	{
		$link = new JFeedLink;
		$link->uri      = (string) $el['href'];
		$link->language = (string) $el['hreflang'];
		$link->length   = (int) $el['length'];
		$link->relation = (string) $el['rel'];
		$link->title    = (string) $el['title'];
		$link->type     = (string) $el['type'];

		$feed->link = $link;
	}

	/**
	 * Method to handle the <rights> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleRights(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->copyright = (string) $el;
	}

	/**
	 * Method to handle the <subtitle> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleSubtitle(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->description = (string) $el;
	}

	/**
	 * Method to handle the <title> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleTitle(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->title = (string) $el;
	}

	/**
	 * Method to handle the <updated> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleUpdated(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->updatedDate = (string) $el;
	}

	/**
	 * Method to initialise the feed for parsing.  Here we detect the version and advance the stream
	 * reader so that it is ready to parse feed elements.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function initialise()
	{
		// Read the version attribute.
		$this->version = ($this->stream->getAttribute('version') == '0.3') ? '0.3' : '1.0';

		// We want to move forward to the first element after the root element.
		$this->moveToNextElement();
	}

	/**
	 * Method to handle the feed entry element for the feed: <entry>.
	 *
	 * @param   JFeedEntry        $entry  The JFeedEntry object being built from the parsed feed entry.
	 * @param   SimpleXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function processFeedEntry(JFeedEntry $entry, SimpleXMLElement $el)
	{
		$entry->uri         = (string) $el->id;
		$entry->title       = (string) $el->title;
		$entry->updatedDate = (string) $el->updated;
		$entry->content     = (string) $el->summary;
	}
}
