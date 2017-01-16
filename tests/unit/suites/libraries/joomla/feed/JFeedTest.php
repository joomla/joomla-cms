<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeed.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedTest extends TestCase
{
	/**
	 * @var    JFeed
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests the JFeed::__get method when the property has been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicGetSet()
	{
		$properties = TestReflection::getValue($this->_instance, 'properties');

		$properties['testValue'] = 'test';

		TestReflection::setValue($this->_instance, 'properties', $properties);

		$this->assertEquals('test', $this->_instance->testValue);
	}

	/**
	 * Tests the JFeed::__get method when the property has not been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicGetNull()
	{
		$this->assertEquals(null, $this->_instance->testValue);
	}

	/**
	 * Tests the JFeed::__set method with updatedDate with a string.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetUpdatedDateString()
	{
		$this->_instance->updatedDate = 'May 2nd, 1967';

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with updatedDate with a JDate object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetUpdatedDateJDateObject()
	{
		$date = new JDate('October 12, 2011');
		$this->_instance->updatedDate = $date;

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
		$this->assertSame($date, $properties['updatedDate']);
	}

	/**
	 * Tests the JFeed::__set method with a person object.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetAuthorWithPerson()
	{
		$person = new JFeedPerson('Brian Kernighan', 'brian@example.com');

		$this->_instance->author = $person;

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertSame($person, $properties['author']);
	}

	/**
	 * Tests the JFeed::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testMagicSetAuthorWithInvalidAuthor()
	{
		$this->_instance->author = 'Jack Sprat';
	}

	/**
	 * Tests the JFeed::__set method with a disallowed property.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testMagicSetCategoriesWithInvalidProperty()
	{
		$this->_instance->categories = 'Can\'t touch this';
	}

	/**
	 * Tests the JFeed::__set method with a typical property.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testMagicSetGeneral()
	{
		$this->_instance->testValue = 'test';

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertEquals($properties['testValue'], 'test');
	}

	/**
	 * Tests the JFeed::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddCategory()
	{
		$this->_instance->addCategory('category1', 'http://www.example.com');

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertEquals('http://www.example.com', $properties['categories']['category1']);
	}

	/**
	 * Tests the JFeed::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddContributor()
	{
		$this->_instance->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = TestReflection::getValue($this->_instance, 'properties');

		// Make sure the contributor we added actually exists.
		$this->assertTrue(
			in_array(
				new JFeedPerson('Dennis Ritchie', 'dennis.ritchie@example.com'),
				$properties['contributors']
			)
		);

		$this->_instance->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = TestReflection::getValue($this->_instance, 'properties');

		// Make sure we aren't adding the same contributor more than once.
		$this->assertCount(1, $properties['contributors']);
	}

	/**
	 * Tests the JFeed::addEntry method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddEntry()
	{
		$entry = new JFeedEntry;

		$this->_instance->addEntry($entry);

		$entries = TestReflection::getValue($this->_instance, 'entries');

		$this->assertEquals(
			$entry,
			$entries[0]
		);

		$this->_instance->addEntry($entry);

		$entries = TestReflection::getValue($this->_instance, 'entries');

		// Make sure we aren't adding the same entry more than once.
		$this->assertCount(1, $entries);
	}

	/**
	 * Tests the JFeed::offsetExists method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetExists()
	{
		if (PHP_VERSION == '5.4.29' || PHP_VERSION == '5.5.13' || PHP_MINOR_VERSION == '6')
		{
			$this->markTestSkipped('Test is skipped due to a PHP bug in versions 5.4.29 and 5.5.13 and a change in behavior in the 5.6 branch');
		}

		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
			->disableOriginalConstructor()
			->getMock();

		$mock->expects($this->once())
			->method('offsetExists')
			->will($this->returnValue(true));

		TestReflection::setValue($this->_instance, 'entries', $mock);

		$this->assertTrue($this->_instance->offsetExists($offset));
	}

	/**
	 * Tests the JFeed::offsetGet method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetGet()
	{
		if (PHP_VERSION == '5.4.29' || PHP_VERSION == '5.5.13' || PHP_MINOR_VERSION == '6')
		{
			$this->markTestSkipped('Test is skipped due to a PHP bug in versions 5.4.29 and 5.5.13 and a change in behavior in the 5.6 branch');
		}

		$offset = new stdClass;

		$mock = $this->getMockBuilder('SplObjectStorage')
			->disableOriginalConstructor()
			->getMock();

		$mock->expects($this->once())
			->method('offsetGet')
			->will($this->returnValue(true));

		TestReflection::setValue($this->_instance, 'entries', $mock);

		$this->assertTrue($this->_instance->offsetGet($offset));
	}

	/**
	 * Tests the JFeed::offsetSet method with a string as a value -- invalid.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testOffsetSetWithString()
	{
		$this->_instance->offsetSet(1, 'My string');
	}

	/**
	 * Tests the JFeed::offsetSet method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetSet()
	{
		$expected = new JFeedEntry;

		$this->_instance->offsetSet(1, $expected);

		$entries = TestReflection::getValue($this->_instance, 'entries');

		$this->assertSame(
			$expected,
			$entries[1]
		);
	}

	/**
	 * Tests the JFeed::offsetUnset method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testOffsetUnset()
	{
		$expected = new JFeedEntry;

		$entries = array(10 => $expected);

		TestReflection::setValue($this->_instance, 'entries', $entries);

		$this->_instance->offsetUnset(10);

		$entries = TestReflection::getValue($this->_instance, 'entries');

		$this->assertFalse(in_array($expected, $entries));
	}

	/**
	 * Tests the JFeed::removeCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveCategory()
	{
		$properties = array();

		$properties['categories'] = array('category1', 'http://www.example.com');

		TestReflection::setValue($this->_instance, 'properties', $properties);

		$this->_instance->removeCategory('category1');

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertFalse(isset($properties['categories']['category1']));
	}

	/**
	 * Tests the JFeed::removeContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveContributor()
	{
		$person = new JFeedPerson;

		$properties = array();
		$properties['contributors'] = array(1 => $person);

		TestReflection::setValue($this->_instance, 'properties', $properties);

		$this->_instance->removeContributor($person);

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertFalse(in_array($person, $properties['contributors']));

		$this->assertInstanceOf('JFeed', $this->_instance->removeContributor($person));
	}

	/**
	 * Tests the JFeed::removeEntry method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveEntry()
	{
		$expected = new JFeedEntry;

		$entries = array(1 => $expected);

		TestReflection::setValue($this->_instance, 'entries', $entries);

		$this->_instance->removeEntry($expected);

		$entries = TestReflection::getValue($this->_instance, 'entries');

		$this->assertFalse(in_array($expected, $entries));

		$this->assertInstanceOf('JFeed', $this->_instance->removeEntry($expected));
	}

	/**
	 * Tests the JFeed::setAuthor method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetAuthor()
	{
		$this->_instance->setAuthor('Sir John A. Macdonald', 'john.macdonald@example.com');

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertInstanceOf('JFeedPerson', $properties['author']);
		$this->assertEquals('Sir John A. Macdonald', $properties['author']->name);
		$this->assertEquals('john.macdonald@example.com', $properties['author']->email);
	}

	/**
	 * Setup the tests.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::setUp()
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new JFeed;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   12.3
	 */
	protected function tearDown()
	{
		unset($this->_instance);

		parent::tearDown();
	}
}
