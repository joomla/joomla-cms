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
 * Tests that a site which never touched the mode parameter routes exactly as it did before.
 *
 * The characterization tests of the preceding pull request already describe that behaviour in detail.
 * This class repeats a few of those expectations with the parameter explicitly absent and with it
 * explicitly set to "mixed", to pin down that both are the very same thing.
 *
 * @package     Joomla.UnitTest
 * @subpackage  com_tags
 * @since       __DEPLOY_VERSION__
 */
class RouterDefaultModeTest extends UnitTestCase
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
     * The ways a site can end up in the mixed mode.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function waysToBeMixed(): array
    {
        return [
            'the parameter was never saved' => [null],
            'the parameter says mixed'      => ['mixed'],
        ];
    }

    /**
     * Build a router without the mode parameter or with it set to the given value.
     *
     * @param   ?string  $mode    The value of the mode parameter, or null to leave it out
     * @param   boolean  $active  Whether the list of all tags is the active menu item
     *
     * @return  \Joomla\Component\Tags\Site\Service\Router
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createDefaultRouter(?string $mode, bool $active = false)
    {
        $tagsMenuItem = $this->createTagsMenuItem(5, ['view' => 'tags']);
        $router       = $this->createRouter(
            $this->createMenuStub([$tagsMenuItem], $active ? $tagsMenuItem : null),
            $this->createStructuredTagDatabaseStub()
        );

        // createRouter() seeds the mixed mode, so overwrite the parameters afterwards.
        $this->setTagMode($mode);

        return $router;
    }

    /**
     * @testdox  A nested tag is addressed by its bare alias
     *
     * @dataProvider  waysToBeMixed
     *
     * @param   ?string  $mode  The value of the mode parameter, or null to leave it out
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNestedTagIsAddressedByItsAlias(?string $mode)
    {
        $router = $this->createDefaultRouter($mode);

        $query = $router->preprocess($this->tagQuery([7], 5));

        // Tag 7 is "news/sport/football" but the URL only ever carries "football".
        $this->assertSame(['football'], $router->build($query));
    }

    /**
     * @testdox  The alias of a nested tag resolves, unlike in the flat mode
     *
     * @dataProvider  waysToBeMixed
     *
     * @param   ?string  $mode  The value of the mode parameter, or null to leave it out
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNestedTagAliasResolves(?string $mode)
    {
        $router   = $this->createDefaultRouter($mode, true);
        $segments = ['football'];

        $this->assertSame(['id' => ['7:football'], 'view' => 'tag'], $router->parse($segments));
    }

    /**
     * @testdox  A numeric segment is still taken as a tag id
     *
     * @dataProvider  waysToBeMixed
     *
     * @param   ?string  $mode  The value of the mode parameter, or null to leave it out
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNumericSegmentIsStillAnId(?string $mode)
    {
        $router   = $this->createDefaultRouter($mode, true);
        $segments = ['3', '11'];

        $this->assertSame(['id' => ['3', '11'], 'view' => 'tag'], $router->parse($segments));
    }

    /**
     * @testdox  The tags keep the order they were requested in
     *
     * @dataProvider  waysToBeMixed
     *
     * @param   ?string  $mode  The value of the mode parameter, or null to leave it out
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTagsKeepTheRequestedOrder(?string $mode)
    {
        $router = $this->createDefaultRouter($mode);

        $query = $router->preprocess($this->tagQuery([11, 3], 5));

        // The mixed mode never reorders the tags, which is what the flat mode changes.
        $this->assertSame(['travel', 'news'], $router->build($query));
    }
}
