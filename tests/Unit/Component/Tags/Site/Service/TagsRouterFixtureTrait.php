<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Tags\Site\Service;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuItem;
use Joomla\Component\Tags\Site\Service\Router;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Shared fixtures for the com_tags router characterization tests.
 *
 * The com_tags router builds its menu item lookup table inside its constructor, which needs a booted
 * application, the component table and the plugin table. None of that is available to a unit test, so
 * the router is built without invoking the constructor and its collaborators are injected by reflection.
 * That keeps these tests free of any database and free of any production code change.
 *
 * @since  __DEPLOY_VERSION__
 */
trait TagsRouterFixtureTrait
{
    /**
     * The tag fixture, keyed by tag id.
     *
     * Each tag carries an alias and a path. The path is deliberately different from the alias for the
     * nested tags, because several of the characterization tests assert that the router uses the alias
     * and never the path.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $tagFixture = [
        // A top level tag.
        7 => ['alias' => 'sport', 'path' => 'sport'],
        // A child of 7. Its path is prefixed with the parent alias, its alias is not.
        11 => ['alias' => 'football', 'path' => 'sport/football'],
        // A child of a different parent that carries the *same* alias as tag 11.
        12 => ['alias' => 'football', 'path' => 'culture/football'],
        // A second top level tag.
        9 => ['alias' => 'culture', 'path' => 'culture'],
    ];

    /**
     * Descendants of a tag id, as returned by the lookup query in buildLookup().
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $tagChildrenFixture = [];

    /**
     * The value ComponentHelper::$components had before the test replaced it.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $originalComponents;

    /**
     * The value Factory::$application had before the test replaced it.
     *
     * @var    mixed
     * @since  __DEPLOY_VERSION__
     */
    private $originalApplication;

    /**
     * Seed the static state the router touches indirectly.
     *
     * Multilanguage::isEnabled() asks the application whether it is a site application, so an application
     * has to be present even though these tests are monolingual.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUpStaticState(): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $this->originalComponents = $components->getValue();
        $components->setValue(null, ['com_tags' => new ComponentRecord(['id' => 42, 'option' => 'com_tags', 'enabled' => 1])]);

        $this->originalApplication = Factory::$application;
        Factory::$application      = $this->createStub(CMSWebApplicationInterface::class);
    }

    /**
     * Restore the static state seeded by setUpStaticState().
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDownStaticState(): void
    {
        $components = new \ReflectionProperty(ComponentHelper::class, 'components');
        $components->setAccessible(true);
        $components->setValue(null, $this->originalComponents);

        Factory::$application = $this->originalApplication;
    }

    /**
     * Build a database stub that answers the three queries the router makes.
     *
     * @return  DatabaseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createTagDatabaseStub(): DatabaseInterface
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

        // preprocess() resolves an id to its alias.
        $db->method('loadObject')->willReturnCallback(
            function () use ($current) {
                $id = (int) $this->boundValue($current->query, ':key');

                return isset($this->tagFixture[$id]) ? (object) $this->tagFixture[$id] : null;
            }
        );

        // fixSegment() resolves an alias to its id.
        $db->method('loadResult')->willReturnCallback(
            function () use ($current) {
                $alias = $this->boundValue($current->query, ':alias');

                foreach ($this->tagFixture as $id => $tag) {
                    if ($tag['alias'] === $alias) {
                        return $id;
                    }
                }

                return null;
            }
        );

        // buildLookup() resolves a tag id to the ids of all its descendants.
        $db->method('loadColumn')->willReturnCallback(
            function () use ($current) {
                $id = (int) $this->boundValue($current->query, ':id');

                return $this->tagChildrenFixture[$id] ?? [];
            }
        );

        return $db;
    }

    /**
     * Read a bound parameter from a query built by the query stub.
     *
     * @param   object  $query  The query that was passed to setQuery()
     * @param   string  $key    The placeholder to read
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function boundValue($query, string $key)
    {
        $bounded = $query->getBounded();

        return isset($bounded[$key]) ? $bounded[$key]->value : null;
    }

    /**
     * Build a menu stub.
     *
     * @param   MenuItem[]   $items   The com_tags menu items that exist on the site
     * @param   ?MenuItem    $active  The active menu item, if any
     *
     * @return  AbstractMenu
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createMenuStub(array $items = [], ?MenuItem $active = null): AbstractMenu
    {
        $byId = [];

        foreach ($items as $item) {
            $byId[$item->id] = $item;
        }

        $menu = $this->createStub(AbstractMenu::class);
        $menu->method('getItems')->willReturn($items);
        $menu->method('getActive')->willReturn($active);
        $menu->method('getItem')->willReturnCallback(fn ($id) => $byId[$id] ?? null);
        $menu->method('getDefault')->willReturn(new MenuItem(['id' => 999, 'language' => '*']));

        return $menu;
    }

    /**
     * Build a com_tags menu item.
     *
     * @param   integer  $id        The menu item id
     * @param   array    $query     The query the menu item points at
     * @param   array    $params    The menu item parameters
     * @param   string   $language  The menu item language
     *
     * @return  MenuItem
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createTagsMenuItem(int $id, array $query, array $params = [], string $language = '*'): MenuItem
    {
        $item = new MenuItem(
            [
                'id'       => $id,
                'language' => $language,
                'query'    => array_merge(['option' => 'com_tags'], $query),
            ]
        );

        $item->setParams(new Registry($params));

        return $item;
    }

    /**
     * Build a router with the given collaborators, bypassing the constructor.
     *
     * @param   AbstractMenu        $menu       The menu the router should use
     * @param   DatabaseInterface   $db         The database the router should use
     * @param   boolean             $buildLookup  Whether the menu item lookup should be built
     * @param   array               $sefParams  Parameters of the SEF system plugin
     *
     * @return  Router
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createRouter(
        AbstractMenu $menu,
        DatabaseInterface $db,
        bool $buildLookup = false,
        array $sefParams = []
    ): Router {
        $reflection = new \ReflectionClass(Router::class);
        $router     = $reflection->newInstanceWithoutConstructor();

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getMenu')->willReturn($menu);
        $app->method('get')->willReturn('en-GB');

        $router->app  = $app;
        $router->menu = $menu;

        $this->setProperty($router, 'db', $db);
        $this->setProperty($router, 'sefparams', new Registry($sefParams));
        $this->setProperty($router, 'lookup', []);

        if ($buildLookup) {
            $method = $reflection->getMethod('buildLookup');
            $method->setAccessible(true);
            $method->invoke($router);
        }

        return $router;
    }

    /**
     * Write a non public property on the router.
     *
     * @param   Router  $router  The router to write to
     * @param   string  $name    The property name
     * @param   mixed   $value   The value to write
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setProperty(Router $router, string $name, $value): void
    {
        $property = new \ReflectionProperty(Router::class, $name);
        $property->setAccessible(true);
        $property->setValue($router, $value);
    }

    /**
     * Read a non public property from the router.
     *
     * @param   Router  $router  The router to read from
     * @param   string  $name    The property name
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getProperty(Router $router, string $name)
    {
        $property = new \ReflectionProperty(Router::class, $name);
        $property->setAccessible(true);

        return $property->getValue($router);
    }
}
