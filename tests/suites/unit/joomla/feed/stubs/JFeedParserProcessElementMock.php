<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock Feed Parser class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserProcessElementMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  12.3
	 */
	public static $parseReturn = null;

	/**
	 * @var    string  Entry element name.
	 * @since  12.3
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
	 * @since   12.3
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
	 * @since   12.3
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
	 * @since   12.3
	 */
	protected function initialise()
	{
		// Do nothing.
	}
}
