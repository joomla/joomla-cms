<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Input
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JInput.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Input
 * @since       11.1
 */
class JInputTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * The test class.
	 *
	 * @var  JInput
	 */
	protected $class;

	/**
	 * Test the JInput::__get method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__get()
	{
		$_POST['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->class->post->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);

		// Test the set method.
		$this->class->post->set('foo', 'notbar');
		$this->assertThat(
			$_POST['foo'],
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetArray()
	{
		$filterMock = new JFilterInputMockTracker;

		$array = array(
			'var1' => 'value1',
			'var2' => 34,
			'var3' => array('test')
		);
		$input = new JInput(
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
	 * Test the JInput::get method using a nested data set.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetArrayNested()
	{
		$filterMock = new JFilterInputMockTracker;

		$array = array(
			'var2' => 34,
			'var3' => array('var2' => 'test'),
			'var4' => array('var1' => array('var2' => 'test'))
		);
		$input = new JInput(
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
	 * Test the JInput::getArray method without specified variables.
	 *
	 * @return  void
	 *
	 * @since   12.3
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

		$input = new JInput($array);

		$this->assertEquals($input->getArray(), $array);
	}

	/**
	 * Test the JInput::get method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testGetFromCookie()
	{
		// Check the object type.
		$this->assertThat(
			$this->class->cookie instanceof JInputCookie,
			$this->isTrue(),
			'Line: ' . __LINE__ . '.'
		);

		$_COOKIE['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->class->cookie->get('foo'),
			$this->equalTo('bar'),
			'Line: ' . __LINE__ . '.'
		);
	}

	/**
	 * Test the JInput::serialize method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSerialize()
	{
		// Load the inputs so that the static $loaded is set to true.
		TestReflection::invoke($this->class, 'loadAllInputs');

		// Adjust the values so they are easier to handle.
		TestReflection::setValue($this->class, 'inputs', array('server' => 'remove', 'env' => 'remove', 'request' => 'keep'));
		TestReflection::setValue($this->class, 'options', 'options');
		TestReflection::setValue($this->class, 'data', 'data');

		$this->assertThat(
			$this->class->serialize(),
			$this->equalTo('a:3:{i:0;s:7:"options";i:1;s:4:"data";i:2;a:1:{s:7:"request";s:4:"keep";}}')
		);
	}

	/*
	 * Protected methods.
	 */

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();

		include_once __DIR__ . '/stubs/JInputInspector.php';
		include_once __DIR__ . '/stubs/JFilterInputMock.php';
		include_once __DIR__ . '/stubs/JFilterInputMockTracker.php';

		$array = null;
		$this->class = new JInputInspector($array, array('filter' => new JFilterInputMock));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->class);
		parent::tearDown();
	}
}
