<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Tags\Site\Service;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Tests for the URLs the com_tags router builds and parses in the tree mode.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterTreeModeTest extends UnitTestCase
{
    use StructuredTagsRouterFixtureTrait;

    /**
     * Skip the test while the mode is not part of the branch, and set up the state the router needs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        $this->skipWithoutTagModes();

        parent::setUp();

        $this->setUpStructuredState();
    }

    /**
     * Restore the static state.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $this->tearDownStructuredState();

        parent::tearDown();
    }

    /**
     * Build a router for a site that carries a list of all tags and a menu item per tag.
     *
     * @param   ?integer  $activeId  The id of the menu item that is active, if any
     * @param   array     $filters   The tags the URL carries as query filters
     *
     * @return  \Joomla\Component\Tags\Site\Service\Router
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createTreeRouter(?int $activeId = null, array $filters = [])
    {
        $items = [
            5 => $this->createTagsMenuItem(5, ['view' => 'tags']),
            // A menu item on "news".
            20 => $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [3]]),
            // A menu item on "sport", which lives below "news".
            21 => $this->createTagsMenuItem(21, ['view' => 'tag', 'id' => [5]]),
        ];

        return $this->createStructuredRouter(
            TagsHelper::MODE_TREE,
            array_values($items),
            $activeId === null ? null : $items[$activeId],
            $filters
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The path of a tag is shortened by the path of the tag the menu item points at')]
    public function testPathIsShortenedByTheMenuItemPath()
    {
        $router = $this->createTreeRouter();

        // The menu item points at "sport", the URL addresses "football" below it.
        $query = $router->preprocess($this->tagQuery([5, 7], 21));

        $this->assertSame(['football'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A menu item further up the tree leaves the rest of the path in the URL')]
    public function testPathKeepsEverythingBelowTheMenuItemTag()
    {
        $router = $this->createTreeRouter();

        // The menu item points at "news", the URL addresses "football" two levels below it.
        $query = $router->preprocess($this->tagQuery([3, 7], 20));

        $this->assertSame(['sport', 'football'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Without a menu item on a tag the URL carries the full path')]
    public function testFullPathIsUsedWithoutAMatchingMenuItem()
    {
        $router = $this->createTreeRouter();

        $query = $router->preprocess($this->tagQuery([7], 5));

        $this->assertSame(['news', 'sport', 'football'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Additional tags become filters in "id:alias" form, ordered by tag id')]
    public function testAdditionalTagsBecomeOrderedFilters()
    {
        $router = $this->createTreeRouter();

        // The tags go in with europe (13) in front of travel (11).
        $query    = $router->preprocess($this->tagQuery([5, 7, 13, 11], 21));
        $segments = $router->build($query);

        $this->assertSame(['football'], $segments);
        $this->assertSame(['11:travel', '13:europe'], $query['id']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Filters that are not ordered by tag id taint the URL')]
    public function testUnorderedFiltersAreTainted()
    {
        $router   = $this->createTreeRouter(21, ['13:europe', '11:travel']);
        $segments = ['football'];

        $vars = $router->parse($segments);

        $this->assertSame([5, 7, 13, 11], $vars['id'], 'Every tag is still addressed');
        $this->assertTainted(true, 'The SEF plugin has to redirect to the ordered URL');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A filter whose alias does not match its id taints the URL and the id decides')]
    public function testFilterWithAWrongAliasIsTaintedAndTheIdWins()
    {
        $router   = $this->createTreeRouter(21, ['11:wrong-alias']);
        $segments = ['football'];

        $vars = $router->parse($segments);

        $this->assertSame([5, 7, 11], $vars['id'], 'The id names the tag, not the alias');
        $this->assertTainted(true);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A filter whose id names no tag produces a 404')]
    public function testFilterWithAnUnknownIdProducesA404()
    {
        $router   = $this->createTreeRouter(21, ['999:ghost']);
        $segments = ['football'];

        $this->expectException(RouteNotFoundException::class);

        $router->parse($segments);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A tag that is both in the path and in a filter taints the URL')]
    public function testDuplicateBetweenPathAndFilterIsTainted()
    {
        $router   = $this->createTreeRouter(21, ['7:football']);
        $segments = ['football'];

        $vars = $router->parse($segments);

        /*
         * The deduplication runs on the resolved ids, so it also catches a tag that the URL addresses
         * once through the path and once through a filter.
         */
        $this->assertSame([5, 7], $vars['id']);
        $this->assertTainted(true);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A filter that names a descendant of the path tag is not a duplicate')]
    public function testDescendantFilterIsNotADuplicate()
    {
        $router   = $this->createTreeRouter(20, ['7:football']);
        $segments = ['sport'];

        $vars = $router->parse($segments);

        $this->assertSame([3, 5, 7], $vars['id']);
        $this->assertTainted(false);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A segment that completes no path is handed back and produces a 404')]
    public function testSegmentThatCompletesNoPathIsHandedBack()
    {
        $router   = $this->createTreeRouter(20);
        $segments = ['sport', 'does-not-exist'];

        $vars = $router->parse($segments);

        // "news/sport" resolves, "news/sport/does-not-exist" does not, so the last segment is left over.
        $this->assertSame([3, 5], $vars['id']);
        $this->assertSame(['does-not-exist'], $segments);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A path that skips a level does not resolve')]
    public function testPathThatSkipsALevelDoesNotResolve()
    {
        $router   = $this->createTreeRouter(20);
        $segments = ['football'];

        // "news/football" is nobody's path, so the segment is handed back and the application 404s.
        $router->parse($segments);

        $this->assertSame(['football'], $segments, 'The unresolvable segment has to be handed back');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A path resolves to the tag it spells out and never to a tag of the same last alias')]
    public function testPathResolvesToTheTagItSpellsOut()
    {
        // A second "football" below "travel", so the last segment alone would be ambiguous.
        $this->structuredTags[15] = [
            'id'        => 15,
            'alias'     => 'football',
            'path'      => 'travel/football',
            'parent_id' => 11,
            'level'     => 2,
            'lft'       => 11,
            'rgt'       => 12,
        ];

        $router   = $this->createTreeRouter(20);
        $segments = ['sport', 'football'];

        $this->assertSame([3, 7], $router->parse($segments)['id']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The tag with the lowest id goes into the path when several are descendants')]
    public function testLowestIdWinsThePathAmongSeveralDescendants()
    {
        $router = $this->createTreeRouter();

        // Both "football" (7) and "politics" (9) live below "news".
        $query    = $router->preprocess($this->tagQuery([3, 9, 7], 20));
        $segments = $router->build($query);

        $this->assertSame(['sport', 'football'], $segments);
        $this->assertSame(['9:politics'], $query['id']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The path stays empty when no requested tag lives below the menu item tag')]
    public function testPathStaysEmptyWithoutADescendant()
    {
        $router = $this->createTreeRouter();

        // Neither "travel" nor "europe" lives below "news", and the menu item is forced by the Itemid.
        $query    = $router->preprocess($this->tagQuery([11, 13], 20));
        $segments = $router->build($query);

        $this->assertSame([], $segments);
        $this->assertSame(['11:travel', '13:europe'], $query['id']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Building and parsing a tree URL with filters returns the same set of tags')]
    public function testRoundTripPreservesTheSetOfTags()
    {
        $router = $this->createTreeRouter();

        $query    = $router->preprocess($this->tagQuery([5, 7, 11], 21));
        $segments = $router->build($query);

        $this->assertSame(['football'], $segments);
        $this->assertSame(['11:travel'], $query['id']);

        // Parse the very URL that was built, filters included.
        $parser = $this->createTreeRouter(21, $query['id']);
        $vars   = $parser->parse($segments);

        $this->assertSame(['id' => [5, 7, 11], 'view' => 'tag'], $vars);
        $this->assertSame([], $segments, 'Every segment has to be consumed');
        $this->assertTainted(false);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A menu item that carries a tag which was not requested does not shorten the path')]
    public function testMenuItemCarryingAnUnrequestedTagDoesNotAbsorbIt()
    {
        $menuItem = $this->createTagsMenuItem(22, ['view' => 'tag', 'id' => [3, 11]]);
        $router   = $this->createStructuredRouter(TagsHelper::MODE_TREE, [$menuItem], null);

        $query = $router->preprocess($this->tagQuery([3, 7], 22));

        /*
         * The menu item also addresses "travel", which the URL is not about, so it absorbs nothing. The
         * path prefix still comes from the menu item, because parsing starts from the same menu item.
         */
        $this->assertSame(['sport', 'football'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Without a com_tags menu item the view is the first segment')]
    public function testWithoutAMenuItemTheViewIsTheFirstSegment()
    {
        $router = $this->createStructuredRouter(TagsHelper::MODE_TREE, [], new MenuItem(['id' => 1, 'query' => ['option' => 'com_content']]));

        $segments = ['tag', 'news', 'sport'];

        $this->assertSame(['view' => 'tag', 'id' => [5]], $router->parse($segments));
    }
}
