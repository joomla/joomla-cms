<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * RSS Feed Parser class.
 *
 * @package     Joomla.Platform
 * @subpackage  Feed
 * @link        http://cyber.law.harvard.edu/rss/rss.html
 * @since       12.1
 */
class JFeedParserRss extends JFeedParser
{
	/**
	 * @var    string  The feed element name for the entry elements.
	 * @since  12.1
	 */
	protected $entryElementName = 'item';

	/**
	 * Method to detect the feed version.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function detectVersion()
	{
		// Read the version attribute.
		$this->version = $this->stream->getAttribute('version');

		// We want to move forward to the first element after the <channel> element.
		$this->moveToNextElement('channel');
		$this->moveToNextElement();
	}

	/**
	 * Method to handle the <title> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleTitle(JFeed $feed, JXMLElement $el)
	{
		$feed->title = (string) $el;
	}

	/**
	 * Method to handle the <link> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleLink(JFeed $feed, JXMLElement $el)
	{
		$feed->uri = (string) $el;
	}

	/**
	 * Method to handle the <description> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleDescription(JFeed $feed, JXMLElement $el)
	{
		$feed->description = (string) $el;
	}

	/**
	 * Method to handle the <language> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleLanguage(JFeed $feed, JXMLElement $el)
	{
		$feed->language = (string) $el;
	}

	/**
	 * Method to handle the <copyright> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleCopyright(JFeed $feed, JXMLElement $el)
	{
		$feed->copyright = (string) $el;
	}

	/**
	 * Method to handle the <lastBuildDate> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleLastBuildDate(JFeed $feed, JXMLElement $el)
	{
		$feed->updatedDate = (string) $el;
	}

	/**
	 * Method to handle the <pubDate> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handlePubDate(JFeed $feed, JXMLElement $el)
	{
		$feed->publishedDate = (string) $el;
	}

	/**
	 * Method to handle the <managingEditor> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleManagingEditor(JFeed $feed, JXMLElement $el)
	{
		// Get the tag contents and split it over the first space.
		$tmp = (string) $el;
		$tmp = explode(' ', $tmp, 2);

		$author = new JFeedPerson;
		$author->email = trim($tmp[0]);

		// This is really cheap parsing.  Probably need to create a method to do this more robustly.
		if (isset($tmp[1]))
		{
			$author->name = trim($tmp[1], ' ()');
		}

		$feed->author = $author;
	}

	/**
	 * Method to handle the <webmaster> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleWebmaster(JFeed $feed, JXMLElement $el)
	{
		// Get the tag contents and split it over the first space.
		$tmp = (string) $el;
		$tmp = explode(' ', $tmp, 2);

		// This is really cheap parsing.  Probably need to create a method to do this more robustly.
		$name = null;
		if (isset($tmp[1]))
		{
			$name = trim($tmp[1], ' ()');
		}
		$email = trim($tmp[0]);

		$feed->addContributor($name, $email, null, 'webmaster');
	}

	/**
	 * Method to handle the <generator> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleGenerator(JFeed $feed, JXMLElement $el)
	{
		$feed->generator = (string) $el;
	}

	/**
	 * Method to handle the <cloud> element for the feed.
	 *
	 * @param   JFeed        $feed  The JFeed object being built from the parsed feed.
	 * @param   JXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function handleCloud(JFeed $feed, JXMLElement $el)
	{
		$cloud = new stdClass;
		$cloud->domain            = (string) $el['domain'];
		$cloud->port              = (string) $el['port'];
		$cloud->path              = (string) $el['path'];
		$cloud->protocol          = (string) $el['protocol'];
		$cloud->registerProcedure = (string) $el['registerProcedure'];

		$feed->cloud = $cloud;
	}

	/**
	 * Method to handle the feed entry element for the feed: <item>.
	 *
	 * @param   JFeedEntry   $entry  The JFeedEntry object being built from the parsed feed entry.
	 * @param   JXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function processFeedEntry(JFeedEntry $entry, JXMLElement $el)
	{
		$entry->uri           = (string) $el->link;
		$entry->title         = (string) $el->title;
		$entry->publishedDate = (string) $el->pubDate;
		$entry->updatedDate   = (string) $el->pubDate;
		$entry->content       = (string) $el->description;
	}
}
