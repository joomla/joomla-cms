<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock Feed Parser class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedParserProcessElementMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  12.1
	 */
	public static $parseReturn = null;

	/**
	 * @var    string  Entry element name.
	 * @since  12.1
	 */
	public $entryElementName = 'myentry';

	public function processElement(JFeed $feed, JXMLElement $el, array $namespaces)
	{
		parent::processElement($feed, $el, $namespaces);
	}

	public function handleElement1($feed, $el)
	{
		// this is to be mocked
	}

	protected function initialise()
	{
		// Do nothing.
	}
}
