<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Data
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('JDataBuran', __DIR__ . '/stubs/buran.php');
JLoader::register('JDataVostok', __DIR__ . '/stubs/vostok.php');

/**
 * Tests for the JContentHelperTest class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Data
 * @since       12.3
 */
class JDataSetTest extends TestCase
{
	/**
	 * @var    JDataSet
	 * @since  12.3
	 */
	protected $class;

	/**
	 * Tests the __construct method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__construct
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
	 * @covers             JDataSet::__construct
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function test__construct_array()
	{
		new JDataSet(array('foo'));
	}

	/**
	 * Tests the __construct method with scalar input.
	 *
	 * @return  void
	 *
	 * @covers             JDataSet::__construct
	 * @expectedException  PHPUnit_Framework_Error
	 * @since              12.3
	 */
	public function test__construct_scalar()
	{
		new JDataSet('foo');
	}

	/**
	 * Tests the __call method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__call
	 * @since   12.3
	 */
	public function test__call()
	{
		$this->assertThat(
			$this->class->launch('go'),
			$this->equalTo(array(1 => 'go'))
		);
	}

	/**
	 * Tests the __get method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__get
	 * @since   12.3
	 */
	public function test__get()
	{
		$this->assertThat(
			$this->class->pilot,
			$this->equalTo(array(0 => null, 1 => 'Yuri Gagarin'))
		);
	}

	/**
	 * Tests the __isset method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__isset
	 * @since   12.3
	 */
	public function test__isset()
	{
		$this->assertTrue(isset($this->class->pilot), 'Property exists.');

		$this->assertFalse(isset($this->class->duration), 'Unknown property');
	}

	/**
	 * Tests the __set method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__set
	 * @since   12.3
	 */
	public function test__set()
	{
		$this->class->successful = 'yes';

		$this->assertThat(
			$this->class->successful,
			$this->equalTo(array(0 => 'yes', 1 => 'YES'))
		);
	}

	/**
	 * Tests the __unset method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::__unset
	 * @since   12.3
	 */
	public function test__unset()
	{
		unset($this->class->pilot);

		$this->assertNull($this->class[1]->pilot);
	}

	/**
	 * Tests the count method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::count
	 * @since   12.3
	 */
	public function testCount()
	{
		$this->assertThat(
			count($this->class),
			$this->equalTo(2)
		);
	}

	/**
	 * Tests the current method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::current
	 * @since   12.3
	 */
	public function testCurrent()
	{
		$object = $this->class[0];

		$this->assertThat(
			$this->class->current(),
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
	 * @covers  JDataSet::dump
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
			$this->class->dump()
		);
	}

	/**
	 * Tests the jsonSerialize method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::jsonSerialize
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
			$this->class->jsonSerialize()
		);
	}

	/**
	 * Tests the key method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::key
	 * @since   12.3
	 */
	public function testKey()
	{
		$this->assertThat(
			$this->class->key(),
			$this->equalTo(0)
		);
	}

	/**
	 * Tests the next method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::next
	 * @since   12.3
	 */
	public function testNext()
	{
		$this->class->next();
		$this->assertThat(
			TestReflection::getValue($this->class, '_current'),
			$this->equalTo(1)
		);

		$this->class->next();
		$this->assertThat(
			TestReflection::getValue($this->class, '_current'),
			$this->equalTo(false)
		);
	}

	/**
	 * Tests the offsetExists method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::offsetExists
	 * @since   12.3
	 */
	public function testOffsetExists()
	{
		$this->assertTrue($this->class->offsetExists(0));
		$this->assertFalse($this->class->offsetExists(2));
		$this->assertFalse($this->class->offsetExists('foo'));
	}

	/**
	 * Tests the offsetGet method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::offsetGet
	 * @since   12.3
	 */
	public function testOffsetGet()
	{
		$this->assertInstanceOf('JDataBuran', $this->class->offsetGet(0));
		$this->assertInstanceOf('JDataVostok', $this->class->offsetGet(1));
		$this->assertNull($this->class->offsetGet('foo'));
	}

	/**
	 * Tests the offsetSet method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::OffsetSet
	 * @since   12.3
	 */
	public function testOffsetSet()
	{
		$this->class->offsetSet(0, new JData);
		$objects = TestReflection::getValue($this->class, '_objects');

		$this->assertEquals(new JData, $objects[0], 'Checks explicit use of offsetSet.');

		$this->class[] = new JData;
		$this->assertInstanceOf('JData', $this->class[1], 'Checks the array push equivalent with [].');

		$this->class['foo'] = new JData;
		$this->assertInstanceOf('JData', $this->class['foo'], 'Checks implicit usage of offsetSet.');
	}

	/**
	 * Tests the offsetSet method for an expected exception
	 *
	 * @return  void
	 *
	 * @covers             JDataSet::OffsetSet
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testOffsetSet_exception1()
	{
		// By implication, this will call offsetSet.
		$this->class['foo'] = 'bar';
	}

	/**
	 * Tests the offsetUnset method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::OffsetUnset
	 * @since   12.3
	 */
	public function testOffsetUnset()
	{
		$this->class->offsetUnset(0);
		$objects = TestReflection::getValue($this->class, '_objects');

		$this->assertFalse(isset($objects[0]));
	}

	/**
	 * Tests the offsetRewind method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::rewind
	 * @since   12.3
	 */
	public function testOffsetRewind()
	{
		TestReflection::setValue($this->class, '_current', 'foo');

		$this->class->rewind();

		$this->assertThat(
			$this->class->key(),
			$this->equalTo(0)
		);
	}

	/**
	 * Tests the valid method.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::valid
	 * @since   12.3
	 */
	public function testValid()
	{
		$this->assertTrue($this->class->valid());

		TestReflection::setValue($this->class, '_current', null);

		$this->assertFalse($this->class->valid());
	}

	/**
	 * Test that JDataSet::_initialise method indirectly.
	 *
	 * @return  void
	 *
	 * @covers  JDataSet::_initialise
	 * @since   12.3
	 */
	public function test_initialise()
	{
		$this->assertInstanceOf('JDataBuran', $this->class[0]);
		$this->assertInstanceOf('JDataVostok', $this->class[1]);
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
		$tests = array();

		foreach ($this->class as $key => $object)
		{
			$tests[] = $object->mission;
		}

		$this->assertThat(
			$tests,
			$this->equalTo(array(null, 'Vostok 1')),
			'Tests multi-item list.'
		);

		$runs = 0;
		$list = new JDataSet(
			array(
				'foo' => new JData,
			)
		);

		$this->assertTrue($list->offsetExists('foo'));

		foreach ($list as $key => $object)
		{
			$runs++;
		}

		$this->assertThat(
			$runs,
			$this->equalTo(1),
			'Tests single item list.'
		);
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @since  12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->class = new JDataSet(
			array(
				new JDataBuran,
				new JDataVostok(array('mission' => 'Vostok 1', 'pilot' => 'Yuri Gagarin')),
			)
		);
	}
}
