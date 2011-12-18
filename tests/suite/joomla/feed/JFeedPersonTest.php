<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedPerson.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedPersonTest extends JoomlaTestCase
{
	/**
	 * Tests the JFeedPerson::__construct() method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedPerson::__construct
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
