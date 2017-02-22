<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Pathway
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JPathway.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pathway
 * @since       3.1
 */
class JPathwayTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JPathway
	 * @since  3.1
	 */
	protected $fixture;

	/**
	 * Set up the tests
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		$this->fixture = new JPathway;

		parent::setUp();
	}

	/**
	 * Tear down the tests
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		unset($this->fixture);

		parent::tearDown();
	}

	/**
	 * Test JPathway::__construct().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function test__construct()
	{
		$this->assertAttributeEquals(array(), '_pathway', $this->fixture);
	}

	/**
	 * Test JPathway::getInstance().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetInstance()
	{
		$current = TestReflection::getValue('JApplicationHelper', '_clients');

		// Test Client
		$obj = new stdClass;
		$obj->id = 0;
		$obj->name = 'inspector';
		$obj->path = JPATH_TESTS;

		$obj2 = new stdClass;
		$obj2->id = 1;
		$obj2->name = 'inspector2';
		$obj2->path = __DIR__ . '/stubs';

		TestReflection::setValue('JApplicationHelper', '_clients', array($obj, $obj2));

		$pathway = JPathway::getInstance('');

		$this->assertInstanceOf('JPathway', $pathway);

		$pathway = JPathway::getInstance('Inspector2');

		$this->assertInstanceOf('JPathwayInspector2', $pathway);

		$ret = true;

		try
		{
			JPathway::getInstance('Error');
		}
		catch (Exception $e)
		{
			$ret = false;
		}

		if ($ret)
		{
			$this->fail('JPathway did not throw a proper exception with a false client.');
		}

		TestReflection::setValue('JApplicationHelper', '_clients', $current);
	}

	/**
	 * Test JPathway::getPathway().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetPathway()
	{
		$this->fixture->addItem('Item1', 'index.php?key=item1');
		$this->fixture->addItem('Item2', 'index.php?key=item2');

		$pathway = array();
		$object1 = new stdClass;
		$object1->name = 'Item1';
		$object1->link = 'index.php?key=item1';
		$pathway[] = $object1;
		$object2 = new stdClass;
		$object2->name = 'Item2';
		$object2->link = 'index.php?key=item2';
		$pathway[] = $object2;

		$this->assertEquals($pathway, $this->fixture->getPathway());
	}

	/**
	 * Test JPathway::setPathway().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testSetPathway()
	{
		$pathway = array();
		$object1 = new stdClass;
		$object1->name = 'Item1';
		$object1->link = 'index.php?key=item1';
		$pathway[2] = $object1;
		$object2 = new stdClass;
		$object2->name = 'Item2';
		$object2->link = 'index.php?key=item2';
		$pathway[4] = $object2;

		$this->assertEquals(array(), $this->fixture->setPathway($pathway));
		$this->assertAttributeEquals(array_values($pathway), '_pathway', $this->fixture);

		$this->assertEquals(array_values($pathway), $this->fixture->setPathway(array()));
		$this->assertAttributeEquals(array(), '_pathway', $this->fixture);
	}

	/**
	 * Test JPathway::getPathwayNames().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testGetPathwayNames()
	{
		$pathway = array();
		$object1 = new stdClass;
		$object1->name = 'Item1';
		$object1->link = 'index.php?key=item1';
		$pathway[] = $object1;
		$object2 = new stdClass;
		$object2->name = 'Item2';
		$object2->link = 'index.php?key=item2';
		$pathway[] = $object2;

		TestReflection::setValue($this->fixture, '_pathway', $pathway);

		$this->fixture->setPathway($pathway);

		$this->assertEquals(array('Item1', 'Item2'), $this->fixture->getPathwayNames());
	}

	/**
	 * Test JPathway::addItem().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testAddItem()
	{
		$pathway = array();
		$object1 = new stdClass;
		$object1->name = 'Item1';
		$object1->link = 'index.php?key=item1';
		$pathway[] = $object1;
		$object2 = new stdClass;
		$object2->name = 'Item2';
		$object2->link = 'index.php?key=item2';
		$pathway[] = $object2;

		$this->fixture->addItem('Item1', 'index.php?key=item1');
		$this->fixture->addItem('Item2', 'index.php?key=item2');

		$this->assertAttributeEquals($pathway, '_pathway', $this->fixture);
	}

	/**
	 * Test JPathway::setItemName().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testSetItemName()
	{
		$pathway = array();
		$object1 = new stdClass;
		$object1->name = 'Item1';
		$object1->link = 'index.php?key=item1';
		$pathway[] = $object1;
		$object2 = new stdClass;
		$object2->name = 'Item2';
		$object2->link = 'index.php?key=item2';
		$pathway[] = $object2;

		$this->fixture->setPathway($pathway);

		$this->assertTrue($this->fixture->setItemName(1, 'Item3'));

		$pathway[1]->name = 'Item3';

		$this->assertAttributeEquals($pathway, '_pathway', $this->fixture);

		$this->assertFalse($this->fixture->setItemName(3, 'False'));

		$this->assertAttributeEquals($pathway, '_pathway', $this->fixture);
	}

	/**
	 * Test JPathway::makeItem().
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testMakeItem()
	{
		$object = new stdClass;
		$object->link = 'index.php?key=value1';
		$object->name = 'Value1';

		$this->assertEquals($object, TestReflection::invoke($this->fixture, 'makeItem', 'Value1', 'index.php?key=value1'));
	}
}
