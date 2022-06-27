<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed\Parser;

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
        $feed->setAuthor(
            $this->inputFilter->clean((string) $el->name, 'html'),
            filter_var((string) $el->email, FILTER_VALIDATE_EMAIL),
            filter_var((string) $el->uri, FILTER_VALIDATE_URL)
        );
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
        $feed->addContributor(
            $this->inputFilter->clean((string) $el->name, 'html'),
            filter_var((string) $el->email, FILTER_VALIDATE_EMAIL),
            filter_var((string) $el->uri, FILTER_VALIDATE_URL)
        );
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
        $feed->generator = $this->inputFilter->clean((string) $el, 'html');
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
        $link = new FeedLink();
        $link->uri      = (string) $el['href'];
        $link->language = (string) $el['hreflang'];
        $link->length   = (int) $el['length'];
        $link->relation = (string) $el['rel'];
        $link->title    = $this->inputFilter->clean((string) $el['title'], 'html');
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
        $feed->copyright = $this->inputFilter->clean((string) $el, 'html');
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
        $feed->description = $this->inputFilter->clean((string) $el, 'html');
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
        $feed->title = $this->inputFilter->clean((string) $el, 'html');
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
        $feed->updatedDate = $this->inputFilter->clean((string) $el, 'html');
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
        // We want to move forward to the first XML Element after the xml doc type declaration
        $this->moveToNextElement();

        $this->version = ($this->stream->getAttribute('version') == '0.3') ? '0.3' : '1.0';
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
        $entry->title       = $this->inputFilter->clean((string) $el->title, 'html');
        $entry->updatedDate = $this->inputFilter->clean((string) $el->updated, 'html');
        $entry->content     = $this->inputFilter->clean((string) $el->summary, 'html');

        if (!$entry->content) {
            $entry->content = $this->inputFilter->clean((string) $el->content, 'html');
        }

        if (filter_var($entry->uri, FILTER_VALIDATE_URL) === false && !\is_null($el->link) && $el->link) {
            $link = $el->link;

            if (\is_array($link)) {
                $link = $this->bestLinkForUri($link);
            }

            $uri = (string) $link['href'];

            if ($uri) {
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

        foreach ($linkPrefs as $pref) {
            foreach ($links as $link) {
                $rel = (string) $link['rel'];

                if ($rel === $pref) {
                    return $link;
                }
            }
        }

        return array_shift($links);
    }
}
