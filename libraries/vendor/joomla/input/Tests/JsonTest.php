<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Json;

/**
 * Test class for Joomla\Input\Json.
 *
 * @since  1.0
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Files
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the Joomla\Input\Json::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Json::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Json::getRaw method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Json::getRaw
	 * @since   1.0
	 */
	public function testgetRaw()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Sets up the fixture.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->instance = new Json;
	}
}
