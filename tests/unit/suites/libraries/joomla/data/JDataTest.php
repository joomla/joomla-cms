<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Data
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

JLoader::register('JDataBuran', __DIR__ . '/stubs/buran.php');
JLoader::register('JDataCapitaliser', __DIR__ . '/stubs/capitaliser.php');

/**
 * Tests for the JData class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Data
 * @since       12.3
 */
class JDataTest extends TestCase
{
	/**
	 * @var    JData
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests the object constructor.
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		$instance = new JData(array('property1' => 'value1', 'property2' => 5));
		$this->assertThat(
			$instance->property1,
			$this->equalTo('value1')
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
		$this->assertNull(
			$this->_instance->foobar,
			'Unknown property should return null.'
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
		$this->assertFalse(isset($this->_instance->title), 'Unknown property');

		$this->_instance->bind(array('title' => true));

		$this->assertTrue(isset($this->_instance->title), 'Property is set.');
	}

	/**
	 * Tests the __set method where a custom setter is available.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function test__set_setter()
	{
		$instance = new JDataCapitaliser;

		// Set the property and assert that it is the expected value.
		$instance->test_value = 'one';
		$this->assertEquals('ONE', $instance->test_value);

		$instance->bind(array('test_value' => 'two'));
		$this->assertEquals('TWO', $instance->test_value);
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
		$this->_instance->bind(array('title' => true));

		$this->assertTrue(isset($this->_instance->title));

		unset($this->_instance->title);

		$this->assertFalse(isset($this->_instance->title));
	}

	/**
	 * Tests the bind method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testBind()
	{
		$properties = array('null' => null);

		$this->_instance->null = 'notNull';
		$this->_instance->bind($properties, false);
		$this->assertSame('notNull', $this->_instance->null, 'Checking binding without updating nulls works correctly.');

		$this->_instance->bind($properties);
		$this->assertSame(null, $this->_instance->null, 'Checking binding with updating nulls works correctly.');
	}

	/**
	 * Tests the bind method with array input.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testBind_array()
	{
		$properties = array(
			'property_1' => 'value_1',
			'property_2' => '1',
			'property_3' => 1,
			'property_4' => false,
			'property_5' => array('foo')
		);

		// Bind an array to the object.
		$this->_instance->bind($properties);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->_instance->$property);
		}
	}

	/**
	 * Tests the bind method with input that is a traverable object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testBind_arrayObject()
	{
		$properties = array(
			'property_1' => 'value_1',
			'property_2' => '1',
			'property_3' => 1,
			'property_4' => false,
			'property_5' => array('foo')
		);

		$traversable = new ArrayObject($properties);

		// Bind an array to the object.
		$this->_instance->bind($traversable);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->_instance->$property);
		}
	}

	/**
	 * Tests the bind method with object input.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testBind_object()
	{
		$properties = new stdClass;
		$properties->property_1 = 'value_1';
		$properties->property_2 = '1';
		$properties->property_3 = 1;
		$properties->property_4 = false;
		$properties->property_5 = array('foo');

		// Bind an array to the object.
		$this->_instance->bind($properties);

		// Assert that the values match.
		foreach ($properties as $property => $value)
		{
			$this->assertEquals($value, $this->_instance->$property);
		}
	}

	/**
	 * Tests the bind method for an expected exception.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testBind_exception()
	{
		$this->_instance->bind('foobar');
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
		// Tests the current object is empty.
		$this->assertCount(0, $this->_instance);

		// Set a complex property.
		$this->_instance->foo = array(1 => array(2));
		$this->assertCount(1, $this->_instance);

		// Set some more properties.
		$this->_instance->bar = 'bar';
		$this->_instance->barz = 'barz';
		$this->assertCount(3, $this->_instance);
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
		$dump = $this->_instance->dump();

		$this->assertEquals(
			'object',
			gettype($dump),
			'Dump should return an object.'
		);

		$this->assertEmpty(
			get_object_vars($dump),
			'Empty JData should give an empty dump.'
		);

		$properties = array(
			'scalar' => 'value_1',
			'date' => new JDate('2012-01-01'),
			'registry' => new Registry(array('key' => 'value')),
			'JData' => new JData(
				array(
					'level2' => new JData(
						array(
							'level3' => new JData(
								array(
									'level4' => new JData(
										array(
											'level5' => 'deep',
										)
									)
								)
							)
						)
					)
				)
			),
		);

		// Bind an array to the object.
		$this->_instance->bind($properties);

		// Dump the object (default is 3 levels).
		$dump = $this->_instance->dump();

		$this->assertEquals($dump->scalar, 'value_1');
		$this->assertEquals($dump->date, '2012-01-01 00:00:00');
		$this->assertEquals($dump->registry, (object) array('key' => 'value'));
		$this->assertInstanceOf('stdClass', $dump->JData->level2);
		$this->assertInstanceOf('stdClass', $dump->JData->level2->level3);
		$this->assertInstanceOf('JData', $dump->JData->level2->level3->level4);

		$dump = $this->_instance->dump(0);
		$this->assertInstanceOf('JDate', $dump->date);
		$this->assertInstanceOf('\\Joomla\\Registry\\Registry', $dump->registry);
		$this->assertInstanceOf('JData', $dump->JData);

		$dump = $this->_instance->dump(1);
		$this->assertEquals($dump->date, '2012-01-01 00:00:00');
		$this->assertEquals($dump->registry, (object) array('key' => 'value'));
		$this->assertInstanceOf('stdClass', $dump->JData);
		$this->assertInstanceOf('JData', $dump->JData->level2);
	}

	/**
	 * Tests the dumpProperty method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDumpProperty()
	{
		$dumped = new SplObjectStorage;

		$this->_instance->bind(array('dump_test' => 'dump_test_value'));
		$this->assertEquals(
			'dump_test_value',
			TestReflection::invoke($this->_instance, 'dumpProperty', 'dump_test', 3, $dumped)
		);
	}

	/**
	 * Tests the getIterator method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetIterator()
	{
		$this->assertInstanceOf('ArrayIterator', $this->_instance->getIterator());
	}

	/**
	 * Tests the getProperty method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetProperty()
	{
		$this->_instance->bind(array('get_test' => 'get_test_value'));
		$this->assertEquals('get_test_value', $this->_instance->get_test);
	}

	/**
	 * Tests the getProperty method.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testGetProperty_exception()
	{
		$this->_instance->bind(array('get_test' => 'get_test_value'));

		// Get the reflection property. This should throw an exception.
		TestReflection::getValue($this->_instance, 'get_test');
	}

	/**
	 * Tests the jsonSerialize method.
	 *
	 * Note, this is not completely backward compatible. Previous this would just return the class name.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testJsonSerialize()
	{
		$this->assertEquals('{}', json_encode($this->_instance->jsonSerialize()), 'Empty object.');

		$this->_instance->bind(array('title' => 'Simple Object'));
		$this->assertEquals('{"title":"Simple Object"}', json_encode($this->_instance->jsonSerialize()), 'Simple object.');
	}

	/**
	 * Tests the setProperty method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetProperty()
	{
		$this->_instance->set_test = 'set_test_value';
		$this->assertEquals('set_test_value', $this->_instance->set_test);

		$object = new JDataCapitaliser;
		$object->test_value = 'upperCase';

		$this->assertEquals('UPPERCASE', $object->test_value);
	}

	/**
	 * Tests the setProperty method.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testSetProperty_exception()
	{
		// Get the reflection property. This should throw an exception.
		TestReflection::getValue($this->_instance, 'set_test');
	}

	/**
	 * Test that JData::setProperty() will not set a property which starts with a null byte.
	 *
	 * @return  void
	 *
	 * @see     http://us3.php.net/manual/en/language.types.array.php#language.types.array.casting
	 * @since   12.3
	 */
	public function testSetPropertySkipsPropertyWithNullBytes()
	{
		// Create a property that starts with a null byte.
		$property = "\0foo";

		// Attempt to set the property.
		$this->_instance->$property = 'bar';

		// The property should not be set.
		$this->assertNull($this->_instance->$property);
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

		$this->_instance = new JData;
	}
}
