<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  com_tags
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Component\Tags\Administrator\Table;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Component\Tags\Administrator\Table\TagTable;
use Joomla\Event\Dispatcher;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Characterization tests for the nesting and alias handling of the tag table.
 *
 * These tests describe the behaviour of the table as it is today. Where the behaviour looks wrong it is
 * marked with a "Characterization:" comment and left alone.
 *
 * @package     Joomla.IntegrationTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class TagTableTest extends IntegrationTestCase implements DBTestInterface
{
    use DBTestTrait;

    /**
     * The value Factory::$application had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalApplication;

    /**
     * Retrieve the list of schemas to load for this test case.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getSchemasToLoad(): array
    {
        return ['framework.sql', 'tags.sql'];
    }

    /**
     * Reset the tags table to a single root node before every test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Warm the global language before the application is replaced. Factory::getDate(), which the
         * table uses to stamp the created and modified times, resolves the language, and the language is
         * built from the application configuration as soon as an application is present. Resolving it
         * while there is no application keeps the stub below as small as it needs to be.
         */
        Factory::getLanguage();

        /*
         * Table::__construct() reads the default access level off the application, so an application has
         * to be present. It also falls back to the application dispatcher whenever a table is built
         * without one, which TagTable::store() does when it checks the alias for duplicates.
         */
        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('get')->willReturn(1);
        $app->method('getDispatcher')->willReturn(new Dispatcher());

        $this->originalApplication = Factory::$application;
        Factory::$application      = $app;

        $db = $this->getDBDriver();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__tags'))->execute();
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__tags')
            . ' (' . $db->quoteName('id') . ', ' . $db->quoteName('parent_id') . ', ' . $db->quoteName('lft')
            . ', ' . $db->quoteName('rgt') . ', ' . $db->quoteName('level') . ', ' . $db->quoteName('path')
            . ', ' . $db->quoteName('title') . ', ' . $db->quoteName('alias') . ', ' . $db->quoteName('note')
            . ', ' . $db->quoteName('description') . ', ' . $db->quoteName('published') . ', ' . $db->quoteName('access')
            . ', ' . $db->quoteName('params') . ', ' . $db->quoteName('metadesc') . ', ' . $db->quoteName('metakey')
            . ', ' . $db->quoteName('metadata') . ', ' . $db->quoteName('created_user_id') . ', ' . $db->quoteName('created_time')
            . ', ' . $db->quoteName('created_by_alias') . ', ' . $db->quoteName('modified_user_id')
            . ', ' . $db->quoteName('modified_time') . ', ' . $db->quoteName('images') . ', ' . $db->quoteName('urls')
            . ', ' . $db->quoteName('hits') . ', ' . $db->quoteName('language') . ', ' . $db->quoteName('version') . ')'
            . " VALUES (1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 1, '{}', '', '', '{}', 42,"
            . " '2026-01-01 00:00:00', '', 42, '2026-01-01 00:00:00', '{}', '{}', 0, '*', 1)"
        )->execute();
    }

    /**
     * Restore the application.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->originalApplication;

        parent::tearDown();
    }

    /**
     * Build an unstored tag positioned as the last child of the given parent.
     *
     * @param   string   $title     The tag title
     * @param   string   $alias     The tag alias
     * @param   integer  $parentId  The id of the parent tag
     *
     * @return  TagTable
     *
     * @since   __DEPLOY_VERSION__
     */
    private function makeTag(string $title, string $alias, int $parentId = 1): TagTable
    {
        $table = new TagTable($this->getDBDriver(), new Dispatcher());
        $table->setCurrentUser(new User());

        $table->title       = $title;
        $table->alias       = $alias;
        $table->description = '';
        $table->published   = 1;
        $table->access      = 1;
        $table->language    = '*';

        /*
         * check() defaults the params, metadata, urls and images columns but not these. They are not
         * nullable in the schema, and TagTable::store() passes $updateNulls = true, so an update of a
         * tag that never had them set fails at the database. In production they arrive through the form.
         */
        $table->note             = '';
        $table->created_by_alias = '';
        $table->version          = 1;

        $table->setLocation($parentId, 'last-child');

        return $table;
    }

    /**
     * Build and store a tag, asserting that both steps succeed.
     *
     * @param   string   $title     The tag title
     * @param   string   $alias     The tag alias
     * @param   integer  $parentId  The id of the parent tag
     *
     * @return  TagTable
     *
     * @since   __DEPLOY_VERSION__
     */
    private function storeTag(string $title, string $alias, int $parentId = 1): TagTable
    {
        $table = $this->makeTag($title, $alias, $parentId);

        $this->assertTrue($table->check(), 'The tag "' . $alias . '" has to pass check()');
        $this->assertTrue($table->store(), 'The tag "' . $alias . '" has to store: ' . $table->getError());

        return $table;
    }

    /**
     * Read a single column of a stored tag straight from the database.
     *
     * @param   integer  $id      The tag id
     * @param   string   $column  The column to read
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    private function readColumn(int $id, string $column)
    {
        $db = $this->getDBDriver();

        return $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName($column))
                ->from($db->quoteName('#__tags'))
                ->where($db->quoteName('id') . ' = ' . $id)
        )->loadResult();
    }

    /**
     * @testdox  A tag can be stored under any parent, however deeply nested
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagCanBeStoredUnderAnArbitraryParent()
    {
        $first  = $this->storeTag('First', 'first');
        $second = $this->storeTag('Second', 'second', (int) $first->id);
        $third  = $this->storeTag('Third', 'third', (int) $second->id);
        $fourth = $this->storeTag('Fourth', 'fourth', (int) $third->id);

        $this->assertSame(1, (int) $first->level);
        $this->assertSame(2, (int) $second->level);
        $this->assertSame(3, (int) $third->level);
        $this->assertSame(4, (int) $fourth->level);

        $this->assertSame((int) $third->id, (int) $fourth->parent_id);

        // The nested set has to actually contain the subtree.
        $this->assertLessThan((int) $fourth->lft, (int) $first->lft);
        $this->assertGreaterThan((int) $fourth->rgt, (int) $this->readColumn((int) $first->id, 'rgt'));
    }

    /**
     * @testdox  Two sibling tags may not share an alias
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSiblingsMayNotShareAnAlias()
    {
        $this->storeTag('News', 'news');

        $duplicate = $this->makeTag('News again', 'news');

        $this->assertTrue($duplicate->check());
        $this->assertFalse($duplicate->store(), 'A sibling with the same alias must not be stored');
    }

    /**
     * @testdox  The alias uniqueness check is global and is not scoped to the siblings of a tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAliasUniquenessIsGlobalAndNotScopedToSiblings()
    {
        $sport   = $this->storeTag('Sport', 'sport');
        $culture = $this->storeTag('Culture', 'culture');

        $this->storeTag('Football', 'football', (int) $sport->id);

        $secondFootball = $this->makeTag('Football', 'football', (int) $culture->id);

        /*
         * Characterization: this looks like it should be allowed, because the two tags are in different
         * branches of the tree and a nested taxonomy would normally scope an alias to its siblings.
         * It is not allowed. TagTable::store() loads any tag with the same alias regardless of its
         * parent, and TagModel::generateNewTitle() ignores the $parentId argument it is handed.
         *
         * This is load bearing for anything that wants to derive URLs from the tag tree: because the
         * alias is unique for the whole site, a single alias segment already identifies a tag and no
         * path is needed to disambiguate it.
         */
        $this->assertTrue($secondFootball->check());
        $this->assertFalse(
            $secondFootball->store(),
            'The alias check is currently global, so a duplicate alias under a different parent is rejected'
        );
        $this->assertNotEmpty($secondFootball->getError());
    }

    /**
     * @testdox  Storing a tag does not compose its path
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testStoreDoesNotComposeThePath()
    {
        $parent = $this->storeTag('Sport', 'sport');
        $child  = $this->storeTag('Football', 'football', (int) $parent->id);

        /*
         * Characterization: the path is not maintained by the table at all. check() only defaults it to
         * an empty string and store() writes whatever is there. Composing the path is the job of
         * TagModel::save(), which calls rebuildPath() and rebuild() after storing.
         */
        $this->assertSame('', $this->readColumn((int) $child->id, 'path'));
    }

    /**
     * @testdox  The path is composed from the aliases of all ancestors
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPathIsComposedFromTheAncestorAliases()
    {
        $sport    = $this->storeTag('Sport', 'sport');
        $football = $this->storeTag('Football', 'football', (int) $sport->id);
        $rules    = $this->storeTag('Rules', 'rules', (int) $football->id);

        $sport->rebuildPath((int) $sport->id);
        $football->rebuildPath((int) $football->id);
        $rules->rebuildPath((int) $rules->id);

        // The root node is stripped, every other ancestor contributes its alias.
        $this->assertSame('sport', $this->readColumn((int) $sport->id, 'path'));
        $this->assertSame('sport/football', $this->readColumn((int) $football->id, 'path'));
        $this->assertSame('sport/football/rules', $this->readColumn((int) $rules->id, 'path'));
    }

    /**
     * @testdox  Renaming the alias of a parent updates the paths of all its descendants
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRenamingAParentAliasUpdatesTheDescendantPaths()
    {
        $sport    = $this->storeTag('Sport', 'sport');
        $football = $this->storeTag('Football', 'football', (int) $sport->id);
        $rules    = $this->storeTag('Rules', 'rules', (int) $football->id);

        $sport->rebuildPath((int) $sport->id);
        $football->rebuildPath((int) $football->id);
        $rules->rebuildPath((int) $rules->id);

        // Rename the parent the way TagModel::save() does it.
        $sport->alias = 'athletics';
        $this->assertTrue($sport->store(), 'Renaming the alias has to store: ' . $sport->getError());
        $this->assertTrue($sport->rebuildPath((int) $sport->id));

        // rebuild() answers with the new right hand value of the subtree, not with a boolean.
        $this->assertNotFalse($sport->rebuild((int) $sport->id, (int) $sport->lft, (int) $sport->level, $sport->path));

        $this->assertSame('athletics', $this->readColumn((int) $sport->id, 'path'));
        $this->assertSame('athletics/football', $this->readColumn((int) $football->id, 'path'));
        $this->assertSame('athletics/football/rules', $this->readColumn((int) $rules->id, 'path'));
    }

    /**
     * @testdox  An empty alias falls back to the title
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmptyAliasFallsBackToTheTitle()
    {
        $table = $this->makeTag('Winter Sport', '');

        $this->assertTrue($table->check());
        $this->assertSame('winter-sport', $table->alias);
    }
}
