<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed\Parser;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\FeedLink;
use Joomla\CMS\Feed\FeedParser;

/**
 * ATOM Feed Parser class.
 *
 * @link   http://www.atomenabled.org/developers/syndication/
 * @since  3.1.4
 */
class AtomParser extends FeedParser
{
	/**
	 * @var    string  The feed format version.
	 * @since  3.1.4
	 */
	protected $version;

	/**
	 * Method to handle the `<author>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleAuthor(Feed $feed, \SimpleXMLElement $el)
	{
		// Set the author information from the XML element.
		$feed->setAuthor((string) $el->name, (string) $el->email, (string) $el->uri);
	}

	/**
	 * Method to handle the `<contributor>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleContributor(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->addContributor((string) $el->name, (string) $el->email, (string) $el->uri);
	}

	/**
	 * Method to handle the `<generator>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleGenerator(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->generator = (string) $el;
	}

	/**
	 * Method to handle the `<id>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleId(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->uri = (string) $el;
	}

	/**
	 * Method to handle the `<link>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleLink(Feed $feed, \SimpleXMLElement $el)
	{
		$link = new FeedLink;
		$link->uri      = (string) $el['href'];
		$link->language = (string) $el['hreflang'];
		$link->length   = (int) $el['length'];
		$link->relation = (string) $el['rel'];
		$link->title    = (string) $el['title'];
		$link->type     = (string) $el['type'];

		$feed->link = $link;
	}

	/**
	 * Method to handle the `<rights>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleRights(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->copyright = (string) $el;
	}

	/**
	 * Method to handle the `<subtitle>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleSubtitle(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->description = (string) $el;
	}

	/**
	 * Method to handle the `<title>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleTitle(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->title = (string) $el;
	}

	/**
	 * Method to handle the `<updated>` element for the feed.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function handleUpdated(Feed $feed, \SimpleXMLElement $el)
	{
		$feed->updatedDate = (string) $el;
	}

	/**
	 * Method to initialise the feed for parsing.  Here we detect the version and advance the stream
	 * reader so that it is ready to parse feed elements.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function initialise()
	{
		// Read the version attribute.
		$this->version = ($this->stream->getAttribute('version') == '0.3') ? '0.3' : '1.0';

		// We want to move forward to the first element after the root element.
		$this->moveToNextElement();
	}

	/**
	 * Method to handle a `<entry>` element for the feed.
	 *
	 * @param   FeedEntry          $entry  The FeedEntry object being built from the parsed feed entry.
	 * @param   \SimpleXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function processFeedEntry(FeedEntry $entry, \SimpleXMLElement $el)
	{
		$entry->uri         = (string) $el->id;
		$entry->title       = (string) $el->title;
		$entry->updatedDate = (string) $el->updated;
		$entry->content     = (string) $el->summary;

		if (!$entry->content)
		{
			$entry->content = (string) $el->content;
		}

		if (filter_var($entry->uri, FILTER_VALIDATE_URL) === false && !is_null($el->link) && $el->link)
		{
			$link = $el->link;

			if (is_array($link))
			{
				$link = $this->bestLinkForUri($link);
			}

			$uri = (string) $link['href'];

			if ($uri)
			{
				$entry->uri = $uri;
			}
		}
	}

	/**
	 * If there is more than one <link> in the feed entry, find the most appropriate one and return it.
	 *
	 * @param   array  $links  Array of <link> elements from the feed entry.
	 *
	 * @return  \SimpleXMLElement
	 */
	private function bestLinkForUri(array $links)
	{
		$linkPrefs = array('', 'self', 'alternate');

		foreach ($linkPrefs as $pref)
		{
			foreach ($links as $link)
			{
				$rel = (string) $link['rel'];

				if ($rel === $pref)
				{
					return $link;
				}
			}
		}

		return array_shift($links);
	}
}
