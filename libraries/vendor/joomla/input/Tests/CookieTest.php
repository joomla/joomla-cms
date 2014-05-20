<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Cookie;
use Joomla\Test\TestHelper;

/**
 * Test class for JInputCookie.
 *
 * @since  1.0
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Cookie
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the Joomla\Input\Cookie::set method.
	 *
	 * @return  void
	 *
	 * @todo    Figure out out to tests w/o ob_start() in bootstrap. setcookie() prevents this.
	 *
	 * @covers  Joomla\Input\Cookie::set
	 * @since   1.0
	 */
	public function testSet()
	{
		if (headers_sent())
		{
			$this->markTestSkipped();
		}
		else
		{
			$this->instance->set('foo', 'bar');

			$data = TestHelper::getValue($this->instance, 'data');

			$this->assertTrue(array_key_exists('foo', $data));
			$this->assertTrue(in_array('bar', $data));
		}
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

		$this->instance = new Cookie;
	}
}
