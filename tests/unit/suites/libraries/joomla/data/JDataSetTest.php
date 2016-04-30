<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Data
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JDataBuran', __DIR__ . '/stubs/buran.php');
JLoader::register('JDataVostok', __DIR__ . '/stubs/vostok.php');

/**
 * Tests for the JDataSet class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Data
 * @since       12.3
 */
class JDataSetTest extends TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var    JDataSet
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__construct()
	{
		$this->assertEmpty(TestReflection::getValue(new JDataSet, '_objects'), 'New list should have no objects.');

		$input = array(
			'key' => new JData(array('foo' => 'bar'))
		);
		$new = new JDataSet($input);

		$this->assertEquals($input, TestReflection::getValue($new, '_objects'), 'Check initialised object list.');
	}

	/**
	 * Tests the __construct method with an array that does not contain JData objects.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function test__construct_array()
	{
		new JDataSet(array('foo'));
	}

	/**
	 * Tests the __call method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__call()
	{
		$this->assertThat(
			$this->_instance->launch('go'),
			$this->equalTo(array(1 => 'go'))
		);
	}

	/**
	 * Tests the __get method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__get()
	{
		$this->assertThat(
			$this->_instance->pilot,
			$this->equalTo(array(0 => null, 1 => 'Yuri Gagarin'))
		);
	}

	/**
	 * Tests the __isset method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__isset()
	{
		$this->assertTrue(isset($this->_instance->pilot), 'Property exists.');

		$this->assertFalse(isset($this->_instance->duration), 'Unknown property');
	}

	/**
	 * Tests the __set method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__set()
	{
		$this->_instance->successful = 'yes';

		$this->assertThat(
			$this->_instance->successful,
			$this->equalTo(array(0 => 'yes', 1 => 'YES'))
		);
	}

	/**
	 * Tests the __unset method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__unset()
	{
		unset($this->_instance->pilot);

		$this->assertNull($this->_instance[1]->pilot);
	}

	/**
	 * Tests the count method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCount()
	{
		$this->assertCount(2, $this->_instance);
	}

	/**
	 * Tests the clear method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testClear()
	{
		$this->assertGreaterThan(0, count($this->_instance), 'Check there are objects set.');
		$this->_instance->clear();
		$this->assertCount(0, $this->_instance, 'Check the objects were cleared.');
	}

	/**
	 * Tests the current method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCurrent()
	{
		$object = $this->_instance[0];

		$this->assertThat(
			$this->_instance->current(),
			$this->equalTo($object)
		);

		$new = new JDataSet(array('foo' => new JData));

		$this->assertThat(
			$new->current(),
			$this->equalTo(new JData)
		);
	}

	/**
	 * Tests the dump method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDump()
	{
		$this->assertEquals(
			array(
				new stdClass,
				(object) array(
					'mission' => 'Vostok 1',
					'pilot' => 'Yuri Gagarin',
				),
			),
			$this->_instance->dump()
		);
	}

	/**
	 * Tests the jsonSerialize method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testJsonSerialize()
	{
		$this->assertEquals(
			array(
				new stdClass,
				(object) array(
					'mission' => 'Vostok 1',
					'pilot' => 'Yuri Gagarin',
				),
			),
			$this->_instance->jsonSerialize()
		);
	}

	/**
	 * Tests the key method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testKey()
	{
		$this->assertEquals(0, $this->_instance->key());
	}

	/**
	 * Tests the keys method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testKeys()
	{
		$instance = new JDataSet;
		$instance['key1'] = new JData;
		$instance['key2'] = new JData;

		$this->assertEquals(array('key1', 'key2'), $instance->keys());
	}

	/**
	 * Tests the next method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testNext()
	{
		$this->_instance->next();
		$this->assertThat(
			TestReflection::getValue($this->_instance, '_current'),
			$this->equalTo(1)
		);

		$this->_instance->next();
		$this->assertThat(
			TestReflection::getValue($this->_instance, '_current'),
			$this->equalTo(false)
		);
	}

	/**
	 * Tests the offsetExists method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetExists()
	{
		$this->assertTrue($this->_instance->offsetExists(0));
		$this->assertFalse($this->_instance->offsetExists(2));
		$this->assertFalse($this->_instance->offsetExists('foo'));
	}

	/**
	 * Tests the offsetGet method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetGet()
	{
		$this->assertInstanceOf('JDataBuran', $this->_instance->offsetGet(0));
		$this->assertInstanceOf('JDataVostok', $this->_instance->offsetGet(1));
		$this->assertNull($this->_instance->offsetGet('foo'));
	}

	/**
	 * Tests the offsetSet method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetSet()
	{
		$this->_instance->offsetSet(0, new JData);
		$objects = TestReflection::getValue($this->_instance, '_objects');

		$this->assertEquals(new JData, $objects[0], 'Checks explicit use of offsetSet.');

		$this->_instance[] = new JData;
		$this->assertInstanceOf('JData', $this->_instance[1], 'Checks the array push equivalent with [].');

		$this->_instance['foo'] = new JData;
		$this->assertInstanceOf('JData', $this->_instance['foo'], 'Checks implicit usage of offsetSet.');
	}

	/**
	 * Tests the offsetSet method for an expected exception
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testOffsetSet_exception1()
	{
		// By implication, this will call offsetSet.
		$this->_instance['foo'] = 'bar';
	}

	/**
	 * Tests the offsetUnset method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetUnset()
	{
		$this->_instance->offsetUnset(0);
		$objects = TestReflection::getValue($this->_instance, '_objects');

		$this->assertFalse(isset($objects[0]));
	}

	/**
	 * Tests the offsetRewind method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetRewind()
	{
		TestReflection::setValue($this->_instance, '_current', 'foo');

		$this->_instance->rewind();
		$this->assertEquals(0, $this->_instance->key());

		$this->_instance->clear();
		$this->assertFalse($this->_instance->key());
	}

	/**
	 * Tests the valid method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testValid()
	{
		$this->assertTrue($this->_instance->valid());

		TestReflection::setValue($this->_instance, '_current', null);

		$this->assertFalse($this->_instance->valid());
	}

	/**
	 * Test that JDataSet::_initialise method indirectly.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test_initialise()
	{
		$this->assertInstanceOf('JDataBuran', $this->_instance[0]);
		$this->assertInstanceOf('JDataVostok', $this->_instance[1]);
	}

	/*
	 * Ancillary tests.
	 */

	/**
	 * Tests using JDataSet in a foreach statement.
	 *
	 * @return  void
	 *
	 * @coversNothing  Integration test.
	 * @since          12.3
	 */
	public function test_foreach()
	{
		// Test multi-item list.
		$tests = array();

		foreach ($this->_instance as $key => $object)
		{
			$tests[] = $object->mission;
		}

		$this->assertEquals(array(null, 'Vostok 1'), $tests);

		// Tests single item list.
		$this->_instance->clear();
		$this->_instance['1'] = new JData;
		$runs = 0;

		foreach ($this->_instance as $key => $object)
		{
			$runs++;
		}

		$this->assertEquals(1, $runs);

		// Exhaustively testing unsetting within a foreach.
		$this->_instance['2'] = new JData;
		$this->_instance['3'] = new JData;
		$this->_instance['4'] = new JData;
		$this->_instance['5'] = new JData;

		$runs = 0;

		foreach ($this->_instance as $k => $v)
		{
			$runs++;

			if ($k != 3)
			{
				unset($this->_instance[$k]);
			}
		}

		$this->assertFalse($this->_instance->offsetExists(1), 'Index 1 should have been unset.');
		$this->assertFalse($this->_instance->offsetExists(2), 'Index 2 should have been unset.');
		$this->assertTrue($this->_instance->offsetExists(3), 'Index 3 should be set.');
		$this->assertFalse($this->_instance->offsetExists(4), 'Index 4 should have been unset.');
		$this->assertFalse($this->_instance->offsetExists(5), 'Index 5 should have been unset.');
		$this->assertCount(1, $this->_instance);
		$this->assertEquals(5, $runs, 'Oops, the foreach ran too many times.');
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new JDataSet(
			array(
				new JDataBuran,
				new JDataVostok(array('mission' => 'Vostok 1', 'pilot' => 'Yuri Gagarin')),
			)
		);
	}
}
