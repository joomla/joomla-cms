<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Table
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Libraries\Cms\Table;

use Joomla\CMS\Table\Nested;
use Joomla\Event\Dispatcher;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Tests for Nested::rebuild() against a real database.
 *
 * rebuild() derives the whole nested set from the parent_id links, so every test here scrambles or
 * clears the lft, rgt, level and path columns first and then asserts what rebuild() puts back.
 *
 * Unless a test is explicitly about the default start node, it passes the root id to rebuild()
 * rather than relying on the no argument form. See testNoArgumentFormStartsAtTheVirtualParent()
 * for why that distinction matters.
 *
 * @package     Joomla.IntegrationTest
 * @subpackage  Table
 * @since       __DEPLOY_VERSION__
 */
class NestedRebuildTest extends IntegrationTestCase implements DBTestInterface
{
    use DBTestTrait;

    /**
     * The table under test.
     *
     * @var    Nested
     * @since  __DEPLOY_VERSION__
     */
    private $table;

    /**
     * Retrieve the list of schemas to load for this test case.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getSchemasToLoad(): array
    {
        return ['framework.sql', 'nested.sql'];
    }

    /**
     * Reset the fixture tables and the static root cache.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        // getRootId() caches into a static shared by every Nested instance in the process.
        $property = new \ReflectionProperty(Nested::class, 'root_id');
        $property->setAccessible(true);
        $property->setValue(null, 0);

        $db = $this->getDBDriver();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__testnested'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__testnestedplain'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__testnestedminimal'))->execute();

        $this->table = new Nested('#__testnested', 'id', $db, new Dispatcher());
    }

    /**
     * Insert a row with deliberately wrong nested set values.
     *
     * Only id, parent_id, alias and ordering are meaningful input. lft, rgt, level and path are the
     * columns rebuild() is supposed to compute, so they start out as garbage.
     *
     * @param   integer  $id        The row id
     * @param   integer  $parentId  The parent row id
     * @param   string   $alias     The alias that feeds the path
     * @param   integer  $ordering  The sibling ordering
     * @param   string   $table     The fixture table to write to
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function insertNode(int $id, int $parentId, string $alias, int $ordering = 0, string $table = '#__testnested'): void
    {
        $db      = $this->getDBDriver();
        $columns = [
            $db->quoteName('id') . ' = ' . $id,
            $db->quoteName('parent_id') . ' = ' . $parentId,
            $db->quoteName('alias') . ' = ' . $db->quote($alias),
            $db->quoteName('title') . ' = ' . $db->quote(ucfirst($alias)),
            // Deliberate garbage, rebuild() has to overwrite all four.
            $db->quoteName('lft') . ' = 999',
            $db->quoteName('rgt') . ' = 999',
            $db->quoteName('level') . ' = 99',
            $db->quoteName('path') . ' = ' . $db->quote('garbage'),
        ];

        if ($table === '#__testnested') {
            $columns[] = $db->quoteName('ordering') . ' = ' . $ordering;
        }

        $db->setQuery('INSERT INTO ' . $db->quoteName($table) . ' SET ' . implode(', ', $columns))->execute();
    }

    /**
     * Read the whole fixture table back, keyed by id.
     *
     * @param   string  $table  The fixture table to read
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function readTree(string $table = '#__testnested'): array
    {
        $db = $this->getDBDriver();

        $rows = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['id', 'lft', 'rgt', 'level', 'path']))
                ->from($db->quoteName($table))
                ->order($db->quoteName('lft') . ' ASC')
        )->loadAssocList();

        $tree = [];

        foreach ($rows as $row) {
            $tree[(int) $row['id']] = [
                'lft'   => (int) $row['lft'],
                'rgt'   => (int) $row['rgt'],
                'level' => (int) $row['level'],
                'path'  => $row['path'],
            ];
        }

        return $tree;
    }

    /**
     * Seed the tree used by most of the tests.
     *
     *   1 root
     *   |- 2 sport          |- 4 culture
     *      |- 3 football
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedTree(): void
    {
        $this->insertNode(1, 0, 'root');
        $this->insertNode(2, 1, 'sport', 1);
        $this->insertNode(3, 2, 'football', 1);
        $this->insertNode(4, 1, 'culture', 2);
    }

    /**
     * @testdox  rebuild() recomputes lft, rgt, level and path for the whole tree
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildRecomputesTheWholeTree()
    {
        $this->seedTree();

        $this->assertSame(8, $this->table->rebuild(1, 0, 0, ''));

        $this->assertSame(
            [
                1 => ['lft' => 0, 'rgt' => 7, 'level' => 0, 'path' => ''],
                2 => ['lft' => 1, 'rgt' => 4, 'level' => 1, 'path' => 'sport'],
                3 => ['lft' => 2, 'rgt' => 3, 'level' => 2, 'path' => 'sport/football'],
                4 => ['lft' => 5, 'rgt' => 6, 'level' => 1, 'path' => 'culture'],
            ],
            $this->readTree()
        );
    }

    /**
     * @testdox  rebuild() answers with one more than the right value of the node it started at
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildAnswersWithOneMoreThanTheStartingRight()
    {
        $this->seedTree();

        // Not a boolean. The start node ends up with rgt = 7, so the answer is 8.
        $this->assertSame(8, $this->table->rebuild(1, 0, 0, ''));
    }

    /**
     * @testdox  rebuild() derives the tree from parent_id alone
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildDerivesTheTreeFromParentIdAlone()
    {
        $this->seedTree();

        // Wipe every nested set column, so parent_id is the only structural information left.
        $db = $this->getDBDriver();
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__testnested') . ' SET ' . $db->quoteName('lft') . ' = 0, '
            . $db->quoteName('rgt') . ' = 0, ' . $db->quoteName('level') . ' = 0, '
            . $db->quoteName('path') . ' = ' . $db->quote('')
        )->execute();

        $this->table->rebuild(1, 0, 0, '');

        $this->assertSame(
            [
                1 => ['lft' => 0, 'rgt' => 7, 'level' => 0, 'path' => ''],
                2 => ['lft' => 1, 'rgt' => 4, 'level' => 1, 'path' => 'sport'],
                3 => ['lft' => 2, 'rgt' => 3, 'level' => 2, 'path' => 'sport/football'],
                4 => ['lft' => 5, 'rgt' => 6, 'level' => 1, 'path' => 'culture'],
            ],
            $this->readTree()
        );
    }

    /**
     * @testdox  A leaf ends up with consecutive left and right values
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testALeafGetsConsecutiveLeftAndRight()
    {
        $this->seedTree();
        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        foreach ([3, 4] as $leafId) {
            $this->assertSame(1, $tree[$leafId]['rgt'] - $tree[$leafId]['lft'], 'Node ' . $leafId . ' is a leaf');
        }
    }

    /**
     * @testdox  Every subtree is contained in the interval of its parent
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEverySubtreeIsContainedInItsParentInterval()
    {
        $this->seedTree();
        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        foreach ([2 => 1, 3 => 2, 4 => 1] as $child => $parent) {
            $this->assertGreaterThan($tree[$parent]['lft'], $tree[$child]['lft']);
            $this->assertLessThan($tree[$parent]['rgt'], $tree[$child]['rgt']);
            $this->assertSame($tree[$parent]['level'] + 1, $tree[$child]['level']);
        }
    }

    /**
     * @testdox  The numbering is dense
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTheNumberingIsDense()
    {
        $this->insertNode(1, 0, 'root');

        for ($i = 2; $i <= 11; $i++) {
            $this->insertNode($i, 1, 'child-' . $i, $i);
        }

        $this->table->rebuild(1, 0, 0, '');

        $used = [];

        foreach ($this->readTree() as $node) {
            $used[] = $node['lft'];
            $used[] = $node['rgt'];
        }

        sort($used);

        // Ten leaves under one root occupy exactly the numbers 0 to 21, each once.
        $this->assertSame(range(0, 21), $used);
    }

    /**
     * @testdox  The path is built from the ancestor aliases and never contains the start node alias
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testThePathIsBuiltFromTheAncestorAliases()
    {
        $this->insertNode(1, 0, 'root');
        $this->insertNode(2, 1, 'one', 1);
        $this->insertNode(3, 2, 'two', 1);
        $this->insertNode(4, 3, 'three', 1);
        $this->insertNode(5, 4, 'four', 1);

        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        // The start node is handed an empty path, so its own alias never enters any descendant path.
        $this->assertSame('', $tree[1]['path']);
        $this->assertSame('one', $tree[2]['path']);
        $this->assertSame('one/two', $tree[3]['path']);
        $this->assertSame('one/two/three', $tree[4]['path']);
        $this->assertSame('one/two/three/four', $tree[5]['path']);

        $this->assertSame(4, $tree[5]['level']);
    }

    /**
     * @testdox  Siblings are ordered by the ordering column when the table has one
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsAreOrderedByTheOrderingColumn()
    {
        $this->insertNode(1, 0, 'root');

        // Insertion order is alpha, beta, gamma. The ordering column asks for the reverse.
        $this->insertNode(2, 1, 'alpha', 3);
        $this->insertNode(3, 1, 'beta', 2);
        $this->insertNode(4, 1, 'gamma', 1);

        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        $this->assertSame(1, $tree[4]['lft'], 'gamma has the lowest ordering and comes first');
        $this->assertSame(3, $tree[3]['lft']);
        $this->assertSame(5, $tree[2]['lft']);
    }

    /**
     * @testdox  Siblings with an equal ordering fall back to the primary key
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsWithEqualOrderingFallBackToTheKey()
    {
        $this->insertNode(1, 0, 'root');
        $this->insertNode(7, 1, 'seven', 5);
        $this->insertNode(3, 1, 'three', 5);
        $this->insertNode(5, 1, 'five', 5);

        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        // All three share an ordering, so the id decides and the result is stable across runs.
        $this->assertSame(1, $tree[3]['lft']);
        $this->assertSame(3, $tree[5]['lft']);
        $this->assertSame(5, $tree[7]['lft']);
    }

    /**
     * @testdox  Siblings are ordered by lft when the table has no ordering column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsAreOrderedByLeftWhenThereIsNoOrderingColumn()
    {
        $this->insertNode(1, 0, 'root', 0, '#__testnestedplain');
        $this->insertNode(2, 1, 'alpha', 0, '#__testnestedplain');
        $this->insertNode(3, 1, 'beta', 0, '#__testnestedplain');

        // insertNode() writes the same garbage lft to every row, so give them a defined order.
        $db = $this->getDBDriver();
        $db->setQuery('UPDATE ' . $db->quoteName('#__testnestedplain') . ' SET ' . $db->quoteName('lft') . ' = 20 WHERE ' . $db->quoteName('id') . ' = 2')->execute();
        $db->setQuery('UPDATE ' . $db->quoteName('#__testnestedplain') . ' SET ' . $db->quoteName('lft') . ' = 10 WHERE ' . $db->quoteName('id') . ' = 3')->execute();

        $table = new Nested('#__testnestedplain', 'id', $db, new Dispatcher());
        $table->rebuild(1, 0, 0, '');

        $tree = $this->readTree('#__testnestedplain');

        // beta had the lower lft, so it is rebuilt as the first sibling.
        $this->assertSame(1, $tree[3]['lft']);
        $this->assertSame(3, $tree[2]['lft']);
    }

    /**
     * @testdox  rebuild() is idempotent
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildIsIdempotent()
    {
        $this->seedTree();

        $this->table->rebuild(1, 0, 0, '');
        $first = $this->readTree();

        $this->assertSame(8, $this->table->rebuild(1, 0, 0, ''));
        $this->assertSame($first, $this->readTree());
    }

    /**
     * @testdox  Rebuilding a subtree renumbers only that subtree
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildingASubtreeLeavesTheRestAlone()
    {
        $this->seedTree();
        $this->table->rebuild(1, 0, 0, '');

        $before = $this->readTree();

        // Renumber the "sport" subtree in place, the way TagModel::save() does it.
        $this->table->rebuild(2, $before[2]['lft'], $before[2]['level'], $before[2]['path']);

        $after = $this->readTree();

        $this->assertSame($before[2], $after[2], 'The subtree root keeps its numbering');
        $this->assertSame($before[3], $after[3], 'The subtree child keeps its numbering');
        $this->assertSame($before[1], $after[1], 'The tree root is untouched');
        $this->assertSame($before[4], $after[4], 'The sibling subtree is untouched');
    }

    /**
     * @testdox  Rebuilding a subtree writes the path it is handed onto the subtree root
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRebuildingASubtreeWritesTheGivenPathOntoItsRoot()
    {
        $this->seedTree();
        $this->table->rebuild(1, 0, 0, '');

        /*
         * The $path argument describes the node being processed, not its children, so a caller that
         * passes the wrong value silently rewrites the subtree root's own path. This is why
         * TagModel::save() calls rebuildPath() first and feeds the result into rebuild().
         */
        $this->table->rebuild(2, 1, 1, 'renamed');

        $tree = $this->readTree();

        $this->assertSame('renamed', $tree[2]['path']);
        $this->assertSame('renamed/football', $tree[3]['path']);
    }

    /**
     * @testdox  Nodes that hang off a missing parent are never renumbered
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOrphanedNodesAreNotRenumbered()
    {
        $this->seedTree();

        // Node 5 points at a parent that does not exist.
        $this->insertNode(5, 4242, 'orphan', 1);

        $this->table->rebuild(1, 0, 0, '');

        $tree = $this->readTree();

        /*
         * rebuild() walks down from the start node, so a node that is not reachable that way keeps
         * whatever was in its nested set columns. Here that is the garbage insertNode() wrote, and it
         * overlaps the freshly numbered tree. rebuild() repairs a reachable tree, it does not detect
         * a broken one.
         */
        $this->assertSame(999, $tree[5]['lft']);
        $this->assertSame(999, $tree[5]['rgt']);
        $this->assertSame('garbage', $tree[5]['path']);
    }

    /**
     * @testdox  A cycle in the adjacency list is reported instead of looping forever
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testACycleIsReported()
    {
        $this->insertNode(1, 0, 'root');
        $this->insertNode(2, 1, 'two', 1);
        $this->insertNode(3, 2, 'three', 1);

        // Point the root at its own grandchild.
        $db = $this->getDBDriver();
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__testnested') . ' SET ' . $db->quoteName('parent_id')
            . ' = 3 WHERE ' . $db->quoteName('id') . ' = 1'
        )->execute();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/cycle in the adjacency list/');

        $this->table->rebuild(1, 0, 0, '');
    }

    /**
     * @testdox  A tree of only a start node is numbered zero to one
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testATreeOfOnlyOneNode()
    {
        $this->insertNode(1, 0, 'root');

        $this->assertSame(2, $this->table->rebuild(1, 0, 0, ''));

        $this->assertSame(
            [1 => ['lft' => 0, 'rgt' => 1, 'level' => 0, 'path' => '']],
            $this->readTree()
        );
    }

    /**
     * @testdox  A table without alias, level and path columns is rebuilt from lft and rgt alone
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testATableWithoutAliasLevelOrPathColumns()
    {
        $db = $this->getDBDriver();

        foreach ([[1, 0], [2, 1], [3, 2], [4, 1]] as [$id, $parentId]) {
            $db->setQuery(
                'INSERT INTO ' . $db->quoteName('#__testnestedminimal') . ' SET '
                . $db->quoteName('id') . ' = ' . $id . ', ' . $db->quoteName('parent_id') . ' = ' . $parentId
                . ', ' . $db->quoteName('lft') . ' = 999, ' . $db->quoteName('rgt') . ' = 999'
            )->execute();
        }

        // Only the columns that exist are touched, so an assets style table can be rebuilt too.
        $table = new Nested('#__testnestedminimal', 'id', $db, new Dispatcher());

        $this->assertSame(8, $table->rebuild(1, 0, 0, ''));

        $rows = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['id', 'lft', 'rgt']))
                ->from($db->quoteName('#__testnestedminimal'))
                ->order($db->quoteName('id') . ' ASC')
        )->loadAssocList('id');

        $this->assertSame([0, 7], [(int) $rows[1]['lft'], (int) $rows[1]['rgt']]);
        $this->assertSame([1, 4], [(int) $rows[2]['lft'], (int) $rows[2]['rgt']]);
        $this->assertSame([2, 3], [(int) $rows[3]['lft'], (int) $rows[3]['rgt']]);
        $this->assertSame([5, 6], [(int) $rows[4]['lft'], (int) $rows[4]['rgt']]);
    }

    /**
     * @testdox  A deep chain does not exhaust the call stack
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testADeepChainIsHandledIteratively()
    {
        $db    = $this->getDBDriver();
        $depth = 400;

        // The minimal table has no path column, so the chain can be deeper than a path would allow.
        for ($i = 1; $i <= $depth; $i++) {
            $db->setQuery(
                'INSERT INTO ' . $db->quoteName('#__testnestedminimal') . ' SET '
                . $db->quoteName('id') . ' = ' . $i . ', ' . $db->quoteName('parent_id') . ' = ' . ($i - 1)
                . ', ' . $db->quoteName('lft') . ' = 999, ' . $db->quoteName('rgt') . ' = 999'
            )->execute();
        }

        $table = new Nested('#__testnestedminimal', 'id', $db, new Dispatcher());

        $this->assertSame($depth * 2, $table->rebuild(1, 0, 0, ''));

        $deepest = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName(['lft', 'rgt']))
                ->from($db->quoteName('#__testnestedminimal'))
                ->where($db->quoteName('id') . ' = ' . $depth)
        )->loadAssoc();

        $this->assertSame($depth - 1, (int) $deepest['lft']);
        $this->assertSame($depth, (int) $deepest['rgt']);
    }

    /**
     * @testdox  Without arguments rebuild() starts at the virtual parent rather than at the root
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoArgumentFormStartsAtTheVirtualParent()
    {
        $this->seedTree();

        // A table that has not been loaded has parent_id = null, which is cast to the virtual node 0.
        $this->assertSame(10, $this->table->rebuild());

        $tree = $this->readTree();

        /*
         * The real root is treated as a child of node 0, so it is numbered one level down: lft 1
         * instead of 0, level 1 instead of 0, and its own alias enters every path.
         *
         * This differs from the released behaviour, where the no argument form resolves the start
         * node through getRootId() and the root keeps lft = 0, level = 0 and an empty path. It also
         * defeats the second detection strategy in getRootId(), which looks for a unique row with
         * lft = 0 and would now find none. Passing the root id explicitly avoids all of this.
         */
        $this->assertSame(1, $tree[1]['lft']);
        $this->assertSame(1, $tree[1]['level']);
        $this->assertSame('root', $tree[1]['path']);
        $this->assertSame('root/sport', $tree[2]['path']);
    }
}
