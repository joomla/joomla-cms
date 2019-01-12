<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed\Parser;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;

/**
 * Feed Namespace interface.
 *
 * @since  3.1.4
 */
interface NamespaceParserInterface
{
	/**
	 * Method to handle an element for the feed given that a certain namespace is present.
	 *
	 * @param   Feed               $feed  The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function processElementForFeed(Feed $feed, \SimpleXMLElement $el);

	/**
	 * Method to handle the feed entry element for the feed given that a certain namespace is present.
	 *
	 * @param   FeedEntry          $entry  The FeedEntry object being built from the parsed feed entry.
	 * @param   \SimpleXMLElement  $el     The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function processElementForFeedEntry(FeedEntry $entry, \SimpleXMLElement $el);
}
