<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInputCookie.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       11.1
 */
class JInputCookieTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JInputCookie
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JInputCookie;
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSet().
	 *
	 * @return void
	 */
	public function testSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
