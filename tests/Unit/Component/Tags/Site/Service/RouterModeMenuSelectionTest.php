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
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests for the menu item the com_tags router picks in the flat and the tree mode.
 *
 * The rule is the same in both modes: only a menu item whose tags are a subset of the requested tags is
 * a candidate, and the candidate carrying the most tags wins. Every test therefore runs in both modes.
 *
 * The menu items mirror the ones the testing instructions of the pull request ask for:
 *
 * | Menu item | Tags                |
 * |-----------|---------------------|
 * | 30        | list of all tags    |
 * | 41        | news                |
 * | 42        | sport               |
 * | 43        | news, sport         |
 * | 44        | travel              |
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterModeMenuSelectionTest extends UnitTestCase
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
     * The modes that pick a menu item by subset matching.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function structuredModes(): array
    {
        return [
            'flat mode' => [TagsHelper::MODE_FLAT],
            'tree mode' => [TagsHelper::MODE_TREE],
        ];
    }

    /**
     * Ask the router for the menu item of a set of tags.
     *
     * @param   string  $mode       The mode the site runs in
     * @param   array   $ids        The tag ids that are being requested
     * @param   array   $menuItems  The com_tags menu items of the site, or the default set when empty
     *
     * @return  ?integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function selectMenuItem(string $mode, array $ids, array $menuItems = []): ?int
    {
        $router = $this->createStructuredRouter($mode, $menuItems ?: $this->defaultMenuItems());

        return $router->preprocess($this->tagQuery($ids))['Itemid'] ?? null;
    }

    /**
     * The com_tags menu items every test starts from.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    private function defaultMenuItems(): array
    {
        return [
            $this->createTagsMenuItem(30, ['view' => 'tags']),
            $this->createTagsMenuItem(41, ['view' => 'tag', 'id' => [3]]),
            $this->createTagsMenuItem(42, ['view' => 'tag', 'id' => [5]]),
            $this->createTagsMenuItem(43, ['view' => 'tag', 'id' => [3, 5]]),
            $this->createTagsMenuItem(44, ['view' => 'tag', 'id' => [11]]),
        ];
    }

    /**
     * @testdox  The menu item carrying exactly the requested tag is selected
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSingleTagMenuItemIsSelected(string $mode)
    {
        $this->assertSame(41, $this->selectMenuItem($mode, [3]));
        $this->assertSame(44, $this->selectMenuItem($mode, [11]));
    }

    /**
     * @testdox  The menu item carrying the most of the requested tags wins
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemWithTheMostTagsWins(string $mode)
    {
        // Menu items 41, 42 and 43 are all subsets of the request, 43 carries the most tags.
        $this->assertSame(43, $this->selectMenuItem($mode, [3, 5]));
    }

    /**
     * @testdox  The menu item with the most tags is used even when further tags were requested
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testMenuItemIsUsedForAWiderRequest(string $mode)
    {
        $this->assertSame(43, $this->selectMenuItem($mode, [3, 5, 13]));
    }

    /**
     * @testdox  A menu item whose tags are not a subset of the request is not selected
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNonSubsetMenuItemIsNotSelected(string $mode)
    {
        // Menu item 43 also carries "sport", which was not requested, so menu item 41 is used.
        $this->assertSame(41, $this->selectMenuItem($mode, [3, 13]));
    }

    /**
     * @testdox  The generic list of all tags is the fallback when no menu item is a subset
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGenericTagsMenuItemIsTheFallback(string $mode)
    {
        $this->assertSame(30, $this->selectMenuItem($mode, [13]));
    }

    /**
     * @testdox  The menu item with the lowest id wins when two carry the same tags
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testLowestIdWinsATie(string $mode)
    {
        $menuItems = [
            $this->createTagsMenuItem(30, ['view' => 'tags']),
            // Registered in the reverse order, so that the order of the menu cannot decide.
            $this->createTagsMenuItem(52, ['view' => 'tag', 'id' => [3, 5]]),
            $this->createTagsMenuItem(51, ['view' => 'tag', 'id' => [5, 3]]),
        ];

        $this->assertSame(51, $this->selectMenuItem($mode, [3, 5], $menuItems));
    }

    /**
     * @testdox  A menu item that no longer exists falls back to the next best candidate
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFallbackToTheNextBestCandidate(string $mode)
    {
        // An unpublished menu item never reaches the router, so it is simply not in the list.
        $menuItems = [
            $this->createTagsMenuItem(30, ['view' => 'tags']),
            $this->createTagsMenuItem(41, ['view' => 'tag', 'id' => [3]]),
            $this->createTagsMenuItem(42, ['view' => 'tag', 'id' => [5]]),
        ];

        // Both remaining menu items carry one tag, so the lower menu item id decides.
        $this->assertSame(41, $this->selectMenuItem($mode, [3, 5], $menuItems));
    }

    /**
     * @testdox  The order the tags are requested in does not change the menu item
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagOrderDoesNotMatter(string $mode)
    {
        $this->assertSame(43, $this->selectMenuItem($mode, [5, 3]));
        $this->assertSame(43, $this->selectMenuItem($mode, [3, 5]));
    }

    /**
     * @testdox  An explicitly supplied Itemid is never replaced
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testExplicitItemidIsKept(string $mode)
    {
        $router = $this->createStructuredRouter($mode, $this->defaultMenuItems());

        $this->assertSame(77, $router->preprocess($this->tagQuery([3], 77))['Itemid']);
    }

    /**
     * @testdox  The list of all tags is still selected through its parent tag
     *
     * @dataProvider  structuredModes
     *
     * @param   string  $mode  The mode the site runs in
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagsViewIsUnaffectedByTheMode(string $mode)
    {
        $router = $this->createStructuredRouter(
            $mode,
            [
                $this->createTagsMenuItem(30, ['view' => 'tags']),
                $this->createTagsMenuItem(31, ['view' => 'tags', 'parent_id' => 3]),
            ]
        );

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tags', 'parent_id' => 3]);

        $this->assertSame(31, $query['Itemid']);
    }
}
