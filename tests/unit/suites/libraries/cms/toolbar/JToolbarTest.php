<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JToolbar.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarTest extends TestCase
{
	/**
	 * @var    JToolbar
	 * @since  3.0
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
		$this->object = new JToolbar('toolbar');

		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the constructor
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function test__construct()
	{
		$this->assertThat(
			new JToolbar('toolbar'),
			$this->isInstanceOf('JToolbar')
		);
	}

	/**
	 * Tests the getInstance method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetInstance()
	{
		$this->object = JToolbar::getInstance('menu');

		$this->assertThat(
			$this->object,
			$this->isInstanceOf('JToolbar')
		);
	}

	/**
	 * Tests the appendButton method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testAppendButton()
	{
		$this->assertThat(
			$this->object->appendButton('Separator', 'divider'),
			$this->isTrue()
		);
	}

	/**
	 * Tests the getItems method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetItems()
	{
		$this->assertThat(
			$this->object->getItems(),
			$this->isType('array')
		);
	}

	/**
	 * Tests the getName method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetName()
	{
		$this->assertThat(
			$this->object->getName(),
			$this->equalTo('toolbar')
		);
	}

	/**
	 * Tests the prependButton method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testPrependButton()
	{
		$this->assertThat(
			$this->object->prependButton('Separator', 'spacer', 25),
			$this->isTrue()
		);
	}

	/**
	 * Tests the render method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testRender()
	{
		$toolbar = JToolbar::getInstance('toolbar');
		$toolbar->appendButton('Separator', 'spacer', 25);

		$this->assertThat(
			$toolbar->render(),
			$this->isType('string')
		);
	}

	public function testLoadButtonType()
	{
		$this->assertThat(
			$this->object->loadButtonType('Separator'),
			$this->isInstanceOf('JToolbarButtonSeparator')
		);
	}

	/**
	 * Tests the addButtonPath method with an array parameter
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testAddButtonPath_Array()
	{
		$initialValue = $this->readAttribute($this->object, '_buttonPath');
		$this->object->addButtonPath(array('MyTestPath1', 'MyTestPath2'));
		$newValue = $this->readAttribute($this->object, '_buttonPath');
		$this->assertThat(
			$newValue[0],
			$this->equalTo('MyTestPath2' . DIRECTORY_SEPARATOR)
		);

		$this->assertThat(
			$newValue[1],
			$this->equalTo('MyTestPath1' . DIRECTORY_SEPARATOR)
		);

		$initialCount = count($initialValue);

		for ($i = 0; $i < $initialCount; $i++)
		{
			$this->assertThat(
				$initialValue[$i],
				$this->equalTo($newValue[$i+2])
			);
		}
	}

	/**
	 * Tests the addButtonPath method with a string parameter
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testAddButtonPath_String()
	{
		$initialValue = $this->readAttribute($this->object, '_buttonPath');
		$this->object->addButtonPath('MyTestPath');
		$newValue = $this->readAttribute($this->object, '_buttonPath');
		$this->assertThat(
			$newValue[0],
			$this->equalTo('MyTestPath' . DIRECTORY_SEPARATOR)
		);

		$initialCount = count($initialValue);

		for ($i = 0; $i < $initialCount; $i++)
		{
			$this->assertThat(
				$initialValue[$i],
				$this->equalTo($newValue[$i+1])
			);
		}
	}
}
