<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Menu
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JMenu.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Menu
 * @since       3.4
 */
class JMenuTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JMenu
	 * @since  3.4
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		$this->object = new JMenu;

		TestReflection::setValue($this->object, '_items', TestMockMenu::create($this)->getMenu());
	}

	/**
	 * Tests the getItem method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::getItem
	 */
	public function testGetItem()
	{
		$this->assertNull($this->object->getItem(41), 'Menu item ID 41 should not exist');
		$this->assertNull($this->object->getItem('41'), 'If the integer does not exist, the string should not work as a key either');

		$item = $this->object->getItem(42);
		$this->assertInstanceOf('stdClass', $item);
		$this->assertEquals('Test1', $item->title);
	}

	/**
	 * Tests the setDefault method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::setDefault
	 */
	public function testSetDefault()
	{
		$this->assertEquals(array(), TestReflection::getValue($this->object, '_default'));
		$this->assertTrue($this->object->setDefault(42), 'Existing menu item should be able to be set as default');
		$this->assertEquals(array('*' => 42), TestReflection::getValue($this->object, '_default'));

		$this->assertFalse($this->object->setDefault(41), 'Non-existing menu item should not be able to be set as default');
		$this->assertEquals(array('*' => 42), TestReflection::getValue($this->object, '_default'));

		$this->assertTrue($this->object->setDefault(47, 'en-GB'));
		$this->assertEquals(array('*' => 42, 'en-GB' => 47), TestReflection::getValue($this->object, '_default'));
		$this->assertFalse($this->object->setDefault(41, 'en-GB'), 'Non-existing menu item should not be able to be set as default');
	}

	/**
	 * Tests the getDefault method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::getDefault
	 */
	public function testGetDefault()
	{
		$this->assertNull($this->object->getDefault());
		
		TestReflection::setValue($this->object, '_default', array('*' => 42, 'en-GB' => 47));

		$this->assertEquals('Test1', $this->object->getDefault()->title);
		$this->assertEquals('English Test', $this->object->getDefault('en-GB')->title);
		$this->assertEquals('Test1', $this->object->getDefault('fr-FR')->title, 'Non-existing languages should return * default item');
	}

	/**
	 * Tests the setActive method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::setActive
	 */
	public function testSetActive()
	{
		$this->assertEquals(0, TestReflection::getValue($this->object, '_active'));
		$this->assertEquals('Test1', $this->object->setActive(42)->title, 'Existing menu item should be able to be set as active');
		$this->assertEquals(42, TestReflection::getValue($this->object, '_active'));

		$this->assertNull($this->object->setActive(41), 'Non-existing menu item should not be able to be set as default');
		$this->assertEquals(42, TestReflection::getValue($this->object, '_active'));
	}

	/**
	 * Tests the getActive method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::getActive
	 */
	public function testGetActive()
	{
		$this->assertNull($this->object->getActive());
		
		TestReflection::setValue($this->object, '_active', 42);

		$this->assertEquals('Test1', $this->object->getActive()->title);
	}

	/**
	 * Tests the getMenu method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JMenu::getMenu
	 */
	public function testGetMenu()
	{
		$this->assertEquals(TestMockMenu::create($this)->getMenu(), $this->object->getMenu());
	}
}
