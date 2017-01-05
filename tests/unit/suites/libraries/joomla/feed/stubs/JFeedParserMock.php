<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Mock Feed Parser class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedParserMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  12.3
	 */
	public static $parseReturn;

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

	/**
	 * Return the static value.
	 *
	 * @return  mixed
	 *
	 * @see     JFeedParser::parse()
	 * @since   12.3
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
