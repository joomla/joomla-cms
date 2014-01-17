<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * RSS Feed Parser class.
 *
 * @package     Joomla.Platform
 * @subpackage  Feed
 * @link        http://cyber.law.harvard.edu/rss/rss.html
 * @since       12.3
 */
class JFeedParserRss extends JFeedParser
{
	/**
	 * @var    string  The feed element name for the entry elements.
	 * @since  12.3
	 */
	protected $entryElementName = 'item';

	/**
	 * @var    string  The feed format version.
	 * @since  12.3
	 */
	protected $version;

	/**
	 * Method to handle the <category> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleCategory(JFeed $feed, SimpleXMLElement $el)
	{
		// Get the data from the element.
		$domain    = (string) $el['domain'];
		$category  = (string) $el;

		$feed->addCategory($category, $domain);
	}

	/**
	 * Method to handle the <cloud> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleCloud(JFeed $feed, SimpleXMLElement $el)
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
	 * Method to handle the <copyright> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleCopyright(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->copyright = (string) $el;
	}

	/**
	 * Method to handle the <description> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleDescription(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->description = (string) $el;
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
	 * Method to handle the <image> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleImage(JFeed $feed, SimpleXMLElement $el)
	{
		// Create a feed link object for the image.
		$image = new JFeedLink(
			(string) $el->url,
			null,
			'logo',
			null,
			(string) $el->title
		);

		// Populate extra fields if they exist.
		$image->link         = (string) $el->link;
		$image->description  = (string) $el->description;
		$image->height       = (string) $el->height;
		$image->width        = (string) $el->width;

		$feed->image = $image;
	}

	/**
	 * Method to handle the <language> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleLanguage(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->language = (string) $el;
	}

	/**
	 * Method to handle the <lastBuildDate> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleLastBuildDate(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->updatedDate = (string) $el;
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
		$link->uri = (string) $el['href'];
		$feed->link = $link;
	}

	/**
	 * Method to handle the <managingEditor> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleManagingEditor(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->author = $this->processPerson((string) $el);
	}

	/**
	 * Method to handle the <skipDays> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleSkipDays(JFeed $feed, SimpleXMLElement $el)
	{
		// Initialise the array.
		$days = array();

		// Add all of the day values from the feed to the array.
		foreach ($el->day as $day)
		{
			$days[] = (string) $day;
		}

		$feed->skipDays = $days;
	}

	/**
	 * Method to handle the <skipHours> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleSkipHours(JFeed $feed, SimpleXMLElement $el)
	{
		// Initialise the array.
		$hours = array();

		// Add all of the day values from the feed to the array.
		foreach ($el->hour as $hour)
		{
			$hours[] = (int) $hour;
		}

		$feed->skipHours = $hours;
	}

	/**
	 * Method to handle the <pubDate> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handlePubDate(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->publishedDate = (string) $el;
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
	 * Method to handle the <ttl> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleTtl(JFeed $feed, SimpleXMLElement $el)
	{
		$feed->ttl = (integer) $el;
	}

	/**
	 * Method to handle the <webmaster> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function handleWebmaster(JFeed $feed, SimpleXMLElement $el)
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
		$this->version = $this->stream->getAttribute('version');

		// We want to move forward to the first element after the <channel> element.
		$this->moveToNextElement('channel');
		$this->moveToNextElement();
	}

	/**
	 * Method to handle the feed entry element for the feed: <item>.
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
		$entry->uri           = (string) $el->link;
		$entry->title         = (string) $el->title;
		$entry->publishedDate = (string) $el->pubDate;
		$entry->updatedDate   = (string) $el->pubDate;
		$entry->content       = (string) $el->description;
		$entry->guid          = (string) $el->guid;
		$entry->comments      = (string) $el->comments;

		// Add the feed entry author if available.
		$author = (string) $el->author;

		if (!empty($author))
		{
			$entry->author = $this->processPerson($author);
		}

		// Add any categories to the entry.
		foreach ($el->category as $category)
		{
			$entry->addCategory((string) $category, (string) $category['domain']);
		}

		// Add any enclosures to the entry.
		foreach ($el->enclosure as $enclosure)
		{
			$link = new JFeedLink(
				(string) $enclosure['url'],
				null,
				(string) $enclosure['type'],
				null,
				null,
				(int) $enclosure['length']
			);

			$entry->addLink($link);
		}
	}

	/**
	 * Method to parse a string with person data and return a JFeedPerson object.
	 *
	 * @param   string  $data  The string to parse for a person.
	 *
	 * @return  JFeedPerson
	 *
	 * @since   12.3
	 */
	protected function processPerson($data)
	{
		// Create a new person object.
		$person = new JFeedPerson;

		// This is really cheap parsing, but so far good enough. :)
		$data = explode(' ', $data, 2);

		if (isset($data[1]))
		{
			$person->name = trim($data[1], ' ()');
		}

		// Set the email for the person.
		$person->email = trim($data[0]);

		return $person;
	}
}
