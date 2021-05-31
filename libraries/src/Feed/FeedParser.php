<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Feed\Parser\NamespaceParserInterface;
use Joomla\CMS\Filter\InputFilter;

/**
 * Feed Parser class.
 *
 * @since  3.1.4
 */
abstract class FeedParser
{
	/**
	 * The feed element name for the entry elements.
	 *
	 * @var    string
	 * @since  3.1.4
	 */
	protected $entryElementName = 'entry';

	/**
	 * Array of NamespaceParserInterface objects
	 *
	 * @var    array
	 * @since  3.1.4
	 */
	protected $namespaces = array();

	/**
	 * The XMLReader stream object for the feed.
	 *
	 * @var    \XMLReader
	 * @since  3.1.4
	 */
	protected $stream;

	/**
	 * The InputFilter
	 *
	 * @var    InputFilter
	 * @since  3.9.25
	 */
	protected $inputFilter;

	/**
	 * Constructor.
	 *
	 * @param   \XMLReader   $stream       The XMLReader stream object for the feed.
	 * @param   InputFilter  $inputFilter  The InputFilter object to be used
	 *
	 * @since   3.1.4
	 */
	public function __construct(\XMLReader $stream, InputFilter $inputFilter = null)
	{
		$this->stream      = $stream;
		$this->inputFilter = $inputFilter ? $inputFilter : InputFilter::getInstance(array(), array(), 1, 1);
	}

	/**
	 * Method to parse the feed into a JFeed object.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function parse()
	{
		$feed = new Feed;

		// Detect the feed version.
		$this->initialise();

		// Let's get this party started...
		do
		{
			// Expand the element for processing.
			$el = new \SimpleXMLElement($this->stream->readOuterXml());

			// Get the list of namespaces used within this element.
			$ns = $el->getNamespaces(true);

			// Get an array of available namespace objects for the element.
			$namespaces = array();

			foreach ($ns as $prefix => $uri)
			{
				// Ignore the empty namespace prefix.
				if (empty($prefix))
				{
					continue;
				}

				// Get the necessary namespace objects for the element.
				$namespace = $this->fetchNamespace($prefix);

				if ($namespace)
				{
					$namespaces[] = $namespace;
				}
			}

			// Process the element.
			$this->processElement($feed, $el, $namespaces);

			// Skip over this element's children since it has been processed.
			$this->moveToClosingElement();
		}

		while ($this->moveToNextElement());

		return $feed;
	}

	/**
	 * Method to register a namespace handler object.
	 *
	 * @param   string                    $prefix     The XML namespace prefix for which to register the namespace object.
	 * @param   NamespaceParserInterface  $namespace  The namespace object to register.
	 *
	 * @return  FeedParser
	 *
	 * @since   3.1.4
	 */
	public function registerNamespace($prefix, NamespaceParserInterface $namespace)
	{
		$this->namespaces[$prefix] = $namespace;

		return $this;
	}

	/**
	 * Method to initialise the feed for parsing.  If child parsers need to detect versions or other
	 * such things this is where you'll want to implement that logic.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	abstract protected function initialise();

	/**
	 * Method to parse a specific feed element.
	 *
	 * @param   Feed               $feed        The Feed object being built from the parsed feed.
	 * @param   \SimpleXMLElement  $el          The current XML element object to handle.
	 * @param   array              $namespaces  The array of relevant namespace objects to process for the element.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function processElement(Feed $feed, \SimpleXMLElement $el, array $namespaces)
	{
		// Build the internal method name.
		$method = 'handle' . ucfirst($el->getName());

		// If we are dealing with an item then it is feed entry time.
		if ($el->getName() == $this->entryElementName)
		{
			// Create a new feed entry for the item.
			$entry = new FeedEntry;

			// First call the internal method.
			$this->processFeedEntry($entry, $el);

			foreach ($namespaces as $namespace)
			{
				if ($namespace instanceof NamespaceParserInterface)
				{
					$namespace->processElementForFeedEntry($entry, $el);
				}
			}

			// Add the new entry to the feed.
			$feed->addEntry($entry);

			return;
		}

		// Otherwise we treat it like any other element.

		// First call the internal method.
		if (\is_callable(array($this, $method)))
		{
			$this->$method($feed, $el);
		}

		foreach ($namespaces as $namespace)
		{
			if ($namespace instanceof NamespaceParserInterface)
			{
				$namespace->processElementForFeed($feed, $el);
			}
		}
	}

	/**
	 * Method to get a namespace object for a given namespace prefix.
	 *
	 * @param   string  $prefix  The XML prefix for which to fetch the namespace object.
	 *
	 * @return  mixed  NamespaceParserInterface or false if none exists.
	 *
	 * @since   3.1.4
	 */
	protected function fetchNamespace($prefix)
	{
		if (isset($this->namespaces[$prefix]))
		{
			return $this->namespaces[$prefix];
		}

		$className = \get_class($this) . ucfirst($prefix);

		if (class_exists($className))
		{
			$this->namespaces[$prefix] = new $className;

			return $this->namespaces[$prefix];
		}

		return false;
	}

	/**
	 * Method to move the stream parser to the next XML element node.
	 *
	 * @param   string  $name  The name of the element for which to move the stream forward until is found.
	 *
	 * @return  boolean  True if the stream parser is on an XML element node.
	 *
	 * @since   3.1.4
	 */
	protected function moveToNextElement($name = null)
	{
		// Only keep looking until the end of the stream.
		while ($this->stream->read())
		{
			// As soon as we get to the next ELEMENT node we are done.
			if ($this->stream->nodeType == \XMLReader::ELEMENT)
			{
				// If we are looking for a specific name make sure we have it.
				if (isset($name) && ($this->stream->name != $name))
				{
					continue;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Method to move the stream parser to the closing XML node of the current element.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws  \RuntimeException  If the closing tag cannot be found.
	 */
	protected function moveToClosingElement()
	{
		// If we are on a self-closing tag then there is nothing to do.
		if ($this->stream->isEmptyElement)
		{
			return;
		}

		// Get the name and depth for the current node so that we can match the closing node.
		$name  = $this->stream->name;
		$depth = $this->stream->depth;

		// Only keep looking until the end of the stream.
		while ($this->stream->read())
		{
			// If we have an END_ELEMENT node with the same name and depth as the node we started with we have a bingo. :-)
			if (($this->stream->name == $name) && ($this->stream->depth == $depth) && ($this->stream->nodeType == \XMLReader::END_ELEMENT))
			{
				return;
			}
		}

		throw new \RuntimeException('Unable to find the closing XML node.');
	}
}
