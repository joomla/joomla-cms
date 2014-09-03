<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Files;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/FilterInputMock.php';

/**
 * Test class for \Joomla\Input\Files.
 *
 * @since  1.0
 */
class FilesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test the Joomla\Input\Files::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::__construct
	 * @since   1.1.4
	 */
	public function test__construct()
	{
		// Default constructor call
		$instance = new Files;

		$this->assertInstanceOf(
			'Joomla\Filter\InputFilter',
			TestHelper::getValue($instance, 'filter')
		);

		$this->assertEmpty(
			TestHelper::getValue($instance, 'options')
		);

		$this->assertEquals(
			$_FILES,
			TestHelper::getValue($instance, 'data')
		);

		// Given Source & filter
		$src = array('foo' => 'bar');
		$instance = new Files($src, array('filter' => new FilterInputMock));

		$this->assertArrayHasKey(
			'filter',
			TestHelper::getValue($instance, 'options')
		);
	}

	/**
	 * Test the Joomla\Input\Files::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$instance = new Files;

		$this->assertEquals('foobar', $instance->get('myfile', 'foobar'));

		$data = array(
			'myfile' => array(
				'name' => 'n',
				'type' => 'ty',
				'tmp_name' => 'tm',
				'error' => 'e',
				'size' => 's'
			),
			'myfile2' => array(
				'name' => 'nn',
				'type' => 'ttyy',
				'tmp_name' => 'ttmm',
				'error' => 'ee',
				'size' => 'ss'
			)
		);

		$decoded = TestHelper::setValue($instance, 'data', $data);
		$expected = array(
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		);

		$this->assertEquals($expected, $instance->get('myfile'));
	}

	/**
	 * Test the Joomla\Input\Files::decodeData method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::decodeData
	 * @since   1.1.4
	 */
	public function testDecodeData()
	{
		$instance = new Files;

		$data = array('n', 'ty', 'tm', 'e', 's');
		$decoded = TestHelper::invoke($instance, 'decodeData', $data);
		$expected = array(
			'name' => 'n',
			'type' => 'ty',
			'tmp_name' => 'tm',
			'error' => 'e',
			'size' => 's'
		);

		$this->assertEquals($expected, $decoded);

		$dataArr = array('first', 'second');
		$data = array($dataArr , $dataArr, $dataArr, $dataArr, $dataArr);

		$decoded = TestHelper::invoke($instance, 'decodeData', $data);
		$expectedFirst = array(
			'name' => 'first',
			'type' => 'first',
			'tmp_name' => 'first',
			'error' => 'first',
			'size' => 'first'
		);
		$expectedSecond = array(
			'name' => 'second',
			'type' => 'second',
			'tmp_name' => 'second',
			'error' => 'second',
			'size' => 'second'
		);
		$expected = array($expectedFirst, $expectedSecond);
		$this->assertEquals($expected, $decoded);
	}

	/**
	 * Test the Joomla\Input\Files::set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Files::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$instance = new Files;

		$this->assertNull($instance->set('foo', 'bar'));
	}
}
