<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/application/input.php';

/**
 * Test class for JInput.
 */
class JInputTest extends PHPUnit_Framework_TestCase
{
	/**
	 */
	protected $inspector;

	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JInput_Inspector.php';
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JFilterInput_Mock.php';
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JFilterInput_Mock_Tracker.php';
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGet()
	{
		$this->inspector = new JInputInspector(null, array('filter' => new JFilterInputMock()));

		$_REQUEST['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->inspector->get('foo'),
			$this->equalTo('bar'),
			'Line: '.__LINE__.'.'
		);

		$_GET['foo'] = 'bar2';

		// Test the get method.
		$this->assertThat(
			$this->inspector->get->get('foo'),
			$this->equalTo('bar2'),
			'Line: '.__LINE__.'.'
		);

		// Test the get method.
		$this->assertThat(
			$this->inspector->get('default_value', 'default'),
			$this->equalTo('default'),
			'Line: '.__LINE__.'.'
		);

	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGetArray()
	{
		$filterMock = new JFilterInputMockTracker();

		$input = new JInput(
			array(
				'var1' => 'value1',
				'var2' => 34,
				'var3' => array('test')
			),
			array('filter' => $filterMock)
		);

		$this->assertThat(
			$input->getArray(
				array('var1' => 'filter1', 'var2' => 'filter2', 'var3' => 'filter3')
			),
			$this->equalTo(array('var1' => 'value1', 'var2' => 34, 'var3' => array('test'))),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][0],
			$this->equalTo(array('value1', 'filter1')),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][1],
			$this->equalTo(array(34, 'filter2')),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][2],
			$this->equalTo(array(array('test'), 'filter3')),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JInput::get method using a nested data set.
	 */
	public function testGetArrayNested()
	{
		$filterMock = new JFilterInputMockTracker();

		$input = new JInput(
			array(
				'var2' => 34,
				'var3' => array('var2' => 'test'),
				'var4' => array('var1' => array('var2' => 'test'))
			),
			array('filter' => $filterMock)
		);

		$this->assertThat(
			$input->getArray(
				array('var2' => 'filter2', 'var3' => array('var2' => 'filter3'))
			),
			$this->equalTo(array('var2' => 34, 'var3' => array('var2' => 'test'))),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$input->getArray(
				array('var4' => array('var1' => array('var2' => 'filter1')))
			),
			$this->equalTo(array('var4' => array('var1' => array('var2' => 'test')))),
			'Line: '.__LINE__.'.'
		);


		$this->assertThat(
			$filterMock->calls['clean'][0],
			$this->equalTo(array(34, 'filter2')),
			'Line: '.__LINE__.'.'
		);

		$this->assertThat(
			$filterMock->calls['clean'][1],
			$this->equalTo(array(array('var2' => 'test'), 'array')),
			'Line: '.__LINE__.'.'
		);
	}

	/**
	 * Test the JInput::get method.
	 */
	public function testGetFromCookie()
	{
		$this->inspector = new JInputInspector(null, array('filter' => new JFilterInputMock()));

		// Check the object type.
		$this->assertThat(
			$this->inspector->cookie instanceof JInputCookie,
			$this->isTrue(),
			'Line: '.__LINE__.'.'
		);

		$_COOKIE['foo'] = 'bar';

		// Test the get method.
		$this->assertThat(
			$this->inspector->cookie->get('foo'),
			$this->equalTo('bar'),
			'Line: '.__LINE__.'.'
		);
	}
}
