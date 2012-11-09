<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Pathway
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/JPathwayInspector.php';

/**
 * Test class for JPathway.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pathway
 *
 * @since       12.3
 */
class JPathwayTest extends TestCase
{
	/**
	 * Set up the tests
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->fixture = new JPathwayInspector;

		parent::setUp();
	}

	/**
	 * Tear down the tests
	 *
	 * @return  void
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
	 */
	public function test__construct()
	{
		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo(array())
		);
	}

	/**
	 * Test JPathway::getInstance().
	 *
	 * @return  void
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

		$pathway = JPathway::getInstance('Inspector');
		$this->assertThat(
			get_class($pathway),
			$this->equalTo('JPathwayInspector')
		);

		$this->assertThat(
			JPathway::getInstance('Inspector'),
			$this->equalTo($pathway)
		);

		$pathway = JPathway::getInstance('Inspector2');
		$this->assertThat(
			get_class($pathway),
			$this->equalTo('JPathwayInspector2')
		);

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
			$this->fail('JPathway did not throw proper exception upon false client.');
		}

		TestReflection::setValue('JApplicationHelper', '_clients', $current);
	}

	/**
	 * Test JPathway::getPathway().
	 *
	 * @return  void
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

		$this->assertThat(
			$this->fixture->getPathway(),
			$this->equalTo($pathway)
		);
	}

	/**
	 * Test JPathway::setPathway().
	 *
	 * @return  void
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

		$this->assertThat(
			$this->fixture->setPathway($pathway),
			$this->equalTo(array())
		);

		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo(array_values($pathway))
		);

		$this->assertThat(
			$this->fixture->setPathway(array()),
			$this->equalTo(array_values($pathway))
		);

		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo(array())
		);
	}

	/**
	 * Test JPathway::getPathwayNames().
	 *
	 * @return  void
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

		$this->fixture->_pathway = $pathway;

		$this->assertThat(
			$this->fixture->getPathwayNames(),
			$this->equalTo(array('Item1', 'Item2'))
		);
	}

	/**
	 * Test JPathway::addItem().
	 *
	 * @return  void
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

		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo($pathway)
		);
	}

	/**
	 * Test JPathway::setItemName().
	 *
	 * @return  void
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

		$this->fixture->_pathway = $pathway;

		$this->assertTrue(
			$this->fixture->setItemName(1, 'Item3')
		);

		$pathway[1]->name = 'Item3';
		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo($pathway)
		);

		$this->assertFalse(
			$this->fixture->setItemName(3, 'False')
		);

		$this->assertThat(
			$this->fixture->_pathway,
			$this->equalTo($pathway)
		);
	}

	/**
	 * Test JPathway::_makeItem().
	 *
	 * @return  void
	 */
	public function test_makeItem()
	{
		$object = new stdClass;
		$object->link = 'index.php?key=value1';
		$object->name = 'Value1';

		$this->assertThat(
			$this->fixture->_makeItem('Value1', 'index.php?key=value1'),
			$this->equalTo($object)
		);
	}
}
