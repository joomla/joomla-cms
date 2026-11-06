<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Tags\Site\Service;

use Joomla\Tests\Unit\UnitTestCase;

/**
 * Characterization tests for the menu item the com_tags router picks for a given set of tags.
 *
 * The selection happens in Router::preprocess() against the lookup table that Router::buildLookup()
 * assembles from every com_tags menu item on the site. Both are covered here, because the shape of the
 * lookup table decides the outcome.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterMenuSelectionTest extends UnitTestCase
{
    use TagsRouterFixtureTrait;

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

        $this->setUpStaticState();
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
        $this->tearDownStaticState();

        parent::tearDown();
    }

    /**
     * Run preprocess() for a tag request against the given com_tags menu items.
     *
     * @param   array  $menuItems  The com_tags menu items that exist on the site
     * @param   array  $ids        The tag ids that are being requested
     * @param   array  $sefParams  Parameters of the SEF system plugin
     *
     * @return  array  The preprocessed query
     *
     * @since   __DEPLOY_VERSION__
     */
    private function preprocessTagRequest(array $menuItems, array $ids, array $sefParams = []): array
    {
        $router = $this->createRouter(
            $this->createMenuStub($menuItems),
            $this->createTagDatabaseStub(),
            true,
            $sefParams
        );

        return $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => $ids]);
    }

    /**
     * @testdox  The single matching menu item is selected
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSingleMatchingMenuItemIsSelected()
    {
        $tagMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7]]);

        $query = $this->preprocessTagRequest([$tagMenuItem], [7]);

        $this->assertSame(20, $query['Itemid']);
    }

    /**
     * @testdox  A menu item is matched regardless of the order the tags are requested in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemIsMatchedIndependentlyOfTheTagOrder()
    {
        $tagMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [9, 7]]);

        // Both the menu item ids and the requested ids are sorted before they are compared.
        $this->assertSame(20, $this->preprocessTagRequest([$tagMenuItem], [7, 9])['Itemid']);
        $this->assertSame(20, $this->preprocessTagRequest([$tagMenuItem], [9, 7])['Itemid']);
    }

    /**
     * @testdox  A single tag menu item is selected for a wider request whatever its match type is
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSingleTagMenuItemIsSelectedForASupersetWhateverItsMatchType()
    {
        $tagsMenuItem = $this->createTagsMenuItem(30, ['view' => 'tags']);

        foreach ([0, 1] as $matchType) {
            $tagMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7]], ['return_any_or_all' => $matchType]);

            $query = $this->preprocessTagRequest([$tagMenuItem, $tagsMenuItem], [7, 9]);

            /*
             * Characterization: buildLookup() registers a menu item under the imploded list of its tag
             * ids, and only registers the single tag keys when the match type is "any". For a menu item
             * with exactly one tag both keys are the string "7", which PHP stores as the integer key 7,
             * so the per tag lookup finds it regardless of the match type. The match type therefore only
             * has an effect on menu items that carry two or more tags.
             */
            $this->assertSame(20, $query['Itemid'], 'Match type ' . $matchType);
        }
    }

    /**
     * @testdox  A multi tag menu item in "all" mode is not selected for a partially overlapping request
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMultiTagMenuItemInAllModeIsNotSelectedForAPartialOverlap()
    {
        // The menu item shows tags 7 and 12 and only registers the combination key "7,12".
        $tagMenuItem  = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7, 12]], ['return_any_or_all' => 0]);
        $tagsMenuItem = $this->createTagsMenuItem(30, ['view' => 'tags']);

        $query = $this->preprocessTagRequest([$tagMenuItem, $tagsMenuItem], [7, 9]);

        $this->assertSame(30, $query['Itemid']);
    }

    /**
     * @testdox  The menu item carrying the exact tag combination wins over a less specific one
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExactCombinationWinsOverASingleTagMenuItem()
    {
        $singleTagMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7]], ['return_any_or_all' => 1]);
        $bothTagsMenuItem  = $this->createTagsMenuItem(21, ['view' => 'tag', 'id' => [7, 9]], ['return_any_or_all' => 1]);

        $query = $this->preprocessTagRequest([$singleTagMenuItem, $bothTagsMenuItem], [7, 9]);

        // The exact combination is looked up first, so the more specific menu item is used.
        $this->assertSame(21, $query['Itemid']);
    }

    /**
     * @testdox  When two "any" menu items carry the same tag the one registered last wins
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testLastRegisteredMenuItemWinsForASharedTag()
    {
        $firstMenuItem  = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7]], ['return_any_or_all' => 1]);
        $secondMenuItem = $this->createTagsMenuItem(21, ['view' => 'tag', 'id' => [7, 9]], ['return_any_or_all' => 1]);

        // Only tag 7 is requested, so the exact combination "7" is looked up.
        $query = $this->preprocessTagRequest([$firstMenuItem, $secondMenuItem], [7]);

        /*
         * Characterization: menu item 21 registers itself under the single tags 7 and 9 as well, which
         * overwrites the entry menu item 20 made for tag 7. The winner therefore depends on the order in
         * which the menu items happen to come back from the menu, not on how well they match.
         */
        $this->assertSame(21, $query['Itemid']);
    }

    /**
     * @testdox  An "any" menu item is selected although it carries a tag that was not requested
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemIsSelectedAlthoughItsTagsAreNotASubsetOfTheRequest()
    {
        // The menu item shows tags 7 and 12, the request asks for tags 7 and 9.
        $tagMenuItem  = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7, 12]], ['return_any_or_all' => 1]);
        $tagsMenuItem = $this->createTagsMenuItem(30, ['view' => 'tags']);

        $query = $this->preprocessTagRequest([$tagMenuItem, $tagsMenuItem], [7, 9]);

        /*
         * Characterization: the overlap of a single tag is enough. The router picks a menu item that
         * addresses tag 12, which the request never asked for, in preference to the generic list.
         */
        $this->assertSame(20, $query['Itemid']);
    }

    /**
     * @testdox  A menu item whose tags do not overlap the request at all is not selected
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemWithoutAnyOverlapIsNotSelected()
    {
        $tagMenuItem  = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [11, 12]], ['return_any_or_all' => 1]);
        $tagsMenuItem = $this->createTagsMenuItem(30, ['view' => 'tags']);

        $query = $this->preprocessTagRequest([$tagMenuItem, $tagsMenuItem], [7, 9]);

        $this->assertSame(30, $query['Itemid']);
    }

    /**
     * @testdox  The generic list of all tags is the fallback when no tag menu item matches
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGenericTagsMenuItemIsUsedAsFallback()
    {
        $tagsMenuItem = $this->createTagsMenuItem(30, ['view' => 'tags']);

        $query = $this->preprocessTagRequest([$tagsMenuItem], [7]);

        // The "tags" menu item without a parent_id is registered under the key 0 and acts as the catch all.
        $this->assertSame(30, $query['Itemid']);
    }

    /**
     * @testdox  The site default menu item is used when nothing matches and strict routing is off
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDefaultMenuItemIsUsedWhenNothingMatches()
    {
        $query = $this->preprocessTagRequest([], [7]);

        /*
         * Characterization: com_tags falls back to the home page menu item, which produces a URL that has
         * nothing to do with tags. The router itself flags this as a bug that is to be removed in 7.0.
         */
        $this->assertSame(999, $query['Itemid']);
    }

    /**
     * @testdox  No menu item is selected when nothing matches and strict routing is on
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoMenuItemIsSelectedUnderStrictRouting()
    {
        $query = $this->preprocessTagRequest([], [7], ['strictrouting' => 1]);

        $this->assertArrayNotHasKey('Itemid', $query);
    }

    /**
     * @testdox  An explicitly supplied Itemid is never replaced
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExplicitItemidIsKept()
    {
        $tagMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [7]]);
        $router      = $this->createRouter($this->createMenuStub([$tagMenuItem]), $this->createTagDatabaseStub(), true);

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7], 'Itemid' => 77]);

        // There is no active menu item, so the supplied Itemid is taken as is and the lookup is skipped.
        $this->assertSame(77, $query['Itemid']);
    }

    /**
     * @testdox  A tags view menu item is selected by its parent tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagsViewMenuItemIsSelectedByParentId()
    {
        $rootTagsMenuItem   = $this->createTagsMenuItem(30, ['view' => 'tags']);
        $nestedTagsMenuItem = $this->createTagsMenuItem(31, ['view' => 'tags', 'parent_id' => 7]);

        $router = $this->createRouter(
            $this->createMenuStub([$rootTagsMenuItem, $nestedTagsMenuItem]),
            $this->createTagDatabaseStub(),
            true
        );

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tags', 'parent_id' => 7]);

        $this->assertSame(31, $query['Itemid']);
    }

    /**
     * @testdox  A tags view menu item also covers the descendants of its parent tag
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagsViewMenuItemCoversDescendantsOfItsParentTag()
    {
        // Tag 11 is a child of tag 7.
        $this->tagChildrenFixture = [7 => [11]];

        $rootTagsMenuItem   = $this->createTagsMenuItem(30, ['view' => 'tags']);
        $nestedTagsMenuItem = $this->createTagsMenuItem(31, ['view' => 'tags', 'parent_id' => 7]);

        $router = $this->createRouter(
            $this->createMenuStub([$rootTagsMenuItem, $nestedTagsMenuItem]),
            $this->createTagDatabaseStub(),
            true
        );

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tags', 'parent_id' => 11]);

        // buildLookup() expands a tags menu item over the whole subtree below its parent tag.
        $this->assertSame(31, $query['Itemid']);
    }

    /**
     * @testdox  A tags view request without a matching parent falls back to the generic list
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagsViewFallsBackToTheGenericList()
    {
        $rootTagsMenuItem   = $this->createTagsMenuItem(30, ['view' => 'tags']);
        $nestedTagsMenuItem = $this->createTagsMenuItem(31, ['view' => 'tags', 'parent_id' => 7]);

        $router = $this->createRouter(
            $this->createMenuStub([$rootTagsMenuItem, $nestedTagsMenuItem]),
            $this->createTagDatabaseStub(),
            true
        );

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tags', 'parent_id' => 9]);

        $this->assertSame(30, $query['Itemid']);
    }

    /**
     * @testdox  The lookup table registers tag menu items by their exact combination
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testLookupRegistersTagMenuItemsByTheirExactCombination()
    {
        $allModeMenuItem = $this->createTagsMenuItem(20, ['view' => 'tag', 'id' => [9, 7]], ['return_any_or_all' => 0]);
        $anyModeMenuItem = $this->createTagsMenuItem(21, ['view' => 'tag', 'id' => [11]], ['return_any_or_all' => 1]);
        $tagsMenuItem    = $this->createTagsMenuItem(30, ['view' => 'tags']);

        $router = $this->createRouter(
            $this->createMenuStub([$allModeMenuItem, $anyModeMenuItem, $tagsMenuItem]),
            $this->createTagDatabaseStub(),
            true
        );

        $lookup = $this->getProperty($router, 'lookup');

        /*
         * The combination key is built from the sorted ids, the per tag keys only exist in "any" mode.
         * Characterization: the combination key of a single tag menu item is a numeric string, which PHP
         * stores as an integer key and which is therefore indistinguishable from a per tag key.
         */
        $this->assertSame(
            [
                '7,9' => 20,
                11    => 21,
            ],
            $lookup['*']['tag']
        );
        $this->assertSame([0 => 30], $lookup['*']['tags']);
    }
}
