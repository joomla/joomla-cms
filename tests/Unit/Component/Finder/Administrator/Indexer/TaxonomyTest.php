<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\DatabaseQuery;
use Joomla\DI\Container;
use Joomla\Event\Dispatcher;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Indexer\Taxonomy
 *
 * @since  6.2.1
 */
#[CoversMethod(Taxonomy::class, 'removeOrphanNodes')]
class TaxonomyTest extends UnitTestCase
{
    /**
     * @var \Joomla\CMS\Application\CMSApplicationInterface|null
     */
    private $previousApplication;

    /**
     * @var \Joomla\DI\Container|null
     */
    private $previousContainer;

    /**
     * @return void
     *
     * @since   6.2.1
     */
    protected function setUp(): void
    {
        $this->previousApplication = Factory::$application;
        $this->previousContainer   = Factory::$container;
    }

    /**
     * @return void
     *
     * @since   6.2.1
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->previousApplication;
        Factory::$container   = $this->previousContainer;
    }

    /**
     * Returns a real query object so that bound values and placeholders behave as in production.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @return  DatabaseQuery
     *
     * @since   6.2.1
     */
    private function createQuery(DatabaseInterface $db): DatabaseQuery
    {
        return new class ($db) extends DatabaseQuery {
            public function groupConcat($expression, $separator = ',')
            {
                return $expression;
            }

            public function processLimit($query, $limit, $offset = 0)
            {
                return $query;
            }
        };
    }

    /**
     * Orphan nodes chained over several levels are removed in several passes. Every pass has to
     * execute a delete query whose bound values match its placeholders, otherwise the PDO driver
     * fails with SQLSTATE HY093.
     *
     * @return void
     *
     * @since   6.2.1
     */
    public function testRemoveOrphanNodesBindsOnlyCurrentPassValues(): void
    {
        // Each pass finds the parents of the nodes deleted by the previous one
        $passes   = [[3, 4], [2], [1]];
        $executed = [];

        $db = $this->createStub(DatabaseInterface::class);
        $db->method('quoteName')->willReturnArgument(0);
        $db->method('getServerType')->willReturn('mysql');
        $db->method('getName')->willReturn('mysql');
        $db->method('getTableColumns')->willReturn(array_fill_keys(['id', 'parent_id', 'lft', 'rgt', 'level', 'path', 'title', 'alias', 'state', 'access'], 'int'));
        $db->method('getAffectedRows')->willReturn(1);
        $db->method('createQuery')->willReturnCallback(fn() => $this->createQuery($db));

        $current = null;
        $db->method('setQuery')->willReturnCallback(
            function ($query) use ($db, &$current) {
                $current = $query;

                return $db;
            }
        );
        $db->method('loadObjectList')->willReturn([]);
        $db->method('loadColumn')->willReturnCallback(
            function () use (&$current, &$passes) {
                // Only the orphan lookup is answered, the nested set rebuild finds nothing
                return str_contains((string) $current, 'finder_taxonomy_map') ? (array) array_shift($passes) : [];
            }
        );
        $db->method('execute')->willReturnCallback(
            function () use (&$current, &$executed) {
                if ($current instanceof DatabaseQuery && str_starts_with(trim((string) $current), 'DELETE')) {
                    $executed[] = [
                        'placeholders' => preg_match_all('/:\w+/', (string) $current),
                        'bound'        => \count($current->getBounded()),
                    ];
                }

                return true;
            }
        );

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('get')->willReturn(1);
        $app->method('getDispatcher')->willReturn(new Dispatcher());

        $container = new Container();
        $container->set(DatabaseInterface::class, $db);
        $container->alias('DatabaseDriver', DatabaseInterface::class);

        Factory::$application = $app;
        Factory::$container   = $container;

        Taxonomy::removeOrphanNodes();

        $this->assertCount(3, $executed, 'One delete query is expected per pass');

        foreach ($executed as $pass => $delete) {
            $this->assertSame(
                $delete['placeholders'],
                $delete['bound'],
                'Pass ' . ($pass + 1) . ' must only bind the values of its own placeholders'
            );
        }
    }
}
