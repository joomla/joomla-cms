<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedPerson.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedPersonTest extends TestCase
{
	/**
	 * Tests the JFeedPerson::__construct() method.
	 *
	 * @return  void
	 *
	 * @covers  JFeedPerson::__construct
	 * @since   12.3
	 */
	public function testConstruct()
	{
		$person = new JFeedPerson('Name', 'eMail', 'URI', 'test');

		$this->assertEquals($person->name, 'Name');
		$this->assertEquals($person->email, 'eMail');
		$this->assertEquals($person->uri, 'URI');
		$this->assertEquals($person->type, 'test');
	}
}
