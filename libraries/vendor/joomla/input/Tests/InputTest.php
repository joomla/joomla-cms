<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Input\Tests;

use Joomla\Input\Input;
use Joomla\Input\Cookie;
use Joomla\Test\TestHelper;

require_once __DIR__ . '/Stubs/FilterInputMock.php';

/**
 * Test class for Input.
 *
 * @since  1.0
 */
class InputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * The test class.
	 *
	 * @var    Input
	 * @since  1.0
	 */
	private $instance;

	/**
	 * Test the Joomla\Input\Input::__construct method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::__construct
	 * @since   1.0
	 */
	public function test__construct()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Input::__call method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::__call
	 * @since   1.0
	 */
	public function test__call()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Input::__get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::__get
	 * @since   1.0
	 */
	public function test__get()
	{
		$_POST['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->instance->post->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		// Test the set method.
		$this->instance->post->set('foo', 'notbar');
		$this->assertThat(
			$_POST['foo'],
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Input::count method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::count
	 * @since   1.0
	 */
	public function testCount()
	{
		$this->assertEquals(
			count($_REQUEST),
			count($this->instance)
		);

		$this->assertEquals(
			count($_POST),
			count($this->instance->post)
		);

		$this->assertEquals(
			count($_GET),
			count($this->instance->get)
		);
	}

	/**
	 * Test the Joomla\Input\Input::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::get
	 * @since   1.0
	 */
	public function testGet()
	{
		$_REQUEST['foo'] = 'bar';

		$instance = new Input;

		// Test the get method.
		$this->assertThat(
			$instance->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		$_GET['foo'] = 'bar2';

		// Test the get method.
		$this->assertThat(
			$instance->get->get('foo'),
			$this->equalTo('bar2'),
			'Checks first use of new super-global.'
		);

		// Test the get method.
		$this->assertThat(
			$instance->get('default_value', 'default'),
			$this->equalTo('default'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Input::def method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::def
	 * @since   1.0
	 */
	public function testDef()
	{
		$_REQUEST['foo'] = 'bar';

		$this->instance->def('foo', 'nope');

		$this->assertThat(
			$_REQUEST['foo'],
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		$this->instance->def('Joomla', 'is great');

		$this->assertArrayNotHasKey('Joomla', $_REQUEST, 'Checks super-global was not modified.');
	}

	/**
	 * Test the Joomla\Input\Input::set method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::set
	 * @since   1.0
	 */
	public function testSet()
	{
		$_REQUEST['foo'] = 'bar2';
		$this->instance->set('foo', 'bar');

		$this->assertThat(
			$_REQUEST['foo'],
			$this->equalTo('bar2'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Input::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::get
	 * @since   1.0
	 */
	public function testGetArray()
	{
		$filterMock = new FilterInputMock;

		$array = array(
			'var1' => 'value1',
			'var2' => 34,
			'var3' => array('test')
		);
		$input = new Input(
			$array,
			array('filter' => $filterMock)
		);

		$this->assertThat(
			$input->getArray(
				array('var1' => 'filter1', 'var2' => 'filter2', 'var3' => 'filter3')
			),
			$this->equalTo(array('var1' => 'value1', 'var2' => 34, 'var3' => array('test'))),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][0],
			$this->equalTo(array('value1', 'filter1')),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][1],
			$this->equalTo(array(34, 'filter2')),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][2],
			$this->equalTo(array(array('test'), 'filter3')),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Input::get method using a nested data set.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::get
	 * @since   1.0
	 */
	public function testGetArrayNested()
	{
		$filterMock = new FilterInputMock;

		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test'),
			'var4' => array('var1' => array('var2' => 'test'))
		);
		$input = new Input(
			$array,
			array('filter' => $filterMock)
		);

		$this->assertThat(
			$input->getArray(
				array('var2' => 'filter2', 'var3' => array('var2' => 'filter3'))
			),
			$this->equalTo(array('var2' => 34, 'var3' => array('var2' => 'test'))),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$input->getArray(
				array('var4' => array('var1' => array('var2' => 'filter1')))
			),
			$this->equalTo(array('var4' => array('var1' => array('var2' => 'test')))),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][0],
			$this->equalTo(array(34, 'filter2')),
			'Line: ' . __LINE__ . '.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][1],
			$this->equalTo(array(array('var2' => 'test'), 'array')),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Input::getArray method without specified variables.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::getArray
	 * @since   1.0
	 */
	public function testGetArrayWithoutSpecifiedVariables()
	{
		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test'),
			'var4' => array('var1' => array('var2' => 'test')),
			'var5' => array('foo' => array()),
			'var6' => array('bar' => null),
			'var7' => null
		);

		$input = new Input($array);

		$this->assertEquals($input->getArray(), $array);
	}

	/**
	 * Test the Joomla\Input\Input::get method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::get
	 * @since   1.0
	 */
	public function testGetFromCookie()
	{
		// Check the object type.
		$this->assertThat(
			$this->instance->cookie instanceof Cookie,
			$this->isTrue(),
			'Line: ' . __LINE__ . '.'
		);

		$_COOKIE['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->instance->cookie->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the Joomla\Input\Input::getMethod method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::getMethod
	 * @since   1.0
	 */
	public function testGetMethod()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Test the Joomla\Input\Input::serialize method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::serialize
	 * @since   1.0
	 */
	public function testSerialize()
	{
		// Load the inputs so that the static $loaded is set to true.
		TestHelper::invoke($this->instance, 'loadAllInputs');

		// Adjust the values so they are easier to handle.
		TestHelper::setValue($this->instance, 'inputs', array('server' => 'remove', 'env' => 'remove', 'request' => 'keep'));
		TestHelper::setValue($this->instance, 'options', 'options');
		TestHelper::setValue($this->instance, 'data', 'data');

		$this->assertThat(
			$this->instance->serialize(),
			$this->equalTo('a:3:{i:0;s:7:"options";i:1;s:4:"data";i:2;a:1:{s:7:"request";s:4:"keep";}}')
		);
	}

	/**
	 * Test the Joomla\Input\Input::unserialize method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::unserialize
	 * @since   1.0
	 */
	public function testUnserialize()
	{
		$this->markTestIncomplete();
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Test the Joomla\Input\Input::loadAllInputs method.
	 *
	 * @return  void
	 *
	 * @covers  Joomla\Input\Input::loadAllInputs
	 * @since   1.0
	 */
	public function testLoadAllInputs()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$array = null;
		$this->instance = new Input($array, array('filter' => new FilterInputMock));
	}
}
