<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedLink.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedLinkTest extends JoomlaTestCase
{
	/**
	 * Tests the JFeedLink::__construct() method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedLink::__construct
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
	 * @since   12.1
	 *
	 * @covers             JFeedLink::__construct
	 * @expectedException  InvalidArgumentException
	 */
	public function testConstructWithInvalidLength()
	{
		$link = new JFeedLink('URI', 'self', 'application/x-pdf', 'en-GB', 'My Link', 'foobar');
	}
}
