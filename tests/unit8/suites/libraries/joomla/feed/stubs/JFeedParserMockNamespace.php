<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Mock Feed Parser namespace class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class JFeedParserMockNamespace implements JFeedParserNamespace
{
	/**
	 * Method to handle an element for the feed given that the itunes namespace is present.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function processElementForFeed(JFeed $feed, SimpleXMLElement $el)
	{

	}

	/**
	 * Method to handle the feed entry element for the feed given that the itunes namespace is present.
	 *
	 * @param   JFeedEntry        $entry  The JFeedEntry object being built from the parsed feed entry.
	 * @param   SimpleXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function processElementForFeedEntry(JFeedEntry $entry, SimpleXMLElement $el)
	{

	}
}
