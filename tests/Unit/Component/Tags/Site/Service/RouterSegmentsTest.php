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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Characterization tests for the URL segments the com_tags router builds and parses.
 *
 * These tests describe the behaviour of the router as it is today. They are intentionally free of any
 * judgement about whether that behaviour is desirable; where it looks wrong it is marked with a
 * "Characterization:" comment rather than corrected.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterSegmentsTest extends UnitTestCase
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
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A single tag is routed to a segment built from its alias, never from its path')]
    public function testSingleTagSegmentUsesTheAliasAndNotThePath()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem]), $this->createTagDatabaseStub());

        // Tag 11 is a child of tag 7, so its path is "sport/football" while its alias is only "football".
        $query    = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [11], 'Itemid' => 5]);
        $segments = $router->build($query);

        $this->assertSame(['football'], $segments);
        $this->assertStringNotContainsString('/', $segments[0], 'The segment must not carry the tag path');

        // The router consumes both the view and the id, they must not end up in the query string as well.
        $this->assertArrayNotHasKey('id', $query);
        $this->assertArrayNotHasKey('view', $query);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Two tags that share an alias under different parents produce the same segment')]
    public function testTagsWithTheSameAliasUnderDifferentParentsShareASegment()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem]), $this->createTagDatabaseStub());

        // Tag 11 is "sport/football", tag 12 is "culture/football".
        $firstQuery  = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [11], 'Itemid' => 5]);
        $secondQuery = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [12], 'Itemid' => 5]);

        /*
         * Characterization: the two tags are indistinguishable in the URL, and parsing the segment back
         * resolves to whichever tag the database returns first. The nesting is simply not represented.
         *
         * Note that TagTable::store() rejects a duplicate alias for the whole site rather than only for
         * the siblings of a tag, so this state cannot currently be reached through the administrator.
         * See TagTableTest::testAliasUniquenessIsGlobalAndNotScopedToSiblings().
         */
        $this->assertSame(['football'], $router->build($firstQuery));
        $this->assertSame(['football'], $router->build($secondQuery));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Several tags are routed to one segment per tag')]
    public function testSeveralTagsProduceOneSegmentEach()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem]), $this->createTagDatabaseStub());

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7, 9], 'Itemid' => 5]);

        $this->assertSame(['sport', 'culture'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Several tags parse back into a list of ids')]
    public function testSeveralSegmentsParseIntoAListOfIds()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['sport', 'culture'];
        $vars     = $router->parse($segments);

        $this->assertSame(['id' => ['7:sport', '9:culture'], 'view' => 'tag'], $vars);
        $this->assertSame([], $segments, 'Every segment has to be consumed');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Building and parsing a multi tag URL returns the same set of tags')]
    public function testRoundTripPreservesTheSetOfTags()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $query    = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7, 9], 'Itemid' => 5]);
        $segments = $router->build($query);
        $vars     = $router->parse($segments);

        /*
         * Characterization: the round trip is lossless in terms of *which* tags are addressed, but the
         * ids do not come back in the form they went in. They went in as integers and come back as
         * "id:alias" strings. Every consumer downstream has to cast.
         */
        $this->assertSame('tag', $vars['view']);
        $this->assertSame([7, 9], array_map('intval', $vars['id']));
        $this->assertSame(['7:sport', '9:culture'], $vars['id']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A single tag survives a build and parse round trip')]
    public function testRoundTripOfASingleNestedTag()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $query    = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [11], 'Itemid' => 5]);
        $segments = $router->build($query);
        $vars     = $router->parse($segments);

        $this->assertSame(['id' => ['11:football'], 'view' => 'tag'], $vars);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A segment whose alias is unknown is left in place and produces no variables')]
    public function testUnknownAliasIsLeftForTheApplication()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['does-not-exist'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: the router neither throws nor sets a view. It pushes the segment back and
         * lets the application decide, which surfaces as a 404 further up the stack.
         */
        $this->assertSame([], $vars);
        $this->assertSame(['does-not-exist'], $segments, 'The unknown segment has to be handed back');
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Parsing stops at the first unknown alias but keeps the tags found before it')]
    public function testParsingStopsAtTheFirstUnknownAlias()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['sport', 'does-not-exist', 'culture'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: "culture" is a perfectly valid tag but is never looked at, because the
         * unknown segment in front of it ends the loop.
         */
        $this->assertSame(['id' => ['7:sport'], 'view' => 'tag'], $vars);
        $this->assertSame(['does-not-exist', 'culture'], $segments);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Numeric segments are taken as tag ids without any database lookup')]
    public function testNumericSegmentsAreTakenAsIds()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['7', '9'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: the ids stay strings and are never validated against the tags table, so a
         * URL may address a tag id that does not exist.
         */
        $this->assertSame(['id' => ['7', '9'], 'view' => 'tag'], $vars);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A comma separated segment is taken as one list of ids and ends the parsing')]
    public function testCommaSeparatedSegmentIsTakenAsOneListOfIds()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['7,9', 'sport'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: the comma separated list is stored as a single unsplit array element, and
         * the trailing segment is dropped on the floor rather than handed back.
         */
        $this->assertSame(['id' => ['7,9'], 'view' => 'tag'], $vars);
        $this->assertSame(['sport'], $segments);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A numeric segment after a matched alias is no longer read as an id')]
    public function testNumericSegmentAfterAnAliasIsNotConsumed()
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter($this->createMenuStub([$tagsMenuItem], $tagsMenuItem), $this->createTagDatabaseStub());

        $segments = ['sport', '9'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: mixing aliases and ids in one URL is only supported in the id-first
         * direction. Once an alias matched, a following numeric segment is treated as an alias, fails
         * to resolve, and is handed back.
         */
        $this->assertSame(['id' => ['7:sport'], 'view' => 'tag'], $vars);
        $this->assertSame(['9'], $segments);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Without a com_tags menu item the first segment is taken as the view')]
    public function testWithoutAMenuItemTheFirstSegmentIsTheView()
    {
        $router = $this->createRouter($this->createMenuStub(), $this->createTagDatabaseStub());

        $segments = ['tag', 'sport'];
        $vars     = $router->parse($segments);

        $this->assertSame(['view' => 'tag', 'id' => ['7:sport']], $vars);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Without a com_tags menu item a lone view segment produces only the view')]
    public function testWithoutAMenuItemALoneViewSegmentProducesOnlyTheView()
    {
        $router = $this->createRouter($this->createMenuStub(), $this->createTagDatabaseStub());

        $segments = ['tags'];
        $vars     = $router->parse($segments);

        $this->assertSame(['view' => 'tags'], $vars);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Without a menu item the view and every tag are pushed into the segments')]
    public function testWithoutAMenuItemTheViewIsPartOfTheSegments()
    {
        $router = $this->createRouter($this->createMenuStub(), $this->createTagDatabaseStub());

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7, 9], 'Itemid' => 5]);

        // The Itemid does not resolve to a menu item, so the router falls back to the generic branch.
        $this->assertSame(['tag', 'sport', 'culture'], $router->build($query));
        $this->assertArrayNotHasKey('Itemid', $query);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A tag menu item that addresses exactly the requested tags produces no segments')]
    public function testMenuItemMatchingTheRequestedTagsExactlyProducesNoSegments()
    {
        $tagMenuItem = $this->createTagsMenuItem(6, ['view' => 'tag', 'id' => [7, 9]]);
        $router      = $this->createRouter($this->createMenuStub([$tagMenuItem]), $this->createTagDatabaseStub());

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7, 9], 'Itemid' => 6]);

        $this->assertSame([], $router->build($query));
        $this->assertArrayNotHasKey('id', $query);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A tag menu item that addresses a different set of tags repeats every tag in the URL')]
    public function testMenuItemAddressingADifferentSetOfTagsRepeatsEveryTag()
    {
        $tagMenuItem = $this->createTagsMenuItem(6, ['view' => 'tag', 'id' => [7]]);
        $router      = $this->createRouter($this->createMenuStub([$tagMenuItem]), $this->createTagDatabaseStub());

        $query = $router->preprocess(['option' => 'com_tags', 'view' => 'tag', 'id' => [7, 9], 'Itemid' => 6]);

        /*
         * Characterization: the tag the menu item already carries is repeated in the URL as well, so
         * the menu item contributes nothing to the shortening of the URL as soon as one extra tag is
         * requested.
         */
        $this->assertSame(['sport', 'culture'], $router->build($query));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A tag menu item is the base for parsing further tags out of the URL')]
    public function testTagMenuItemSeedsTheParsedTagList()
    {
        $tagMenuItem = $this->createTagsMenuItem(6, ['view' => 'tag', 'id' => [7]]);
        $router      = $this->createRouter($this->createMenuStub([$tagMenuItem], $tagMenuItem), $this->createTagDatabaseStub());

        $segments = ['culture'];
        $vars     = $router->parse($segments);

        /*
         * Characterization: the tags of the menu item are merged with the tags in the URL, and the
         * merged list mixes plain ids from the menu item with "id:alias" strings from the URL.
         */
        $this->assertSame(['id' => [7, '9:culture'], 'view' => 'tag'], $vars);
    }
}
