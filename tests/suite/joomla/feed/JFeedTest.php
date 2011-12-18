<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/feed/feed.php';

/**
 * Test class for JFeed.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedTest extends JoomlaTestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   12.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new JFeed();
	}

	/**
	 * Tear down any fixtures.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   12.1
	 */
	protected function tearDown()
	{
		parent::tearDown();
	}

	/**
	 * Tests the JFeed::__construct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::__construct
	 */
	public function testConstructor()
	{
		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertTrue(ReflectionHelper::getValue($this->object, 'entries') instanceof SplObjectStorage);
		$this->assertTrue($properties['contributors'] instanceof SplObjectStorage);
	}

	/**
	 * Tests the JFeed::__get method when the property has been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::__get
	 */
	public function testMagicGetSet()
	{
		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$properties['testValue'] = 'test';

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$this->assertEquals('test', $this->object->testValue);
	}

	/**
	 * Tests the JFeed::__get method when the property has not been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.1
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
	 * @since   12.1
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetUpdatedDateString()
	{
		$this->object->updatedDate = 'May 2nd, 1967';

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with updatedDate with a JDate object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetUpdatedDateJDateObject()
	{
		$date = new JDate('October 12, 2011');
		$this->object->updatedDate = $date;

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
		$this->assertTrue($date === $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with a person object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetAuthorWithPerson()
	{
		$person = new JFeedPerson('Brian Kernighan', 'brian@example.com');

		$this->object->author = $person;

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertTrue($person === $properties['author']);
	}

	/**
	 * Tests the JFeed::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @covers  JFeed::__set
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
	 * @since   12.1
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetAuthorWithInvalidProperty()
	{
		$this->object->categories = 'Can\' touch this';
	}

	/**
	 * Tests the JFeed::__set method with a typical property.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::__set
	 */
	public function testMagicSetGeneral()
	{
		$this->object->testValue = 'test';

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertEquals($properties['testValue'], 'test');
	}

	/**
	 * Tests the JFeed::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::addCategory
	 */
	public function testAddCategory()
	{
		$this->object->addCategory('category1', 'http://www.example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertEquals('http://www.example.com', $properties['categories']['category1']);
	}

	/**
	 * Tests the JFeed::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::addContributor
	 */
	public function testAddContributor()
	{
		$this->object->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$properties['contributors']->rewind();

		$this->assertEquals('Dennis Ritchie', $properties['contributors']->current()->name);
	}

	/**
	 * Tests the JFeed::addEntry method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::addEntry
	 */
	public function testAddEntry()
	{
		$entry = new JFeedEntry;

		$this->object->addEntry($entry);

		$entries = ReflectionHelper::getValue($this->object, 'entries');

		$this->assertTrue($entries->contains($entry));
	}

	/**
	 * Tests the JFeed::offsetExists method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::offsetExists
	 */
	public function testOffsetExists()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$mock->expects($this->once())
			 ->method('offsetExists')
			 ->will($this->returnValue(true));

		ReflectionHelper::setValue($this->object, 'entries', $mock);

		$this->assertTrue($this->object->offsetExists($offset));
	}

	/**
	 * Tests the JFeed::offsetGet method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::offsetGet
	 */
	public function testOffsetGet()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$mock->expects($this->once())
			 ->method('offsetGet')
			 ->will($this->returnValue(true));

		ReflectionHelper::setValue($this->object, 'entries', $mock);

		$this->assertTrue($this->object->offsetGet($offset));
	}

	/**
	 * Tests the JFeed::offsetSet method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::offsetSet
	 */
	public function testOffsetSet()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$mock->expects($this->once())
			 ->method('offsetSet')
			 ->with($offset, 'My value')
			 ->will($this->returnValue(true));

		ReflectionHelper::setValue($this->object, 'entries', $mock);

		$this->assertTrue($this->object->offsetSet($offset, 'My value'));
	}

	/**
	 * Tests the JFeed::offsetUnset method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::offsetUnset
	 */
	public function testOffsetUnset()
	{
		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$mock->expects($this->once())
			 ->method('offsetUnset')
			 ->with($offset)
			 ->will($this->returnValue(true));

		ReflectionHelper::setValue($this->object, 'entries', $mock);

		$this->assertTrue($this->object->offsetUnset($offset));
	}

	/**
	 * Tests the JFeed::removeCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::removeCategory
	 */
	public function testRemoveCategory()
	{
		$properties = array();

		$properties['categories'] = array('category1', 'http://www.example.com');

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$this->object->removeCategory('category1');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertFalse(isset($properties['categories']['category1']));
	}

	/**
	 * Tests the JFeed::removeContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::removeContributor
	 */
	public function testRemoveContributor()
	{
		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$person = new JFeedPerson;

		$properties = array();
		$properties['contributors'] = $mock;

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$mock->expects($this->once())
			 ->method('detach')
			 ->with($person);

		$this->object->removeContributor($person);
	}

	/**
	 * Tests the JFeed::removeEntry method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::removeEntry
	 */
	public function testRemoveEntry()
	{
		$mock = $this->getMockBuilder('SplObjectStorage')
					 ->disableOriginalConstructor()
					 ->getMock();

		$entry = new JFeedEntry;

		ReflectionHelper::setValue($this->object, 'entries', $mock);

		$mock->expects($this->once())
			 ->method('detach')
			 ->with($entry);

		$this->object->removeEntry($entry);
	}

	/**
	 * Tests the JFeed::setAuthor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeed::setAuthor
	 */
	public function testSetAuthor()
	{
		$this->object->setAuthor('Sir John A. Macdonald', 'john.macdonald@example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertEquals('Sir John A. Macdonald', $properties['author']->name);
		$this->assertEquals('john.macdonald@example.com', $properties['author']->email);
	}
}
