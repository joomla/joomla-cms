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
class JFeedParserMock extends JFeedParser
{
	/**
	 * @var    mixed  The value to return when the parse method is called.
	 * @since  3.1.4
	 */
	public static $parseReturn;

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

	/**
	 * Return the static value.
	 *
	 * @return  mixed
	 *
	 * @see     JFeedParser::parse()
	 * @since   3.1.4
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
