<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

/**
 * Feed factory class.
 *
 * @since  3.1.4
 */
class FeedFactory
{
    /**
     * @var    array  The list of registered parser classes for feeds.
     * @since  3.1.4
     */
    protected $parsers = array('rss' => 'Joomla\\CMS\\Feed\\Parser\\RssParser', 'feed' => 'Joomla\\CMS\\Feed\\Parser\\AtomParser');

    /**
     * Method to load a URI into the feed reader for parsing.
     *
     * @param   string  $uri  The URI of the feed to load. Idn uris must be passed already converted to punycode.
     *
     * @return  Feed
     *
     * @since   3.1.4
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     */
    public function getFeed($uri)
    {
        // Create the XMLReader object.
        $reader = new \XMLReader();

        // Open the URI within the stream reader.
        if (!@$reader->open($uri, null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING)) {
            // Retry with JHttpFactory that allow using CURL and Sockets as alternative method when available

            // Adding a valid user agent string, otherwise some feed-servers returning an error
            $options = new Registry();
            $options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

            try {
                $response = HttpFactory::getHttp($options)->get($uri);
            } catch (\RuntimeException $e) {
                throw new \RuntimeException('Unable to open the feed.', $e->getCode(), $e);
            }

            if ($response->code != 200) {
                throw new \RuntimeException('Unable to open the feed.');
            }

            // Set the value to the XMLReader parser
            if (!$reader->XML($response->body, null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING)) {
                throw new \RuntimeException('Unable to parse the feed.');
            }
        }

        try {
            // Skip ahead to the root node.
            while ($reader->read()) {
                if ($reader->nodeType == \XMLReader::ELEMENT) {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error reading feed.', $e->getCode(), $e);
        }

        // Setup the appropriate feed parser for the feed.
        $parser = $this->_fetchFeedParser($reader->name, $reader);

        return $parser->parse();
    }

    /**
     * Method to register a FeedParser class for a given root tag name.
     *
     * @param   string   $tagName    The root tag name for which to register the parser class.
     * @param   string   $className  The FeedParser class name to register for a root tag name.
     * @param   boolean  $overwrite  True to overwrite the parser class if one is already registered.
     *
     * @return  FeedFactory
     *
     * @since   3.1.4
     * @throws  \InvalidArgumentException
     */
    public function registerParser($tagName, $className, $overwrite = false)
    {
        // Verify that the class exists.
        if (!class_exists($className)) {
            throw new \InvalidArgumentException('The feed parser class ' . $className . ' does not exist.');
        }

        // Validate that the tag name is valid.
        if (!preg_match('/\A(?!XML)[a-z][\w0-9-]*/i', $tagName)) {
            throw new \InvalidArgumentException('The tag name ' . $tagName . ' is not valid.');
        }

        // Register the given parser class for the tag name if nothing registered or the overwrite flag set.
        if (empty($this->parsers[$tagName]) || (bool) $overwrite) {
            $this->parsers[(string) $tagName] = (string) $className;
        }

        return $this;
    }

    /**
     * Method to get the registered Parsers
     *
     * @return array
     *
     * @since   4.0.0
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * Method to return a new JFeedParser object based on the registered parsers and a given type.
     *
     * @param   string      $type    The name of parser to return.
     * @param   \XMLReader  $reader  The XMLReader instance for the feed.
     *
     * @return  FeedParser
     *
     * @since   3.1.4
     * @throws  \LogicException
     */
    private function _fetchFeedParser($type, \XMLReader $reader)
    {
        // Look for a registered parser for the feed type.
        if (empty($this->parsers[$type])) {
            throw new \LogicException('No registered feed parser for type ' . $type . '.');
        }

        return new $this->parsers[$type]($reader);
    }
}
