<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.3
 */
class JFeedEntryTest extends TestCase
{
	/**
	 * @var    JFeedEntry
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Tests the JFeedEntry::__get method when the property has been set to a value.
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
	 * Tests the JFeedEntry::__get method when the property has not been set to a value.
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
	 * Tests the JFeedEntry::__set method with updatedDate with a string.
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
	 * Tests the JFeedEntry::__set method with updatedDate with a JDate object.
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
	 * Tests the JFeedEntry::__set method with a person object.
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
	 * Tests the JFeedEntry::__set method with an invalid argument for author.
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
	 * Tests the JFeedEntry::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @expectedException  InvalidArgumentException
	 * @since              12.3
	 */
	public function testMagicSetSourceWithInvalidSource()
	{
		$this->_instance->source = 'Outer Space';
	}

	/**
	 * Tests the JFeedEntry::__set method with a disallowed property.
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
	 * Tests the JFeedEntry::__set method with a typical property.
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
	 * Tests the JFeedEntry::addCategory method.
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
	 * Tests the JFeedEntry::addContributor method.
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
	 * Tests JFeedEntry->addLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testAddLink()
	{
		$expected = new JFeedLink('http://domain.com/path/to/resource');
		$this->_instance->addLink($expected);

		$properties = TestReflection::getValue($this->_instance, 'properties');

		// Make sure the link we added actually exists.
		$this->assertTrue(
			in_array(
				$expected,
				$properties['links']
			)
		);

		$this->_instance->addLink($expected);

		$properties = TestReflection::getValue($this->_instance, 'properties');

		// Make sure we aren't adding the same link more than once.
		$this->assertCount(1, $properties['links']);
	}

	/**
	 * Tests the JFeedEntry::removeCategory method.
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
	 * Tests the JFeedEntry::removeContributor method.
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

		$this->assertInstanceOf('JFeedEntry', $this->_instance->removeContributor($person));
	}

	/**
	 * Tests JFeedEntry->removeLink()
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testRemoveLink()
	{
		$link = new JFeedLink;

		$properties = array();
		$properties['links'] = array(1 => $link);

		TestReflection::setValue($this->_instance, 'properties', $properties);

		$this->_instance->removeLink($link);

		$properties = TestReflection::getValue($this->_instance, 'properties');

		$this->assertFalse(in_array($link, $properties['links']));

		$this->assertInstanceOf('JFeedEntry', $this->_instance->removeLink($link));
	}

	/**
	 * Tests the JFeedEntry::setAuthor method.
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
	 * @see     \PHPUnit\Framework\TestCase::setUp()
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->_instance = new JFeedEntry;
	}

	/**
	 * Method to tear down whatever was set up before the test.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   12.3
	 */
	protected function tearDown()
	{
		unset($this->_instance);

		parent::tearDown();
	}
}
