<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Tags\Site\Service;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Component\Tags\Site\Service\Router;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

/**
 * Fixtures for the com_tags router in the flat and the tree mode.
 *
 * The tag fixture is the structure the testing instructions of the pull request ask for, so that the
 * expectations here and the manual checks describe the same site:
 *
 * ```
 *  3 news
 *  5   sport
 *  7     football
 *  9   politics
 * 11 travel
 * 13   europe
 * ```
 *
 * The ids ascend in the order the tags are listed, which is what the ordering rules of both modes are
 * built on. As in the characterization fixtures, the router is built without invoking its constructor
 * and its collaborators are injected, so that the tests need neither a database nor an application.
 *
 * @since  __DEPLOY_VERSION__
 */
trait StructuredTagsRouterFixtureTrait
{
    use TagsRouterFixtureTrait;

    /**
     * The tag rows the database stub hands back, keyed by id.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $structuredTags = [
        3  => ['id' => 3,  'alias' => 'news',     'path' => 'news',                  'parent_id' => 1,  'level' => 1, 'lft' => 1,  'rgt' => 8],
        5  => ['id' => 5,  'alias' => 'sport',    'path' => 'news/sport',            'parent_id' => 3,  'level' => 2, 'lft' => 2,  'rgt' => 5],
        7  => ['id' => 7,  'alias' => 'football', 'path' => 'news/sport/football',   'parent_id' => 5,  'level' => 3, 'lft' => 3,  'rgt' => 4],
        9  => ['id' => 9,  'alias' => 'politics', 'path' => 'news/politics',         'parent_id' => 3,  'level' => 2, 'lft' => 6,  'rgt' => 7],
        11 => ['id' => 11, 'alias' => 'travel',   'path' => 'travel',                'parent_id' => 1,  'level' => 1, 'lft' => 9,  'rgt' => 12],
        13 => ['id' => 13, 'alias' => 'europe',   'path' => 'travel/europe',         'parent_id' => 11, 'level' => 2, 'lft' => 10, 'rgt' => 11],
    ];

    /**
     * The router the site router stub hands back, which records whether the URL was marked as tainted.
     *
     * @var    object
     * @since  __DEPLOY_VERSION__
     */
    protected $siteRouter;

    /**
     * The value Factory::$language had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalLanguage;

    /**
     * Seed the static state the router touches, including the language a 404 message is read from.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUpStructuredState(): void
    {
        $this->setUpStaticState();

        $this->originalLanguage = Factory::$language;
        Factory::$language      = $this->createStub(Language::class);
    }

    /**
     * Restore the static state seeded by setUpStructuredState().
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDownStructuredState(): void
    {
        Factory::$language = $this->originalLanguage;

        $this->tearDownStaticState();
    }

    /**
     * Set the mode of the tagging system.
     *
     * @param   ?string  $mode  One of the TagsHelper::MODE_* constants, or null to leave the parameter
     *                          out of the component parameters altogether
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setTagMode(?string $mode): void
    {
        $record = new ComponentRecord(['id' => 42, 'option' => 'com_tags', 'enabled' => 1]);
        $record->setParams(new Registry($mode === null ? [] : ['mode' => $mode]));

        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $components->setValue(null, ['com_tags' => $record]);
    }

    /**
     * Build a router for a site that reflects its tag structure in the URL.
     *
     * @param   string     $mode        One of the TagsHelper::MODE_* constants
     * @param   array      $menuItems   The com_tags menu items that exist on the site
     * @param   ?object    $active      The active menu item, if any
     * @param   array      $filters     The tags the URL carries as query filters
     *
     * @return  Router
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createStructuredRouter(
        string $mode,
        array $menuItems = [],
        $active = null,
        array $filters = []
    ): Router {
        $this->setTagMode($mode);

        $menu             = $this->createMenuStub($menuItems, $active);
        $this->siteRouter = $this->createRecordingSiteRouter();

        /*
         * CMSApplication::getRouter() is static, so a mock object refuses to answer it. The router only
         * ever asks its application for the menu, the language, the input and the site router, so a
         * small double answers everything it needs.
         */
        $app = new class ($menu, new Input($filters ? ['id' => $filters] : []), $this->siteRouter) {
            /**
             * @param  AbstractMenu  $menu    The menu of the site
             * @param  Input         $input   The input of the request
             * @param  object        $router  The site router
             */
            public function __construct(private $menu, private $input, private $router)
            {
            }

            /**
             * @return  AbstractMenu
             */
            public function getMenu()
            {
                return $this->menu;
            }

            /**
             * @param   string  $name     The name of the setting
             * @param   mixed   $default  The value to answer with when the setting is unknown
             *
             * @return  mixed
             */
            public function get($name, $default = null)
            {
                return $name === 'language' ? 'en-GB' : $default;
            }

            /**
             * @return  Input
             */
            public function getInput()
            {
                return $this->input;
            }

            /**
             * @return  object
             */
            public function getRouter()
            {
                return $this->router;
            }
        };

        $reflection = new \ReflectionClass(Router::class);
        $router     = $reflection->newInstanceWithoutConstructor();

        $router->app  = $app;
        $router->menu = $menu;

        $this->setProperty($router, 'db', $this->createStructuredTagDatabaseStub());
        $this->setProperty($router, 'sefparams', new Registry([]));
        $this->setProperty($router, 'lookup', []);

        $method = $reflection->getMethod('buildLookup');
        $method->setAccessible(true);
        $method->invoke($router);

        return $router;
    }

    /**
     * Build a site router that records whether the URL was marked as tainted.
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createRecordingSiteRouter()
    {
        return new class () extends \Joomla\CMS\Router\Router {
            /**
             * Whether setTainted() was called.
             *
             * @var  boolean
             */
            public $wasTainted = false;

            /**
             * Record that the URL was marked as tainted.
             *
             * @return  void
             */
            public function setTainted()
            {
                $this->wasTainted = true;
            }
        };
    }

    /**
     * Assert whether the URL that was parsed last was marked as tainted.
     *
     * @param   boolean  $expected  The expected state
     * @param   string   $message   The message to show on failure
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function assertTainted(bool $expected, string $message = ''): void
    {
        $this->assertSame($expected, $this->siteRouter->wasTainted, $message);
    }

    /**
     * Build a database stub that answers the queries the router makes in the flat and the tree mode.
     *
     * @return  DatabaseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createStructuredTagDatabaseStub(): DatabaseInterface
    {
        $db      = $this->createStub(DatabaseInterface::class);
        $current = new \stdClass();

        $db->method('createQuery')->willReturnCallback(fn () => $this->getQueryStub($db));
        $db->method('quoteName')->willReturnArgument(0);
        $db->method('setQuery')->willReturnCallback(
            function ($query) use ($db, $current) {
                $current->query = $query;

                return $db;
            }
        );

        // getTagRows() looks tags up by id, getTagRowsByPath() looks them up by path.
        $db->method('loadObjectList')->willReturnCallback(
            function () use ($current) {
                $values = $this->boundValues($current->query);

                if (\in_array('parent_id', $this->selectedColumns($current->query), true)) {
                    return array_values(
                        array_map(
                            fn ($id) => (object) $this->structuredTags[$id],
                            array_filter(array_map('intval', $values), fn ($id) => isset($this->structuredTags[$id]))
                        )
                    );
                }

                $rows = [];

                foreach ($this->structuredTags as $tag) {
                    if (\in_array($tag['path'], $values, true)) {
                        $rows[] = (object) ['id' => $tag['id'], 'path' => $tag['path']];
                    }
                }

                return $rows;
            }
        );

        // preprocess() resolves an id to its alias, getRootTagByAlias() resolves an alias to a tag.
        $db->method('loadObject')->willReturnCallback(
            function () use ($current) {
                $bounded = $current->query->getBounded();

                if (isset($bounded[':key'])) {
                    $id = (int) $bounded[':key']->value;

                    return isset($this->structuredTags[$id]) ? (object) $this->structuredTags[$id] : null;
                }

                $alias = $bounded[':alias']->value ?? null;

                foreach ($this->structuredTags as $tag) {
                    if ($tag['alias'] === $alias && $tag['parent_id'] === 1) {
                        return (object) ['id' => $tag['id'], 'alias' => $tag['alias']];
                    }
                }

                return null;
            }
        );

        // The mixed mode resolves an alias to an id without caring about the level of the tag.
        $db->method('loadResult')->willReturnCallback(
            function () use ($current) {
                $alias = $current->query->getBounded()[':alias']->value ?? null;

                foreach ($this->structuredTags as $tag) {
                    if ($tag['alias'] === $alias) {
                        return $tag['id'];
                    }
                }

                return null;
            }
        );

        // buildLookup() resolves a tag id to the ids of all its descendants.
        $db->method('loadColumn')->willReturnCallback(fn () => []);

        return $db;
    }

    /**
     * Read the columns a query selects.
     *
     * @param   object  $query  The query that was passed to setQuery()
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function selectedColumns($query): array
    {
        $columns = [];

        foreach ($query->select->getElements() as $element) {
            foreach ((array) $element as $column) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * Read every bound value of a query in the order they were bound.
     *
     * @param   object  $query  The query that was passed to setQuery()
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function boundValues($query): array
    {
        $values = [];

        foreach ($query->getBounded() as $bound) {
            $values[] = $bound->value;
        }

        return $values;
    }

    /**
     * Build the query a URL for the given tags starts from.
     *
     * @param   array     $ids     The tag ids the URL should address
     * @param   ?integer  $itemid  The menu item the URL should use
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tagQuery(array $ids, ?int $itemid = null): array
    {
        $query = ['option' => 'com_tags', 'view' => 'tag', 'id' => $ids];

        if ($itemid !== null) {
            $query['Itemid'] = $itemid;
        }

        return $query;
    }

    /**
     * Assert that the mode constant is one the helper knows.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function allModes(): array
    {
        return [TagsHelper::MODE_MIXED, TagsHelper::MODE_FLAT, TagsHelper::MODE_TREE];
    }
}
