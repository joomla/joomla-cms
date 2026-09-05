<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Table
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Table;

use Joomla\CMS\Table\Nested;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Dispatcher;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for the query behaviour of Nested::rebuild().
 *
 * These tests never touch a database. They stub the driver and assert how rebuild() talks to it: how
 * many reads it issues, which rows it writes, and how it behaves when the adjacency list is broken.
 * The values it computes are covered by the integration test of the same name.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Table
 * @since       __DEPLOY_VERSION__
 */
class NestedRebuildTest extends UnitTestCase
{
    /**
     * Number of adjacency list reads the stub has served.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    private $reads = 0;

    /**
     * The bound values of every UPDATE the table executed, in order.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $writes = [];

    /**
     * Transaction calls the table made, in order.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $transactions = [];

    /**
     * Drop the static column cache so each test can define its own table shape.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reads        = 0;
        $this->writes       = [];
        $this->transactions = [];

        $fields = new \ReflectionProperty(Table::class, 'tableFields');
        $fields->setAccessible(true);
        $fields->setValue(null, []);
    }

    /**
     * Build a Nested table on top of a stubbed driver.
     *
     * @param   array  $columns  The column names the table is to report
     * @param   array  $rows     The adjacency list, as row arrays keyed by id
     * @param   array  $options  'failOnWrite' => index of the write that should throw
     *
     * @return  Nested
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createTable(array $columns, array $rows, array $options = []): Nested
    {
        $objects = [];

        foreach ($rows as $id => $row) {
            $objects[$id] = (object) ($row + ['id' => $id]);
        }

        $db      = $this->createStub(DatabaseInterface::class);
        $current = new \stdClass();

        $db->method('getServerType')->willReturn('mysql');
        $db->method('getName')->willReturn('mysqli');
        $db->method('quoteName')->willReturnArgument(0);
        $db->method('quote')->willReturnArgument(0);
        $db->method('getTableColumns')->willReturn(array_fill_keys($columns, 'int'));
        $db->method('getQuery')->willReturnCallback(fn () => $this->getQueryStub($db));
        $db->method('createQuery')->willReturnCallback(fn () => $this->getQueryStub($db));

        $db->method('setQuery')->willReturnCallback(
            function ($query) use ($db, $current) {
                $current->query = $query;

                return $db;
            }
        );

        $db->method('loadObjectList')->willReturnCallback(
            function () use ($objects) {
                $this->reads++;

                return $objects;
            }
        );

        $db->method('execute')->willReturnCallback(
            function () use ($current, $options) {
                $bounded = $current->query->getBounded();
                $write   = [];

                foreach ($bounded as $key => $bind) {
                    $write[ltrim($key, ':')] = $bind->value;
                }

                $this->writes[] = $write;

                if (isset($options['failOnWrite']) && \count($this->writes) === $options['failOnWrite']) {
                    throw new \RuntimeException('write failed');
                }

                return true;
            }
        );

        $db->method('transactionStart')->willReturnCallback(function () {
            $this->transactions[] = 'start';
        });
        $db->method('transactionCommit')->willReturnCallback(function () {
            $this->transactions[] = 'commit';
        });
        $db->method('transactionRollback')->willReturnCallback(function () {
            $this->transactions[] = 'rollback';
        });

        return new Nested('#__nested_' . md5(serialize($columns) . serialize($rows)), 'id', $db, new Dispatcher());
    }

    /**
     * The adjacency list of the tree used by most tests, with the values already correct.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function correctTree(): array
    {
        return [
            1 => ['parent_id' => 0, 'lft' => 0, 'rgt' => 7, 'level' => 0, 'alias' => 'root', 'path' => '', 'ordering' => 0],
            2 => ['parent_id' => 1, 'lft' => 1, 'rgt' => 4, 'level' => 1, 'alias' => 'sport', 'path' => 'sport', 'ordering' => 1],
            3 => ['parent_id' => 2, 'lft' => 2, 'rgt' => 3, 'level' => 2, 'alias' => 'football', 'path' => 'sport/football', 'ordering' => 1],
            4 => ['parent_id' => 1, 'lft' => 5, 'rgt' => 6, 'level' => 1, 'alias' => 'culture', 'path' => 'culture', 'ordering' => 2],
        ];
    }

    /**
     * The default column set.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fullColumns(): array
    {
        return ['id', 'parent_id', 'lft', 'rgt', 'level', 'alias', 'path', 'ordering'];
    }

    /**
     * @testdox  The whole adjacency list is read in a single query
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheAdjacencyListIsReadInOneQuery()
    {
        $rows = [1 => ['parent_id' => 0, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'root', 'path' => '', 'ordering' => 0]];

        // A hundred children under the root, and a chain of ten below the first of them.
        for ($i = 2; $i <= 101; $i++) {
            $rows[$i] = ['parent_id' => 1, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'c' . $i, 'path' => '', 'ordering' => $i];
        }

        for ($i = 102; $i <= 111; $i++) {
            $rows[$i] = ['parent_id' => $i - 1, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'd' . $i, 'path' => '', 'ordering' => 1];
        }

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(1, 0, 0, '');

        // One read for 111 nodes. The recursive implementation issued one read per node.
        $this->assertSame(1, $this->reads);
    }

    /**
     * @testdox  Only the rows whose values change are written
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOnlyChangedRowsAreWritten()
    {
        $rows = $this->correctTree();

        // Exactly one node is wrong.
        $rows[3]['lft'] = 42;

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(1, 0, 0, '');

        $this->assertCount(1, $this->writes);
        $this->assertSame(3, (int) $this->writes[0]['id']);
        $this->assertSame(2, (int) $this->writes[0]['lft']);
        $this->assertSame(3, (int) $this->writes[0]['rgt']);
    }

    /**
     * @testdox  A tree that is already correct is not written at all
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testACorrectTreeIsNotWritten()
    {
        $table = $this->createTable($this->fullColumns(), $this->correctTree());

        $this->assertSame(8, $table->rebuild(1, 0, 0, ''));

        $this->assertSame([], $this->writes, 'Nothing changed, so nothing is written');
        $this->assertSame([], $this->transactions, 'No write means no transaction');
    }

    /**
     * @testdox  A wrong path alone is enough to make a row dirty
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAWrongPathMakesARowDirty()
    {
        $rows = $this->correctTree();

        // Every numeric column is right, only the string differs.
        $rows[4]['path'] = 'stale';

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(1, 0, 0, '');

        $this->assertCount(1, $this->writes);
        $this->assertSame(4, (int) $this->writes[0]['id']);
        $this->assertSame('culture', $this->writes[0]['path']);
    }

    /**
     * @testdox  Writes are wrapped in a transaction
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testWritesAreWrappedInATransaction()
    {
        $rows           = $this->correctTree();
        $rows[2]['lft'] = 99;
        $rows[3]['lft'] = 99;

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(1, 0, 0, '');

        $this->assertSame(['start', 'commit'], $this->transactions);
    }

    /**
     * @testdox  A failing write rolls the transaction back and rethrows
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAFailingWriteRollsBack()
    {
        $rows           = $this->correctTree();
        $rows[2]['lft'] = 99;
        $rows[3]['lft'] = 99;
        $rows[4]['lft'] = 99;

        $table = $this->createTable($this->fullColumns(), $rows, ['failOnWrite' => 2]);

        try {
            $table->rebuild(1, 0, 0, '');
            $this->fail('The failing write has to surface');
        } catch (\RuntimeException $e) {
            $this->assertSame('write failed', $e->getMessage());
        }

        $this->assertSame(['start', 'rollback'], $this->transactions);
    }

    /**
     * @testdox  A cycle in the adjacency list throws instead of looping forever
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testACycleThrows()
    {
        $rows = [
            1 => ['parent_id' => 3, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'a', 'path' => '', 'ordering' => 1],
            2 => ['parent_id' => 1, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'b', 'path' => '', 'ordering' => 1],
            3 => ['parent_id' => 2, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'c', 'path' => '', 'ordering' => 1],
        ];

        $table = $this->createTable($this->fullColumns(), $rows);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cycle in the adjacency list at record ID 1/');

        $table->rebuild(1, 0, 0, '');
    }

    /**
     * @testdox  A node that points at itself is reported as a cycle
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testASelfReferenceIsACycle()
    {
        $rows = [
            1 => ['parent_id' => 0, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'root', 'path' => '', 'ordering' => 0],
            2 => ['parent_id' => 2, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'self', 'path' => '', 'ordering' => 1],
        ];

        $table = $this->createTable($this->fullColumns(), $rows);

        // Node 2 is its own parent, so it is never reached from the root and no cycle is hit.
        $table->rebuild(1, 0, 0, '');

        $written = array_map(fn ($write) => (int) $write['id'], $this->writes);
        $this->assertNotContains(2, $written, 'The self referencing node is unreachable');

        // Starting inside the loop does hit it.
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cycle in the adjacency list at record ID 2/');

        $table->rebuild(2, 0, 0, '');
    }

    /**
     * @testdox  Siblings are sorted by the ordering column when the table has one
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsAreSortedByOrderingWhenPresent()
    {
        $rows = [
            1 => ['parent_id' => 0, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'root', 'path' => '', 'ordering' => 0],
            // lft says alpha first, ordering says gamma first. Ordering has to win.
            2 => ['parent_id' => 1, 'lft' => 1, 'rgt' => 2, 'level' => 1, 'alias' => 'alpha', 'path' => '', 'ordering' => 3],
            3 => ['parent_id' => 1, 'lft' => 3, 'rgt' => 4, 'level' => 1, 'alias' => 'gamma', 'path' => '', 'ordering' => 1],
        ];

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(1, 0, 0, '');

        $byId = [];

        foreach ($this->writes as $write) {
            $byId[(int) $write['id']] = (int) $write['lft'];
        }

        $this->assertSame(1, $byId[3], 'gamma has the lower ordering');
        $this->assertSame(3, $byId[2]);
    }

    /**
     * @testdox  Siblings are sorted by lft when the table has no ordering column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsAreSortedByLeftWithoutAnOrderingColumn()
    {
        $columns = ['id', 'parent_id', 'lft', 'rgt', 'level', 'alias', 'path'];

        $rows = [
            1 => ['parent_id' => 0, 'lft' => 0, 'rgt' => 0, 'level' => 0, 'alias' => 'root', 'path' => ''],
            2 => ['parent_id' => 1, 'lft' => 30, 'rgt' => 31, 'level' => 1, 'alias' => 'alpha', 'path' => ''],
            3 => ['parent_id' => 1, 'lft' => 10, 'rgt' => 11, 'level' => 1, 'alias' => 'gamma', 'path' => ''],
        ];

        $table = $this->createTable($columns, $rows);
        $table->rebuild(1, 0, 0, '');

        $byId = [];

        foreach ($this->writes as $write) {
            $byId[(int) $write['id']] = (int) $write['lft'];
        }

        $this->assertSame(1, $byId[3], 'gamma had the lower lft');
        $this->assertSame(3, $byId[2]);
    }

    /**
     * @testdox  Only the columns the table actually has are written
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOnlyExistingColumnsAreWritten()
    {
        // An assets style table: no alias, no level, no path, no ordering.
        $columns = ['id', 'parent_id', 'lft', 'rgt'];

        $rows = [
            1 => ['parent_id' => 0, 'lft' => 99, 'rgt' => 99],
            2 => ['parent_id' => 1, 'lft' => 99, 'rgt' => 99],
        ];

        $table = $this->createTable($columns, $rows);

        $this->assertSame(4, $table->rebuild(1, 0, 0, ''));

        $this->assertCount(2, $this->writes);

        foreach ($this->writes as $write) {
            $this->assertArrayHasKey('lft', $write);
            $this->assertArrayHasKey('rgt', $write);
            $this->assertArrayNotHasKey('level', $write, 'The table has no level column');
            $this->assertArrayNotHasKey('path', $write, 'The table has no path column');
        }
    }

    /**
     * @testdox  Nodes outside the subtree being rebuilt are never written
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNodesOutsideTheSubtreeAreNotWritten()
    {
        $rows = $this->correctTree();

        // Make every node look wrong, then rebuild only the branch below node 2.
        foreach ($rows as $id => $row) {
            $rows[$id]['lft'] = 99;
            $rows[$id]['rgt'] = 99;
        }

        $table = $this->createTable($this->fullColumns(), $rows);
        $table->rebuild(2, 1, 1, 'sport');

        $written = array_map(fn ($write) => (int) $write['id'], $this->writes);
        sort($written);

        $this->assertSame([2, 3], $written, 'Only the subtree of node 2 is touched');
    }

    /**
     * @testdox  The virtual parent 0 is computed but never written
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheVirtualParentIsNotWritten()
    {
        $rows = $this->correctTree();

        foreach ($rows as $id => $row) {
            $rows[$id]['lft'] = 99;
        }

        $table = $this->createTable($this->fullColumns(), $rows);

        // Start at 0, which is not a row in the table.
        $table->rebuild(0, 0, 0, '');

        $written = array_map(fn ($write) => (int) $write['id'], $this->writes);

        $this->assertNotContains(0, $written, 'There is no row with id 0 to write');
        $this->assertContains(1, $written);
    }
}
