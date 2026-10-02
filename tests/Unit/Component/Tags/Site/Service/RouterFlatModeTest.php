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
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for the URLs the com_tags router builds and parses in the flat mode.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterFlatModeTest extends UnitTestCase
{
    use StructuredTagsRouterFixtureTrait;

    /**
     * Set up the static state the router depends on.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
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
     * Build a router for a site whose only com_tags menu item is a list of all tags.
     *
     * @param   boolean  $isActive  Whether the list of all tags is the active menu item
     * @param   array    $filters   The tags the URL carries as query filters
     *
     * @return  \Joomla\Component\Tags\Site\Service\Router
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createRouterWithTagList(bool $isActive = false, array $filters = [])
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);

        return $this->createStructuredRouter(
            TagsHelper::MODE_FLAT,
            [$tagsMenuItem],
            $isActive ? $tagsMenuItem : null,
            $filters
        );
    }

    /**
     * @testdox  The segments of a multi tag URL are ordered by tag id
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSegmentsAreOrderedByTagId()
    {
        $router = $this->createRouterWithTagList();

        // The tags go in as travel (11) before news (3) and come out the other way round.
        $query = $router->preprocess($this->tagQuery([11, 3], 5));

        $this->assertSame(['news', 'travel'], $router->build($query));
    }

    /**
     * @testdox  A URL whose segments are not ordered by tag id is tainted
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testWrongSegmentOrderIsTainted()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['travel', 'news'];

        $vars = $router->parse($segments);

        $this->assertSame([11, 3], $vars['id'], 'Both tags are still addressed');
        $this->assertTainted(true, 'The SEF plugin has to redirect to the ordered URL');
    }

    /**
     * @testdox  A URL that addresses a tag twice is tainted
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDuplicateSegmentsAreTainted()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['news', 'news'];

        $vars = $router->parse($segments);

        $this->assertSame([3], $vars['id'], 'The duplicate is dropped');
        $this->assertTainted(true);
    }

    /**
     * @testdox  A URL that repeats a tag behind a second one is tainted
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDuplicateBehindAnotherTagIsTainted()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['news', 'travel', 'news'];

        $vars = $router->parse($segments);

        $this->assertSame([3, 11], $vars['id']);
        $this->assertTainted(true);
    }

    /**
     * @testdox  An ordered URL without duplicates is not tainted
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCanonicalUrlIsNotTainted()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['news', 'travel'];

        $router->parse($segments);

        $this->assertTainted(false);
    }

    /**
     * @testdox  A segment that names no tag is handed back and produces a 404
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testUnknownAliasIsHandedBack()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['news', 'does-not-exist'];

        $vars = $router->parse($segments);

        /*
         * The application throws a RouteNotFoundException for any part of the path that no router
         * consumed, so an unresolvable alias is a 404 and never a page that silently shows fewer tags.
         */
        $this->assertSame([3], $vars['id']);
        $this->assertSame(['does-not-exist'], $segments, 'The unknown segment has to be handed back');
    }

    /**
     * @testdox  A lone segment that names no tag produces no variables at all
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testLoneUnknownAliasProducesNoVariables()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['does-not-exist'];

        $this->assertSame([], $router->parse($segments));
        $this->assertSame(['does-not-exist'], $segments);
    }

    /**
     * @testdox  The alias of a nested tag is not routable even though the tag exists
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNestedTagAliasIsNotRoutable()
    {
        $router   = $this->createRouterWithTagList(true);
        $segments = ['football'];

        // Tag 7 carries the alias "football" but lives below "sport", so it takes no part in the route.
        $this->assertSame([], $router->parse($segments));
        $this->assertSame(['football'], $segments);
    }

    /**
     * @testdox  A segment resolves to the root level tag and never to a nested tag of the same alias
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testASharedAliasAlwaysResolvesToTheRootLevelTag()
    {
        // Legacy data may carry a nested tag with the alias of a root level tag. Only the latter routes.
        $this->structuredTags[15] = [
            'id'        => 15,
            'alias'     => 'news',
            'path'      => 'travel/news',
            'parent_id' => 11,
            'level'     => 2,
            'lft'       => 11,
            'rgt'       => 12,
        ];

        $router = $this->createRouterWithTagList(true);

        for ($i = 0; $i < 5; $i++) {
            $segments = ['news'];

            $this->assertSame([3], $router->parse($segments)['id'], 'Run ' . $i);
        }
    }

    /**
     * @testdox  A nested tag falls back to the id query parameter so that the link stays functional
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNestedTagFallsBackToTheQueryParameter()
    {
        $router = $this->createRouterWithTagList();

        $query    = $router->preprocess($this->tagQuery([7], 5));
        $segments = $router->build($query);

        $this->assertSame([], $segments, 'A nested tag must not produce a segment that does not parse back');
        $this->assertSame(['7:football'], $query['id']);
    }

    /**
     * @testdox  A set that mixes a root level and a nested tag keeps every tag in the URL
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMixedSetKeepsEveryTag()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createStructuredRouter(TagsHelper::MODE_FLAT, [$tagsMenuItem], $tagsMenuItem, ['7:football']);

        $query    = $router->preprocess($this->tagQuery([3, 7], 5));
        $segments = $router->build($query);

        // The root level tag is a segment, the nested one is a filter, and both survive the round trip.
        $this->assertSame(['news'], $segments);
        $this->assertSame(['7:football'], $query['id']);
        $this->assertSame(['id' => [3, 7], 'view' => 'tag'], $router->parse($segments));
        $this->assertTainted(false);
    }

    /**
     * @testdox  Building and parsing a multi tag URL returns the same set of tags
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testRoundTripPreservesTheSetOfTags()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createStructuredRouter(TagsHelper::MODE_FLAT, [$tagsMenuItem], $tagsMenuItem);

        $query    = $router->preprocess($this->tagQuery([11, 3], 5));
        $segments = $router->build($query);
        $vars     = $router->parse($segments);

        $this->assertSame(['id' => [3, 11], 'view' => 'tag'], $vars);
        $this->assertSame([], $segments, 'Every segment has to be consumed');
        $this->assertTainted(false);
    }

    /**
     * @testdox  The tags of the menu item are not repeated in the URL
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemTagsAreNotRepeated()
    {
        $tagMenuItem = $this->createTagsMenuItem(6, ['view' => 'tag', 'id' => [3]]);
        $router      = $this->createStructuredRouter(TagsHelper::MODE_FLAT, [$tagMenuItem], $tagMenuItem);

        $query = $router->preprocess($this->tagQuery([3, 11], 6));

        $this->assertSame(['travel'], $router->build($query));

        // The menu item seeds the parsed tag list, so the URL still addresses both tags.
        $segments = ['travel'];

        $this->assertSame(['id' => [3, 11], 'view' => 'tag'], $router->parse($segments));
    }

    /**
     * @testdox  A menu item whose tags were not all requested absorbs nothing
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemCarryingAnUnrequestedTagAbsorbsNothing()
    {
        $tagMenuItem = $this->createTagsMenuItem(6, ['view' => 'tag', 'id' => [3, 11]]);
        $router      = $this->createStructuredRouter(TagsHelper::MODE_FLAT, [$tagMenuItem]);

        $query = $router->preprocess($this->tagQuery([3], 6));

        // Dropping "news" from the URL would leave the menu item addressing "travel" as well.
        $this->assertSame(['news'], $router->build($query));
    }

    /**
     * @testdox  A tag that the URL carries as a filter is added to the tags of the segments
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testQueryFiltersAreAddedToTheSegments()
    {
        $router   = $this->createRouterWithTagList(true, ['7:football']);
        $segments = ['news'];

        $this->assertSame(['id' => [3, 7], 'view' => 'tag'], $router->parse($segments));
        $this->assertTainted(false);
    }

    /**
     * @testdox  A filter whose id names no tag produces a 404
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFilterWithAnUnknownIdProducesA404()
    {
        $router   = $this->createRouterWithTagList(true, ['999:does-not-exist']);
        $segments = ['news'];

        $this->expectException(RouteNotFoundException::class);

        $router->parse($segments);
    }
}
