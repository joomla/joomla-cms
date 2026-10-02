<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  Helper
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Libraries\Cms\Helper;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\User\User;
use Joomla\Component\Tags\Administrator\Table\TagTable;
use Joomla\Event\Dispatcher;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Characterization tests for the parts of the tags helper that decide which tags belong to an item.
 *
 * @package     Joomla.IntegrationTest
 * @subpackage  Helper
 * @since       __DEPLOY_VERSION__
 */
class TagsHelperTest extends IntegrationTestCase implements DBTestInterface
{
    use DBTestTrait;

    /**
     * The globals the test replaced, so they can be put back.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $originalGlobals = [];

    /**
     * Id of the top level "sport" tag.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    private $sportId;

    /**
     * Id of the "football" tag, a child of "sport".
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    private $footballId;

    /**
     * Id of the top level "culture" tag.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    private $cultureId;

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
     * Build the environment the helper expects and seed a small tag tree.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the language while there is no application, see TagTableTest for the reasoning.
        Factory::getLanguage();

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);

        $this->originalGlobals = [
            'application' => Factory::$application,
            'database'    => Factory::$database,
            'components'  => $components->getValue(),
        ];

        $record = new ComponentRecord(['id' => 42, 'option' => 'com_tags', 'enabled' => 1]);
        $record->setParams(new Registry(['tag_list_language_filter' => 'all']));
        $components->setValue(null, ['com_tags' => $record]);

        $db = $this->getDBDriver();

        // The helper reaches for the global database rather than an injected one.
        Factory::$database = $db;

        $user = $this->createStub(User::class);
        $user->method('getAuthorisedViewLevels')->willReturn([1]);
        $user->method('authorise')->willReturn(true);

        $session = $this->createStub(SessionInterface::class);
        $session->method('get')->willReturn($user);

        $factory = $this->createStub(MVCFactoryInterface::class);
        $factory->method('createTable')->willReturnCallback(
            fn () => new TagTable($this->getDBDriver(), new Dispatcher())
        );

        $component = $this->createStub(MVCComponent::class);
        $component->method('getMVCFactory')->willReturn($factory);

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('get')->willReturn(1);
        $app->method('getDispatcher')->willReturn(new Dispatcher());
        $app->method('getSession')->willReturn($session);
        $app->method('bootComponent')->willReturn($component);

        Factory::$application = $app;

        $this->resetTables();
        $this->seedTagTree();
    }

    /**
     * Restore the globals.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $components->setValue(null, $this->originalGlobals['components']);

        Factory::$application = $this->originalGlobals['application'];
        Factory::$database    = $this->originalGlobals['database'];

        parent::tearDown();
    }

    /**
     * Empty the tables this test case writes to and restore the root tag.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resetTables(): void
    {
        $db = $this->getDBDriver();

        $db->setQuery('DELETE FROM ' . $db->quoteName('#__contentitem_tag_map'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__content_types'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__tags'))->execute();

        // One content type is enough, getTagItemsQuery() limits the result to the known type aliases.
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__content_types')
            . ' (' . $db->quoteName('type_id') . ', ' . $db->quoteName('type_title') . ', ' . $db->quoteName('type_alias')
            . ', ' . $db->quoteName('table') . ', ' . $db->quoteName('rules') . ', ' . $db->quoteName('field_mappings')
            . ', ' . $db->quoteName('router') . ')'
            . " VALUES (1, 'Article', 'com_content.article', '', '', '', '')"
        )->execute();

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
     * Store a tag under the given parent and hand back its id.
     *
     * @param   string   $title     The tag title
     * @param   string   $alias     The tag alias
     * @param   integer  $parentId  The id of the parent tag
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function storeTag(string $title, string $alias, int $parentId = 1): int
    {
        $table = new TagTable($this->getDBDriver(), new Dispatcher());
        $table->setCurrentUser(new User());

        $table->title            = $title;
        $table->alias            = $alias;
        $table->description      = '';
        $table->note             = '';
        $table->created_by_alias = '';
        $table->version          = 1;
        $table->published        = 1;
        $table->access           = 1;
        $table->language         = '*';

        $table->setLocation($parentId, 'last-child');

        $table->check();
        $table->store();

        return (int) $table->id;
    }

    /**
     * Seed a two level tag tree: sport, sport/football and a separate culture tag.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedTagTree(): void
    {
        $this->sportId    = $this->storeTag('Sport', 'sport');
        $this->footballId = $this->storeTag('Football', 'football', $this->sportId);
        $this->cultureId  = $this->storeTag('Culture', 'culture');
    }

    /**
     * Map a content item to a tag.
     *
     * @param   integer  $itemId  The content item id
     * @param   integer  $tagId   The tag id
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function tagItem(int $itemId, int $tagId): void
    {
        $db = $this->getDBDriver();

        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__contentitem_tag_map')
            . ' (' . $db->quoteName('type_alias') . ', ' . $db->quoteName('core_content_id')
            . ', ' . $db->quoteName('content_item_id') . ', ' . $db->quoteName('tag_id')
            . ', ' . $db->quoteName('tag_date') . ', ' . $db->quoteName('type_id') . ')'
            . " VALUES ('com_content.article', " . $itemId . ', ' . $itemId . ', ' . $tagId
            . ", '2026-01-01 00:00:00', 1)"
        )->execute();
    }

    /**
     * @testdox  getTagTreeArray returns a tag together with all of its descendants
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTagTreeArrayReturnsTheWholeSubtree()
    {
        $helper = new TagsHelper();
        $result = [];

        $helper->getTagTreeArray($this->sportId, $result);

        $this->assertSame([$this->sportId, $this->footballId], array_map('intval', $result));
    }

    /**
     * @testdox  getTagTreeArray returns only the tag itself when it has no children
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTagTreeArrayOfALeafReturnsOnlyTheLeaf()
    {
        $helper = new TagsHelper();
        $result = [];

        $helper->getTagTreeArray($this->cultureId, $result);

        $this->assertSame([$this->cultureId], array_map('intval', $result));
    }

    /**
     * @testdox  getTagTreeArray appends to the array it is handed rather than replacing it
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTagTreeArrayAppendsToTheGivenArray()
    {
        $helper = new TagsHelper();
        $result = [];

        $helper->getTagTreeArray($this->sportId, $result);
        $helper->getTagTreeArray($this->cultureId, $result);

        /*
         * Characterization: the subtree of "sport" already contains "sport" itself, so a caller that
         * merges the result with the tags it asked for, as getTagItemsQuery() does, ends up with
         * duplicates and has to run array_unique over them.
         */
        $this->assertSame(
            [$this->sportId, $this->footballId, $this->cultureId],
            array_map('intval', $result)
        );
    }

    /**
     * @testdox  getItemTags returns only the tags assigned to the item and never the ancestors
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetItemTagsDoesNotIncludeAncestorTags()
    {
        // The article is tagged with the child tag only.
        $this->tagItem(500, $this->footballId);

        $helper = new TagsHelper();
        $tags   = $helper->getItemTags('com_content.article', 500);

        /*
         * The parent tag "sport" is not returned. A tag is only ever on an item if it was assigned to
         * it directly, tagging is not inherited up or down the tree.
         */
        $this->assertSame([$this->footballId], array_map(fn ($tag) => (int) $tag->id, $tags));
    }

    /**
     * @testdox  getItemTags returns every directly assigned tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetItemTagsReturnsEveryAssignedTag()
    {
        $this->tagItem(500, $this->footballId);
        $this->tagItem(500, $this->cultureId);

        $helper = new TagsHelper();
        $tags   = $helper->getItemTags('com_content.article', 500);

        $ids = array_map(fn ($tag) => (int) $tag->id, $tags);
        sort($ids);

        $this->assertSame([$this->footballId, $this->cultureId], $ids);
    }

    /**
     * @testdox  getItemTags ignores an unpublished tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetItemTagsIgnoresUnpublishedTags()
    {
        $this->tagItem(500, $this->footballId);

        $db = $this->getDBDriver();
        $db->setQuery(
            'UPDATE ' . $db->quoteName('#__tags') . ' SET ' . $db->quoteName('published') . ' = 0'
            . ' WHERE ' . $db->quoteName('id') . ' = ' . $this->footballId
        )->execute();

        $helper = new TagsHelper();

        $this->assertSame([], $helper->getItemTags('com_content.article', 500));
    }

    /**
     * @testdox  createTagsFromField creates a tag for every #new# entry as a child of the root tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCreateTagsFromFieldCreatesNewTagsBelowTheRoot()
    {
        $helper = new TagsHelper();
        $result = $helper->createTagsFromField(['#new#Winter Sport', (string) $this->cultureId]);

        $this->assertCount(2, $result);
        $this->assertSame($this->cultureId, $result[1], 'An existing id is passed through unchanged');

        $db  = $this->getDBDriver();
        $new = $db->setQuery(
            $db->createQuery()
                ->select('*')
                ->from($db->quoteName('#__tags'))
                ->where($db->quoteName('id') . ' = ' . (int) $result[0])
        )->loadObject();

        /*
         * Characterization: a tag created from the tag field is always attached to the root tag and
         * therefore always ends up at the top level, whatever the item being edited is tagged with. The
         * path is set to the bare alias with the comment that "autogenerated tags have always level 1".
         */
        $this->assertSame('Winter Sport', $new->title);
        $this->assertSame(1, (int) $new->parent_id);
        $this->assertSame(1, (int) $new->level);
        $this->assertSame('winter-sport', $new->alias);
        $this->assertSame('winter-sport', $new->path);
    }

    /**
     * @testdox  createTagsFromField reuses an existing tag when the title already exists
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCreateTagsFromFieldReusesAnExistingTitle()
    {
        $helper = new TagsHelper();
        $result = $helper->createTagsFromField(['#new#Culture']);

        /*
         * Characterization: the lookup is by title over the whole table, so a new tag typed below one
         * parent silently reuses a tag of the same name that lives somewhere else entirely.
         */
        $this->assertSame([$this->cultureId], $result);
    }

    /**
     * @testdox  createTagsFromField answers with nothing for an empty field
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCreateTagsFromFieldReturnsNothingForAnEmptyField()
    {
        $helper = new TagsHelper();

        // Characterization: the early return has no value, so the caller gets null rather than an array.
        $this->assertNull($helper->createTagsFromField([]));
        $this->assertNull($helper->createTagsFromField(['']));
    }

    /**
     * @testdox  postStore does nothing when there are no tags and nothing changed
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPostStoreIsANoOpWithoutTags()
    {
        $table             = new TagTable($this->getDBDriver(), new Dispatcher());
        $helper            = new TagsHelper();
        $helper->typeAlias = 'com_content.article';

        // An unset newTags property and an empty array are treated the same way.
        $this->assertTrue($helper->postStore($table, []));

        $table->newTags = [];
        $this->assertTrue($helper->postStore($table, []));
    }

    /**
     * @testdox  postStoreProcess forwards to postStore and is deprecated
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testPostStoreProcessForwardsToPostStore()
    {
        $table             = new TagTable($this->getDBDriver(), new Dispatcher());
        $helper            = new TagsHelper();
        $helper->typeAlias = 'com_content.article';

        $deprecations = [];

        set_error_handler(
            function ($errno, $errstr) use (&$deprecations) {
                $deprecations[] = $errstr;

                return true;
            },
            E_USER_DEPRECATED
        );

        try {
            $helper->postStoreProcess($table, []);
        } finally {
            restore_error_handler();
        }

        $this->assertNotEmpty($deprecations, 'postStoreProcess() has to raise a deprecation');
        $this->assertStringContainsString('postStoreProcess', $deprecations[0]);

        /*
         * Characterization: postStoreProcess() throws away the boolean that postStore() answers with, so
         * a caller on the deprecated method cannot tell whether the tags were written.
         */
        $this->assertNull($helper->postStoreProcess($table, []));
    }

    /**
     * @testdox  getTagItemsQuery joins the categories table and lets uncategorised items through
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetTagItemsQueryJoinsCategories()
    {
        $helper = new TagsHelper();
        $query  = (string) $helper->getTagItemsQuery($this->footballId);

        // The join exists so that items in an unpublished category can be filtered out.
        $this->assertStringContainsString('#__categories', $query);

        /*
         * Characterization: a content type whose table has no category column stores 0 in core_catid,
         * and the explicit "core_catid = 0" branch is what keeps those items in the result. Without it
         * every uncategorised item would be dropped by the join.
         */
        $this->assertMatchesRegularExpression('/core_catid.{0,10}=\s*0/', $query);
    }
}
