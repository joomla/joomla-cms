<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Mock Feed Parser class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class JFeedParserProcessElementMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  3.1.4
	 */
	public static $parseReturn = null;

	/**
	 * @var    string  Entry element name.
	 * @since  3.1.4
	 */
	public $entryElementName = 'myentry';

	/**
	 * Method to parse a specific feed element.
	 *
	 * @param   JFeed             $feed        The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el          The current XML element object to handle.
	 * @param   array             $namespaces  The array of relevant namespace objects to process for the element.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function processElement(JFeed $feed, SimpleXMLElement $el, array $namespaces)
	{
		parent::processElement($feed, $el, $namespaces);
	}

	/**
	 * Method to handle the <link> element for the feed.
	 *
	 * @param   JFeed             $feed  The JFeed object being built from the parsed feed.
	 * @param   SimpleXMLElement  $el    The current XML element object to handle.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function handleElement1($feed, $el)
	{
		// This is to be mocked.
	}

	/**
	 * Do Nothing.
	 *
	 * @return  void
	 *
	 * @see     JFeedParser::initialise()
	 * @since   3.1.4
	 */
	protected function initialise()
	{
		// Do nothing.
	}
}
