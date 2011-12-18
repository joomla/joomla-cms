<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JFeedEntry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Feed
 * @since       12.1
 */
class JFeedEntryTest extends JoomlaTestCase
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

		$this->object = new JFeedEntry(/* parameters */);
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
		$this->object = null;

		parent::tearDown();
	}

	/**
	 * Tests the JFeedEntry::__get method when the property has been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__get
	 */
	public function testMagicGetSet()
	{
		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$properties['testValue'] = 'test';

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$this->assertEquals('test', $this->object->testValue);
	}

	/**
	 * Tests the JFeedEntry::__get method when the property has not been set to a value.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__get
	 */
	public function testMagicGetNull()
	{
		$this->assertEquals(null, $this->object->testValue);
	}

	/**
	 * Tests the JFeedEntry::__set method with updatedDate with a string.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__set
	 */
	public function testMagicSetUpdatedDateString()
	{
		$this->object->updatedDate = 'May 2nd, 1967';

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertInstanceOf('JDate', $properties['updatedDate']);
	}

	/**
	 * Tests the JFeedEntry::__set method with updatedDate with a JDate object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__set
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
	 * Tests the JFeedEntry::__set method with a person object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__set
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
	 * Tests the JFeedEntry::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @covers  JFeedEntry::__set
	 */
	public function testMagicSetAuthorWithInvalidAuthor()
	{
		$this->object->author = 'Jack Sprat';
	}

	/**
	 * Tests the JFeedEntry::__set method with an invalid argument for author.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @covers  JFeedEntry::__set
	 */
	public function testMagicSetSourceWithInvalidSource()
	{
		$this->object->source = 'Outer Space';
	}

	/**
	 * Tests the JFeedEntry::__set method with a disallowed property.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @expectedException  InvalidArgumentException
	 *
	 * @covers  JFeedEntry::__set
	 */
	public function testMagicSetCategoriesWithInvalidProperty()
	{
		$this->object->categories = 'Can\'t touch this';
	}

	/**
	 * Tests the JFeedEntry::__set method with a typical property.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::__set
	 */
	public function testMagicSetGeneral()
	{
		$this->object->testValue = 'test';

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertEquals($properties['testValue'], 'test');
	}

	/**
	 * Tests the JFeedEntry::addCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::addCategory
	 */
	public function testAddCategory()
	{
		$this->object->addCategory('category1', 'http://www.example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertEquals('http://www.example.com', $properties['categories']['category1']);
	}

	/**
	 * Tests the JFeedEntry::addContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::addContributor
	 */
	public function testAddContributor()
	{
		$this->object->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		// Make sure the contributor we added actually exists.
		$this->assertTrue(in_array(
			new JFeedPerson('Dennis Ritchie', 'dennis.ritchie@example.com'),
			$properties['contributors']
		));

		$this->object->addContributor('Dennis Ritchie', 'dennis.ritchie@example.com');

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		// Make sure we aren't adding the same contributor more than once.
		$this->assertTrue(count($properties['contributors']) == 1);
	}

	/**
	 * Tests JFeedEntry->addLink()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testAddLink()
	{
		$expected = new JFeedLink('http://domain.com/path/to/resource');
		$this->object->addLink($expected);

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		// Make sure the link we added actually exists.
		$this->assertTrue(in_array(
			$expected,
			$properties['links']
		));

		$this->object->addLink($expected);

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		// Make sure we aren't adding the same link more than once.
		$this->assertTrue(count($properties['links']) == 1);
	}

	/**
	 * Tests the JFeedEntry::removeCategory method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::removeCategory
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
	 * Tests the JFeedEntry::removeContributor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::removeContributor
	 */
	public function testRemoveContributor()
	{
		$person = new JFeedPerson;

		$properties = array();
		$properties['contributors'] = array(1 => $person);

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$this->object->removeContributor($person);

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertFalse(in_array($person, $properties['contributors']));

		$this->assertInstanceOf('JFeedEntry', $this->object->removeContributor($person));
	}

	/**
	 * Tests JFeedEntry->removeLink()
	 *
	 * @return void
	 *
	 * @since 12.1
	 */
	public function testRemoveLink()
	{
		$link = new JFeedLink;

		$properties = array();
		$properties['links'] = array(1 => $link);

		ReflectionHelper::setValue($this->object, 'properties', $properties);

		$this->object->removeLink($link);

		$properties = ReflectionHelper::getValue($this->object, 'properties');

		$this->assertFalse(in_array($link, $properties['links']));

		$this->assertInstanceOf('JFeedEntry', $this->object->removeLink($link));
	}

	/**
	 * Tests the JFeedEntry::setAuthor method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 *
	 * @covers  JFeedEntry::setAuthor
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
