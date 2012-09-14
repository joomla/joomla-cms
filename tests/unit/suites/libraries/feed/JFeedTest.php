<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeed.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       3.0
 */
class JFeedTest extends TestCase
{
	/**
	 * @var  JFeed
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JFeed;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.0
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Tests the JFeed::__get method when the property has been set to a value.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__get
	 */
	public function testMagicGetSet()
	{
		$properties = TestReflection::getValue($this->object, 'properties');

		$properties['testValue'] = 'test';

		TestReflection::setValue($this->object, 'properties', $properties);

		$this->assertEquals('test', $this->object->testValue);
	}

	/**
	 * Tests the JFeed::__get method when the property has not been set to a value.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__get
	 */
	public function testMagicGetNull()
	{
		$this->assertEquals(null, $this->object->testValue);
	}

	/**
	 * Tests the JFeed::__set method with updatedDate with a string.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetUpdatedDateString()
	{
		$this->object->updatedDate = 'May 2nd, 1967';

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with updatedDate with a JDate object.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetUpdatedDateJDateObject()
	{
		$date = new JDate('October 12, 2011');
		$this->object->updatedDate = $date;

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
		$this->assertTrue($date === $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with a person object.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetAuthorWithPerson()
	{
		$person = new JFeedPerson('Brian Kernighan', 'brian@example.com');

		$this->object->author = $person;

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertTrue($person === $properties['author']);
	}

	/**
	 * Tests the JFeed::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 * @expectedException  InvalidArgumentException
	 */
	public function testMagicSetAuthorWithInvalidAuthor()
	{
		$this->object->author = 'Jack Sprat';
	}

	/**
	 * Tests the JFeed::__set method with a disallowed property.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 * @expectedException  InvalidArgumentException
	 */
	public function testMagicSetCategoriesWithInvalidProperty()
	{
		$this->object->categories = 'Can\'t touch this';
	}

	/**
	 * Tests the JFeed::__set method with a typical property.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetGeneral()
	{
		$this->object->testValue = 'test';

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertEquals($properties['testValue'], 'test');
	}

	/**
	 * Tests the JFeed::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::addCategory
	 */
	public function testAddCategory()
	{
		$this->object->addCategory('category1', 'http://www.example.com');

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertEquals('http://www.example.com', $properties['categories']['category1']);
	}

	/**
	 * Tests the JFeed::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::addContributor
	 */
	public function testAddContributor()
	{
		$this->object->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = TestReflection::getValue($this->object, 'properties');

		// Make sure the contributor we added actually exists.
		$this->assertTrue(in_array(new JFeedPerson('Dennis Ritchie', 'dennis.ritchie@example.com'), $properties['contributors']
			)
		);

		$this->object->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = TestReflection::getValue($this->object, 'properties');

		// Make sure we aren't adding the same contributor more than once.
		$this->assertTrue(count($properties['contributors']) == 1);
	}

	/**
	 * Tests the JFeed::addEntry method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::addEntry
	 */
	public function testAddEntry()
	{
		$entry = new JFeedEntry;

		$this->object->addEntry($entry);

		$entries = TestReflection::getValue($this->object, 'entries');

		$this->assertEquals($entry, $entries[0]);

		$this->object->addEntry($entry);

		$entries = TestReflection::getValue($this->object, 'entries');

		// Make sure we aren't adding the same entry more than once.
		$this->assertTrue(count($entries) == 1);
	}

	/**
	 * Tests the JFeed::offsetExists method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::offsetExists
	 */
	public function testOffsetExists()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')->disableOriginalConstructor()->getMock();

		$mock->expects($this->once())->method('offsetExists')->will($this->returnValue(true));

		TestReflection::setValue($this->object, 'entries', $mock);

		$this->assertTrue($this->object->offsetExists($offset));
	}

	/**
	 * Tests the JFeed::offsetGet method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::offsetGet
	 */
	public function testOffsetGet()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')->disableOriginalConstructor()->getMock();

		$mock->expects($this->once())->method('offsetGet')->will($this->returnValue(true));

		TestReflection::setValue($this->object, 'entries', $mock);

		$this->assertTrue($mock->offsetGet($offset));
	}

	/**
	 * Tests the JFeed::offsetSet method with a string as a value -- invalid.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::offsetSet
	 * @expectedException  InvalidArgumentException
	 */
	public function testOffsetSetWithString()
	{
		$this->object->offsetSet(1, 'My string');
	}

	/**
	 * Tests the JFeed::offsetSet method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::offsetSet
	 */
	public function testOffsetSet()
	{
		$expected = new JFeedEntry;

		$this->object->offsetSet(1, $expected);

		$entries = TestReflection::getValue($this->object, 'entries');

		$this->assertSame($expected, $entries[1]);
	}

	/**
	 * Tests the JFeed::offsetUnset method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::offsetUnset
	 */
	public function testOffsetUnset()
	{
		$expected = new JFeedEntry;

		$entries = array(10 => $expected);

		TestReflection::setValue($this->object, 'entries', $entries);

		$this->object->offsetUnset(10);

		$entries = TestReflection::getValue($this->object, 'entries');

		$this->assertFalse(in_array($expected, $entries));
	}

	/**
	 * Tests the JFeed::removeCategory method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::removeCategory
	 */
	public function testRemoveCategory()
	{
		$properties = array();

		$properties['categories'] = array('category1', 'http://www.example.com');

		TestReflection::setValue($this->object, 'properties', $properties);

		$this->object->removeCategory('category1');

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertFalse(isset($properties['categories']['category1']));
	}

	/**
	 * Tests the JFeed::removeContributor method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::removeContributor
	 */
	public function testRemoveContributor()
	{
		$person = new JFeedPerson;

		$properties = array();
		$properties['contributors'] = array(1 => $person);

		TestReflection::setValue($this->object, 'properties', $properties);

		$this->object->removeContributor($person);

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertFalse(in_array($person, $properties['contributors']));

		$this->assertInstanceOf('JFeed', $this->object->removeContributor($person));
	}

	/**
	 * Tests the JFeed::removeEntry method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::removeEntry
	 */
	public function testRemoveEntry()
	{
		$expected = new JFeedEntry;

		$entries = array(1 => $expected);

		TestReflection::setValue($this->object, 'entries', $entries);

		$this->object->removeEntry($expected);

		$entries = TestReflection::getValue($this->object, 'entries');

		$this->assertFalse(in_array($expected, $entries));

		$this->assertInstanceOf('JFeed', $this->object->removeEntry($expected));
	}

	/**
	 * Tests the JFeed::setAuthor method.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @covers  JFeed::setAuthor
	 */
	public function testSetAuthor()
	{
		$this->object->setAuthor('Sir John A. Macdonald', 'john.macdonald@example.com');

		$properties = TestReflection::getValue($this->object, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertEquals('Sir John A. Macdonald', $properties['author']->name);
		$this->assertEquals('john.macdonald@example.com', $properties['author']->email);
	}
}
