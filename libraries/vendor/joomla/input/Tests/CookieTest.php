<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Cookie;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/FilterInputMock.php';

/**
 * Test class for \Joomla\Input\Cookie.
 *
 * @since  1.0
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the Joomla\Input\Cookie::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Cookie::__construct
	 * @since   1.1.4
	 */
	public function test__construct()
	{
		// Default constructor call
		$instance = new Cookie;

		$this->assertInstanceOf(
			'Joomla\Filter\InputFilter',
			TestHelper::getValue($instance, 'filter')
		);

		$this->assertEmpty(
			TestHelper::getValue($instance, 'options')
		);

		$this->assertEquals(
			$_COOKIE,
			TestHelper::getValue($instance, 'data')
		);

		// Given Source & filter
		$src = array('foo' => 'bar');
		$instance = new Cookie($src, array('filter' => new FilterInputMock));

		$this->assertArrayHasKey(
			'filter',
			TestHelper::getValue($instance, 'options')
		);
	}

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
		$instance = new Cookie;
		$instance->set('foo', 'bar');

		$data = TestHelper::getValue($instance, 'data');

		$this->assertArrayHasKey('foo', $data);
		$this->assertContains('bar', $data);
	}
}

// Stub for setcookie
namespace Joomla\Input;

/**
 * Stub.
 *
 * @param   string  $name      Name
 * @param   string  $value     Value
 * @param   int     $expire    Expire
 * @param   string  $path      Path
 * @param   string  $domain    Domain
 * @param   bool    $secure    Secure
 * @param   bool    $httpOnly  HttpOnly
 *
 * @return  void
 *
 * @since   1.1.4
 */
function setcookie($name, $value, $expire = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
{
	return true;
}
