<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed\Parser\Rss;

use Joomla\CMS\Feed\Feed;
use Joomla\CMS\Feed\FeedEntry;
use Joomla\CMS\Feed\Parser\NamespaceParserInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * RSS Feed Parser Namespace handler for MediaRSS.
 *
 * @link   https://www.rssboard.org/media-rss
 * @since  3.1.4
 */
class MediaRssParser implements NamespaceParserInterface
{
    /**
     * Method to handle an element for the feed given that the media namespace is present.
     *
     * @param   Feed               $feed  The Feed object being built from the parsed feed.
     * @param   \SimpleXMLElement  $el    The current XML element object to handle.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function processElementForFeed(Feed $feed, \SimpleXMLElement $el)
    {
    }

    /**
     * Method to handle the feed entry element for the feed given that the media namespace is present.
     *
     * @param   FeedEntry          $entry  The FeedEntry object being built from the parsed feed entry.
     * @param   \SimpleXMLElement  $el     The current XML element object to handle.
     *
     * @return  void
     *
     * @since   3.1.4
     */
    public function processElementForFeedEntry(FeedEntry $entry, \SimpleXMLElement $el)
    {
    }
}
