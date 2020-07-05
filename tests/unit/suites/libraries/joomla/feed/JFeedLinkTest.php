<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JFeedLink.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.1.4
 */
class JFeedLinkTest extends TestCase
{
	/**
	 * Tests the JFeedLink::__construct() method.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function testConstruct()
	{
		$link = new JFeedLink('URI', 'self', 'application/x-pdf', 'en-GB', 'My Link', 5003932);

		$this->assertEquals($link->uri, 'URI');
		$this->assertEquals($link->relation, 'self');
		$this->assertEquals($link->type, 'application/x-pdf');
		$this->assertEquals($link->language, 'en-GB');
		$this->assertEquals($link->title, 'My Link');
		$this->assertEquals($link->length, 5003932);
	}

	/**
	 * Tests the JFeedLink::__construct() method with invalid length.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              3.1.4
	 */
	public function testConstructWithInvalidLength()
	{
		new JFeedLink('URI', 'self', 'application/x-pdf', 'en-GB', 'My Link', 'foobar');
	}
}
