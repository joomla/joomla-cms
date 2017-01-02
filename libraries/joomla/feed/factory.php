<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Feed factory class.
 *
 * @since  12.3
 */
class JFeedFactory
{
	/**
	 * @var    array  The list of registered parser classes for feeds.
	 * @since  12.3
	 */
	protected $parsers = array('rss' => 'JFeedParserRss', 'feed' => 'JFeedParserAtom');

	/**
	 * Method to load a URI into the feed reader for parsing.
	 *
	 * @param   string  $uri  The URI of the feed to load. Idn uris must be passed already converted to punycode.
	 *
	 * @return  JFeedReader
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function getFeed($uri)
	{
		// Create the XMLReader object.
		$reader = new XMLReader;

		// Open the URI within the stream reader.
		if (!@$reader->open($uri, null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING))
		{
			// Retry with JHttpFactory that allow using CURL and Sockets as alternative method when available

			// Adding a valid user agent string, otherwise some feed-servers returning an error
			$options 	= new \joomla\Registry\Registry;
			$options->set('userAgent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0');

			$connector 	= JHttpFactory::getHttp($options);
			$feed 		= $connector->get($uri);

			if ($feed->code != 200)
			{
				throw new RuntimeException('Unable to open the feed.');
			}

			// Set the value to the XMLReader parser
			if (!$reader->xml($feed->body, null, LIBXML_NOERROR | LIBXML_ERR_NONE | LIBXML_NOWARNING))
			{
				throw new RuntimeException('Unable to parse the feed.');
			}

		}

		try
		{
			// Skip ahead to the root node.
			while ($reader->read())
			{
				if ($reader->nodeType == XMLReader::ELEMENT)
				{
					break;
				}
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException('Error reading feed.', $e->getCode(), $e);
		}

		// Setup the appopriate feed parser for the feed.
		$parser = $this->_fetchFeedParser($reader->name, $reader);

		return $parser->parse();
	}

	/**
	 * Method to register a JFeedParser class for a given root tag name.
	 *
	 * @param   string   $tagName    The root tag name for which to register the parser class.
	 * @param   string   $className  The JFeedParser class name to register for a root tag name.
	 * @param   boolean  $overwrite  True to overwrite the parser class if one is already registered.
	 *
	 * @return  JFeedFactory
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException
	 */
	public function registerParser($tagName, $className, $overwrite = false)
	{
		// Verify that the class exists.
		if (!class_exists($className))
		{
			throw new InvalidArgumentException('The feed parser class ' . $className . ' does not exist.');
		}

		// Validate that the tag name is valid.
		if (!preg_match('/\A(?!XML)[a-z][\w0-9-]*/i', $tagName))
		{
			throw new InvalidArgumentException('The tag name ' . $tagName . ' is not valid.');
		}

		// Register the given parser class for the tag name if nothing registered or the overwrite flag set.
		if (empty($this->parsers[$tagName]) || (bool) $overwrite)
		{
			$this->parsers[(string) $tagName] = (string) $className;
		}

		return $this;
	}

	/**
	 * Method to return a new JFeedParser object based on the registered parsers and a given type.
	 *
	 * @param   string     $type    The name of parser to return.
	 * @param   XMLReader  $reader  The XMLReader instance for the feed.
	 *
	 * @return  JFeedParser
	 *
	 * @since   12.3
	 * @throws  LogicException
	 */
	private function _fetchFeedParser($type, XMLReader $reader)
	{
		// Look for a registered parser for the feed type.
		if (empty($this->parsers[$type]))
		{
			throw new LogicException('No registered feed parser for type ' . $type . '.');
		}

		return new $this->parsers[$type]($reader);
	}
}
