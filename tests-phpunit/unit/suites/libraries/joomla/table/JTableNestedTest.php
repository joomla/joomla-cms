<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/nested.php';

/**
 * Test class for JTableNested.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       11.1
 */
class JTableNestedTest extends TestCaseDatabase
{
	/**
	 * @var    NestedTable
	 * @since  12.1
	 */
	protected $class;

	/**
	 * Tests the `check` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCheck()
	{
		$this->class->parent_id = 1;
		$this->assertTrue($this->class->check(), 'Checks a valid result.');

		$this->class->parent_id = 0;
		$this->assertFalse($this->class->check(), 'Checks fail for parent_id = 0.');

		$this->class->parent_id = 99;
		$this->assertFalse($this->class->check(), 'Checks fail for unknown parent_id.');
	}

	/**
	 * Tests the `debug` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testDebug()
	{
		$this->class->debug(99);
		$this->assertEquals(99, TestReflection::getValue($this->class, '_debug'));
	}

	/**
	 * Tests the `delete` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testDelete()
	{
		// Delete without children.

		$this->class->id = 102;
		$this->assertTrue($this->class->delete(null, false), 'Checks delete 102 worked.');

		$nodes = self::$driver->setQuery('SELECT id, parent_id, lft, rgt, level FROM #__categories')->loadRowList(0);

		$this->assertEquals(array(201, 1, 3, 4, 1), $nodes[201], 'Checks movement of node 201.');
		$this->assertEquals(array(202, 1, 5, 6, 1), $nodes[202], 'Checks movement of node 202.');

		// Delete with children.

		$this->class->id = 103;
		$this->assertTrue($this->class->delete(), 'Checks delete 103 worked.');

		$ids = self::$driver->setQuery('SELECT id FROM #__categories')->loadColumn();

		$this->assertEquals(4, count($ids), 'Checks 3 nodes were deleted.');
		$this->assertArrayNotHasKey(103, $ids, 'Checks node 103 was deleted.');
		$this->assertArrayNotHasKey(203, $ids, 'Checks node 203 was deleted.');
		$this->assertArrayNotHasKey(204, $ids, 'Checks node 204 was deleted.');

		// We need to confirm the locking is called, so we create a mock.
		$class = $this->getMock(
			'NestedTable',
			array('_lock'),
			array(self::$driver)
		);

		$class->expects($this->any())->method('_lock')->will($this->returnValue(false));
		$this->assertFalse($class->delete(1), 'Checks a locked table returns false.');
	}

	/**
	 * Tests the `getPath` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetPath()
	{
		$path = $this->class->getPath(203);
		$this->assertEquals('node001', $path[0]->alias, 'Checks first node.');
		$this->assertEquals('node103', $path[1]->alias, 'Checks second node.');
		$this->assertEquals('node203', $path[2]->alias, 'Checks third node.');
		$this->assertTrue(isset($path[0]->description), 'Checks diagnostic = false (default case).');

		$path = $this->class->getPath(203, true);
		$this->assertFalse(isset($path[0]->description), 'Checks diagnostic = true.');
	}

	/**
	 * Tests the `getRootId` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetRootId()
	{
		$this->assertEquals(1, $this->class->getRootId(), 'Checks for parent_id = 0 case.');

		// Change the id of the root node.
		self::$driver->setQuery('UPDATE #__categories SET parent_id = 99 WHERE id = 1')->execute();
		$this->class->resetRootId();
		$this->assertEquals(1, $this->class->getRootId(), 'Checks for lft = 0 case.');

		// Change the lft of the root node.
		self::$driver->setQuery('UPDATE #__categories SET lft = 99, alias = ' . self::$driver->q('root') . ' WHERE id = 1')->execute();
		$this->class->resetRootId();
		$this->assertEquals(1, $this->class->getRootId(), 'Checks for alias = root case.');

		// Change the alias of the root node.
		self::$driver->setQuery('UPDATE #__categories SET alias = ' . self::$driver->q('foo') . ' WHERE id = 1')->execute();
		$this->class->resetRootId();
		$this->assertFalse($this->class->getRootId(), 'Checks for failure.');
	}

	/**
	 * Tests the `getTree` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testGetTree()
	{
		// Get the whole tree
		$tree = $this->class->getTree(1);
		$this->assertEquals(1, $tree[0]->id, 'Checks node 001.');
		$this->assertEquals(101, $tree[1]->id, 'Checks node 101.');
		$this->assertEquals(102, $tree[2]->id, 'Checks node 102.');
		$this->assertEquals(201, $tree[3]->id, 'Checks node 201.');
		$this->assertEquals(202, $tree[4]->id, 'Checks node 202.');
		$this->assertEquals(103, $tree[5]->id, 'Checks node 103.');
		$this->assertEquals(203, $tree[6]->id, 'Checks node 203.');
		$this->assertEquals(204, $tree[7]->id, 'Checks node 204.');
		$this->assertTrue(isset($tree[0]->description), 'Checks diagnostic = false (default case).');

		// Get a subtree
		$tree = $this->class->getTree(103);
		$this->assertEquals(103, $tree[0]->id, 'Checks subtree node 103.');
		$this->assertEquals(203, $tree[1]->id, 'Checks subtree node 203.');
		$this->assertEquals(204, $tree[2]->id, 'Checks subtree node 204.');

		$tree = $this->class->getTree(1, true);
		$this->assertFalse(isset($tree[0]->description), 'Checks diagnostic = true.');
	}

	/**
	 * Tests the `isLeaf` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testIsLeaf()
	{
		$this->assertTrue($this->class->isLeaf(202), 'Checks a valid leaf.');
		$this->assertFalse($this->class->isLeaf(102), 'Checks a non-leaf.');
		$this->assertNull($this->class->isLeaf(99), 'Checks an invalid node.');
	}

	/**
	 * Tests the `move` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMove_right()
	{
// 		TestReflection::invoke($this->class, '_logtable');

		$this->class->load(101);
		$this->assertTrue($this->class->move(1, null));

		$this->assertEquals(7, $this->class->lft, 'Check new lft of 101.');
		$this->assertEquals(8, $this->class->rgt, 'Check new rgt of 101.');

		$this->class->load(102);
		$this->assertEquals(1, $this->class->lft, 'Check new lft of 102.');
		$this->assertEquals(6, $this->class->rgt, 'Check new rgt of 102.');
	}

	/**
	 * Tests the `move` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMove_left()
	{
		$this->class->load(204);
		$this->assertTrue($this->class->move(-1, null));

		$this->assertEquals(10, $this->class->lft, 'Check new lft of 204.');
		$this->assertEquals(11, $this->class->rgt, 'Check new rgt of 204.');

		$this->class->load(203);
		$this->assertEquals(12, $this->class->lft, 'Check new lft of 203.');
		$this->assertEquals(13, $this->class->rgt, 'Check new rgt of 203.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_after()
	{
		// Move 201 to after 102.
		$this->class->load(201);
		$this->assertTrue($this->class->moveByReference(102, 'after'));

		$this->assertEquals(7, $this->class->lft, 'Check new lft of 201.');
		$this->assertEquals(8, $this->class->rgt, 'Check new rgt of 201.');

		$this->class->load(102);
		$this->assertEquals(6, $this->class->rgt, 'Check new rgt of 102.');

		$this->class->load(103);
		$this->assertEquals(9, $this->class->lft, 'Check lft of 103.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_before()
	{
		// Move 103 to before 102.
		$this->class->load(103);
		$this->assertTrue($this->class->moveByReference(102, 'before'));

		$this->assertEquals(3, $this->class->lft, 'Check new lft of 103.');
		$this->assertEquals(8, $this->class->rgt, 'Check new rgt of 103.');

		$this->class->load(102);
		$this->assertEquals(9, $this->class->lft, 'Check new lft of 102.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_firstChild()
	{
		// Move 204 to first child of 102.
		$this->class->load(204);
		$this->assertTrue($this->class->moveByReference(102, 'first-child'));

		$this->assertEquals(4, $this->class->lft, 'Check new lft of 204.');
		$this->assertEquals(5, $this->class->rgt, 'Check new rgt of 204.');

		$this->class->load(102);
		$this->assertEquals(10, $this->class->rgt, 'Check new rgt of 102.');

		$this->class->load(201);
		$this->assertEquals(6, $this->class->lft, 'Check lft of 103.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_lastChild()
	{
		// Move 204 to last child of 102.
		$this->class->load(204);
		$this->assertTrue($this->class->moveByReference(102, 'last-child'));

		$this->assertEquals(8, $this->class->lft, 'Check new lft of 204.');
		$this->assertEquals(9, $this->class->rgt, 'Check new rgt of 204.');

		$this->class->load(102);
		$this->assertEquals(10, $this->class->rgt, 'Check new rgt of 102.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_noReference()
	{
		// Move 101 to last child of root.
		$this->class->load(101);
		$this->assertTrue($this->class->moveByReference(0));

		$this->assertEquals(13, $this->class->lft, 'Check new lft of 101.');
		$this->assertEquals(14, $this->class->rgt, 'Check new rgt of 101.');
	}

	/**
	 * Tests the `moveByReference` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testMoveByReference_failures()
	{
		$this->assertFalse($this->class->moveByReference(0, 'after', 99), 'Checks invalid pk.');

		$this->class->load(102);
		$this->assertFalse($this->class->moveByReference(202, 'after'), 'Checks moving to a child.');

		// We need to confirm the locking is called, so we create a mock.
		$class = $this->getMock(
			'NestedTable',
			array('_lock'),
			array(self::$driver)
		);

		$class->expects($this->any())->method('_lock')->will($this->returnValue(false));
		$this->assertFalse($class->moveByReference(103, 'after', 102), 'Checks a locked table returns false.');
	}

	/**
	 * Tests the `orderDown` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testOrderDown()
	{
		// These methods should really be called moveLeft and moveRight.
		$this->assertTrue($this->class->orderDown(201));

		$nodes = self::$driver->setQuery('SELECT id, lft, rgt FROM #__categories')->loadRowList(0);

		$this->assertEquals(array('201', '6', '7'), $nodes[201], 'Checks 201 moved to the right.');
		$this->assertEquals(array('202', '4', '5'), $nodes[202], 'Checks 202 was bumbed to the left');

		$this->assertFalse($this->class->orderDown(201), 'Checks 201 cannot move further to the right.');
	}

	/**
	 * Tests the `orderUp` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testOrderUp()
	{
		// These methods should really be called moveLeft and moveRight.
		$this->assertTrue($this->class->orderUp(202));

		$nodes = self::$driver->setQuery('SELECT id, lft, rgt FROM #__categories')->loadRowList(0);

		$this->assertEquals(array('202', '4', '5'), $nodes[202], 'Checks 202 moved to the left.');
		$this->assertEquals(array('201', '6', '7'), $nodes[201], 'Checks 201 was bumbed to the right');

		$this->assertFalse($this->class->orderUp(202), 'Checks 202 cannot move further to the left.');
	}

	/**
	 * Tests the `publish` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testPublish()
	{
		// Reset the published state.
		self::$driver->setQuery('UPDATE #__categories SET published = 0')
			->execute();

		$this->assertTrue($this->class->publish(array(101, 102), 1));

		$nodes = self::$driver->setQuery('SELECT id, published FROM #__categories')->loadObjectList('id');

		$this->assertEquals(1, $nodes[101]->published, 'Checks node 101.');
		$this->assertEquals(1, $nodes[102]->published, 'Checks node 102.');
		$this->assertEquals(0, $nodes[103]->published, 'Checks node 103.');

		//
		$this->class->id = '203,204';
		$this->assertTrue($this->class->publish(null, -1));

		$nodes = self::$driver->setQuery('SELECT id, published FROM #__categories')->loadObjectList('id');

		$this->assertEquals(-1, $nodes[203]->published, 'Checks node 203.');
		$this->assertEquals(-1, $nodes[204]->published, 'Checks node 204.');
	}

	/**
	 * Tests the `rebuild` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testRebuild()
	{
		// Reset the nested set metrics.
		self::$driver->setQuery('UPDATE #__categories SET lft = 0, rgt = 0')
			->execute();

		// Rebuild the whole tree.
		TestReflection::setValue($this->class, '_cache', array());
		$this->class->rebuild();

		$nodes = self::$driver->setQuery('SELECT id, lft, rgt, level, path FROM #__categories')->loadRowList(0);

		// Level 0 root node.
		$this->assertEquals(array('1', '0', '15', '0', ''), $nodes[1], 'Checks node 001.');

		// Level 1 nodes.
		$this->assertEquals(array('101', '1', '2', '1', 'node101'), $nodes[101], 'Checks node 101.');
		$this->assertEquals(array('102', '3', '8', '1', 'node102'), $nodes[102], 'Checks node 102.');
		$this->assertEquals(array('103', '9', '14', '1', 'node103'), $nodes[103], 'Checks node 103.');

		// Level 2 nodes.
		$this->assertEquals(array('201', '4', '5', '2', 'node102/node201'), $nodes[201], 'Checks node 201.');
		$this->assertEquals(array('202', '6', '7', '2', 'node102/node202'), $nodes[202], 'Checks node 202.');
		$this->assertEquals(array('203', '10', '11', '2', 'node103/node203'), $nodes[203], 'Checks node 203.');
		$this->assertEquals(array('204', '12', '13', '2', 'node103/node204'), $nodes[204], 'Checks node 204.');

		// Rebuild with a base path.
		TestReflection::setValue($this->class, '_cache', array());
		$this->class->rebuild(null, 0, 0, 'base');

		$nodes = self::$driver->setQuery('SELECT id, lft, rgt, level, path FROM #__categories')->loadRowList(0);

		$this->assertEquals(array('204', '12', '13', '2', 'base/node103/node204'), $nodes[204], 'Checks node 204 with new base.');

		// Simulate where the 'ordering' field is available.
		self::$driver->setQuery('ALTER TABLE #__categories ADD ordering INTEGER')
			->execute();

		$this->class->ordering = null;

		TestReflection::setValue($this->class, '_cache', array());
		$this->assertEquals(16, $this->class->rebuild(), 'Checks rebuild with ordering.');

		// Reset the root node.
		self::$driver->setQuery('UPDATE #__categories SET parent_id = 99, lft = 99, rgt = 99 WHERE id = 1')
			->execute();
		$this->class->resetRootId();

		TestReflection::setValue($this->class, '_cache', array());
		$this->assertFalse($this->class->rebuild(), 'Checks failure where no root node is found.');
	}

	/**
	 * Tests the `delete` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testRebuildPath()
	{
		$this->class->rebuildPath(1);
		$this->class->rebuildPath(101);
		$this->class->rebuildPath(204);

		$paths = self::$driver->setQuery('SELECT id, path FROM #__categories')->loadObjectList('id');

		$this->assertEquals('node001', $paths[1]->path, 'Checks node 001.');
		$this->assertEquals('node001/node101', $paths[101]->path, 'Checks node 101.');
		$this->assertEquals('node001/node103/node204', $paths[204]->path, 'Checks node 204.');

		// Check for special case where 'root' is removed.
		self::$driver->setQuery('UPDATE #__categories SET alias = ' . self::$driver->q('root') . ' WHERE id = 1')
			->execute();

		$this->class->rebuildPath(203);

		$paths = self::$driver->setQuery('SELECT id, path FROM #__categories')->loadObjectList('id');

		$this->assertEquals('node103/node203', $paths[203]->path, 'Checks node 203.');
	}

	/**
	 * Tests the `saveorder` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSaveorder()
	{
		$ids = array(101, 102, 103);
		$lft = array(3, 9, 1);
		$this->assertEquals(16, $this->class->saveorder($ids, $lft), 'Checks saveorder worked.');

		// TestReflection::invoke($this->class, '_logtable');

		$nodes = self::$driver->setQuery('SELECT id, lft, rgt FROM #__categories')->loadRowList(0);

		$this->assertEquals(array(103, 1, 6), $nodes[103]);
		$this->assertEquals(array(101, 7, 8), $nodes[101]);
		$this->assertEquals(array(102, 9, 14), $nodes[102]);

		$this->assertFalse($this->class->saveorder(array(1), array(1, 2)), 'Checks array mismatch.');
		$this->assertFalse($this->class->saveorder(99, array(1, 2)), 'Checks ids not array.');
		$this->assertFalse($this->class->saveorder(array(1, 2), 99), 'Checks lfts not array.');
	}

	/**
	 * Tests the `setLocation` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testSetLocation()
	{
		$this->class->setLocation(20);
		$this->assertAttributeEquals(20, '_location_id', $this->class);
		$this->assertAttributeEquals('after', '_location', $this->class);

		$this->class->setLocation(20, 'before');
		$this->assertAttributeEquals('before', '_location', $this->class);

		$this->class->setLocation(20, 'first-child');
		$this->assertAttributeEquals('first-child', '_location', $this->class);

		$this->class->setLocation(20, 'last-child');
		$this->assertAttributeEquals('last-child', '_location', $this->class);
	}

	/**
	 * Tests the `setLocation` method for an expected exception.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @expectedException  InvalidArgumentException
	 */
	public function testSetLocation_exception()
	{
		$this->class->setLocation(20, 'foo');
	}

	/**
	 * Tests the `store` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testStore()
	{
		$this->class->reset();
		$this->class->setLocation(102, 'last-child');

		$this->assertTrue($this->class->store(), 'Checks the store method.');

		// TestReflection::invoke($this->class, '_logtable');

		$this->assertEquals(8, $this->class->lft, 'Check new lft.');
		$this->assertEquals(9, $this->class->rgt, 'Check new rgt.');

		// We need to confirm the locking is called, so we create a mock.
		$class = $this->getMock(
			'NestedTable',
			array('_lock'),
			array(self::$driver)
		);

		$class->expects($this->any())->method('_lock')->will($this->returnValue(false));
		$this->assertFalse($class->store(), 'Checks a locked table returns false.');
	}

	/**
	 * Tests the `_getNode` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test_getNode()
	{
		$node = TestReflection::invoke($this->class, '_getNode', 1);
		$this->assertEquals(1, $node->id, 'Check id (by pk).');
		$this->assertEquals(7, $node->numChildren, 'Check number of children (by pk).');
		$this->assertEquals(16, $node->width, 'Check width (by pk).');

		$node = TestReflection::invoke($this->class, '_getNode', 103, 'parent');
		$this->assertEquals(203, $node->id, 'Check id (by parent).');
		$this->assertEquals(0, $node->numChildren, 'Check number of children (by parent).');
		$this->assertEquals(2, $node->width, 'Check width (by parent).');

		$node = TestReflection::invoke($this->class, '_getNode', 3, 'left');
		$this->assertEquals(102, $node->id, 'Check id (by left).');
		$this->assertEquals(2, $node->numChildren, 'Check number of children (by left).');
		$this->assertEquals(6, $node->width, 'Check width (by left).');

		$node = TestReflection::invoke($this->class, '_getNode', 2, 'right');
		$this->assertEquals(101, $node->id, 'Check id (by right).');
		$this->assertEquals(0, $node->numChildren, 'Check number of children (by right).');
		$this->assertEquals(2, $node->width, 'Check width (by right).');

		$node = TestReflection::invoke($this->class, '_getNode', 1, 'foo');
		$this->assertEquals(1, $node->id, 'Check id (by foo).');

		$node = TestReflection::invoke($this->class, '_getNode', 99);
		$this->assertFalse($node);
	}

	/**
	 * Tests the `_getTreeRepositionData` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test_getTreeRepositionData()
	{
		$object = (object) array('id' => 1, 'parent_id' => 0, 'lft' => 2, 'rgt' => 4, 'level' => 1);

		$before = TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 10, 'before');
		$this->assertEquals(
			array('left_where' => 'lft >= 2', 'right_where' => 'rgt >= 2', 'new_lft' => 2, 'new_rgt' => 11, 'new_parent_id' => 0, 'new_level' => 1),
			(array) $before,
			'Checks the before case.'
		);

		$after = TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 10, 'after');
		$this->assertEquals(
			array('left_where' => 'lft > 4', 'right_where' => 'rgt > 4', 'new_lft' => 5, 'new_rgt' => 14, 'new_parent_id' => 0, 'new_level' => 1),
			(array) $after,
			'Checks the after case.'
		);

		$firstChild = TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 10, 'first-child');
		$this->assertEquals(
			array('left_where' => 'lft > 2', 'right_where' => 'rgt >= 2', 'new_lft' => 3, 'new_rgt' => 12, 'new_parent_id' => 1, 'new_level' => 2),
			(array) $firstChild,
			'Checks the first-child case.'
		);

		$lastChild = TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 10, 'last-child');
		$this->assertEquals(
			array('left_where' => 'lft > 4', 'right_where' => 'rgt >= 4', 'new_lft' => 4, 'new_rgt' => 13, 'new_parent_id' => 1, 'new_level' => 2),
			(array) $lastChild,
			'Checks the last-child case.'
		);

		$default = TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 10);
		$this->assertEquals($default, $before, 'Checks the default handling is before.');

		$this->assertFalse(
			TestReflection::invoke($this->class, '_getTreeRepositionData', 'foo', 10),
			'Checks an invalid data type.'
		);

		$this->assertFalse(
			TestReflection::invoke($this->class, '_getTreeRepositionData', (object) array('lft' => 1), 10),
			'Checks an object with invalid rgt.'
		);

		$this->assertFalse(
			TestReflection::invoke($this->class, '_getTreeRepositionData', (object) array('lft' => 1), 10),
			'Checks an object with invalid lft.'
		);

		$this->assertFalse(
			TestReflection::invoke($this->class, '_getTreeRepositionData', $object, 1),
			'Checks an object with invalid width.'
		);
	}

	/**
	 * Tests the `_runQuery` method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test_runQuery()
	{
		// Just run a valid query and then check for an exception case.
		TestReflection::invoke($this->class, '_runQuery', 'SELECT * FROM #__categories', 'foo');

		try
		{
			// We need to confirm the locking is called, so we create a mock.
			$class = $this->getMock(
				'NestedTable',
				array('_unlock'),
				array(self::$driver)
			);

			// Then override the _unlock method so we can test that it was called.
			$this->assignMockCallbacks(
				$class,
				array(
					'_unlock' => array('NestedTable', 'mockUnlock'),
				)
			);

			// Reset the value to detect the change.
			NestedTable::$unlocked = false;

			TestReflection::invoke($class, '_runQuery', 'SELECT foo FROM #__categories', 'foo');

			$this->fail('A RuntimeException was expected.');
		}
		catch (RuntimeException $e)
		{
			$this->assertTrue(NestedTable::$unlocked);
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 *
	 * @since   11.3
	 */
	protected function getDataSet()
	{
		/*
		----------------------------------------
		|   id |  par |  lft |  rgt | lvl
		----------------------------------------
		|    1 |    0 |    0 |   15 |   0
		|  101 |    1 |    1 |    2 |   1
		|  102 |    1 |    3 |    8 |   1
		|  103 |    1 |    9 |   14 |   1
		|  201 |  102 |    4 |    5 |   2
		|  202 |  102 |    6 |    7 |   2
		|  203 |  103 |   10 |   11 |   2
		|  204 |  103 |   12 |   13 |   2
		----------------------------------------
		*/
		return $this->createXMLDataSet(__DIR__ . '/stubs/nested.xml');
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @since  11.1
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setup();

// 		$this->saveFactoryState();

// 		JFactory::$session = $this->getMockSession();

		$this->class = new NestedTable(self::$driver);
	}

	/**
	 * Tears down the fixture.
	 *
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function tearDown()
	{
// 		$this->restoreFactoryState();

		parent::tearDown();
	}
}
