<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Json;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/FilterInputMock.php';

/**
 * Test class for Joomla\Input\Json.
 *
 * @since  1.0
 */
class JsonTest extends \PHPUnit_Framework_TestCase
{
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
		// Default constructor call
		$instance = new Json;

		$this->assertInstanceOf(
			'Joomla\Filter\InputFilter',
			TestHelper::getValue($instance, 'filter')
		);

		$this->assertEmpty(
			TestHelper::getValue($instance, 'options')
		);

		$this->assertEmpty(
			TestHelper::getValue($instance, 'data')
		);

		// Given Source & filter
		$src = array('foo' => 'bar');
		$instance = new Json($src, array('filter' => new FilterInputMock));

		$this->assertArrayHasKey(
			'filter',
			TestHelper::getValue($instance, 'options')
		);

		$this->assertEquals(
			$src,
			TestHelper::getValue($instance, 'data')
		);

		// Src from GLOBAL
		$GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';
		$instance = new Json;

		$this->assertEquals(
			array('a' => 1, 'b' => 2),
			TestHelper::getValue($instance, 'data')
		);
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
		$GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":1,"b":2}';
		$instance = new Json;

		$this->assertEquals(
			$GLOBALS['HTTP_RAW_POST_DATA'],
			$instance->getRaw()
		);
	}
}
