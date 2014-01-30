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
 * RSS Feed Parser Namespace handler for MediaRSS.
 *
 * @package     Joomla.Platform
 * @subpackage  Feed
 * @see         http://video.search.yahoo.com/mrss
 * @since       12.3
 */
class JFeedParserRssMedia implements JFeedParserNamespace
{
	/**
	 * Method to handle an element for the feed given that the media namespace is present.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function processElementForFeed(JFeed $feed, SimpleXMLElement $el)
	{

	}

	/**
	 * Method to handle the feed entry element for the feed given that the media namespace is present.
	 *
	 * @param   JFeedEntry        $entry  The JFeedEntry object being built from the parsed feed entry.
	 * @param   SimpleXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function processElementForFeedEntry(JFeedEntry $entry, SimpleXMLElement $el)
	{

	}
}
