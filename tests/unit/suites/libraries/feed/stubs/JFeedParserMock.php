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
 * @since       3.0
 */
class JFeedParserMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  3.0
	 */
	public static $parseReturn = null;

	/**
	 * Method to initialise the feed for parsing.  If child parsers need to detect versions or other
	 * such things this is where you'll want to implement that logic.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function initialise()
	{
		// Do nothing.
	}

	/**
	 * Method to parse the feed into a JFeed object.
	 *
	 * @return  JFeed
	 *
	 * @since   3.0
	 */
	public function parse()
	{
		if (is_null(self::$parseReturn))
		{
			return parent::parse();
		}

		$return = self::$parseReturn;

		self::$parseReturn = null;

		return $return;
	}
}
