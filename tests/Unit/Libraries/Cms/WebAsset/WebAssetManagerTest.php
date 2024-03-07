<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  WebAsset
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\WebAsset;

use Joomla\CMS\WebAsset\Exception\InvalidActionException;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\Exception\UnsatisfiedDependencyException;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for WebAssetManager.
 *
 * @package     Joomla.UnitTest
 * @subpackage  WebAsset
 * @since       5.1.0
 */
class WebAssetManagerTest extends UnitTestCase
{
    /**
     * The WebAsset Registry instance
     *
     * @var    WebAssetRegistry
     *
     * @since  5.1.0
     */
    protected $registry;

    /**
     * Sets up the fixture.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function setUp(): void
    {
        $this->registry = new WebAssetRegistry();
        $this->registry->addRegistryFile('tests/Unit/Libraries/Cms/WebAsset/asset.registry.json');
    }

    /**
     * Tears down the fixture.
     *
     * @return void
     *
     * @since   5.1.0
     */
    protected function tearDown(): void
    {
        $this->registry = null;
    }

    /**
     * Test useAsset, isAssetActive
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testUseAsset(): void
    {
        $wa = new WebAssetManager($this->registry);

        $this->assertFalse($wa->isAssetActive('script', 'test1'));
        $wa->useAsset('script', 'test1');
        $this->assertTrue($wa->isAssetActive('script', 'test1'));

        // Test asset with dependencies
        $wa->useAsset('script', 'test2');
        $this->assertTrue($wa->isAssetActive('script', 'test2'), 'Dependency should be active also');
    }

    /**
     * Test useAsset with unknown asset
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testUseAssetUnknownAsset(): void
    {
        $wa = new WebAssetManager($this->registry);
        $this->expectException(UnknownAssetException::class);
        $wa->useAsset('script', 'test.nope');
    }

    /**
     * Test isAssetActive with unknown asset
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testIsAssetActiveUnknownAsset(): void
    {
        $wa = new WebAssetManager($this->registry);
        $this->expectException(UnknownAssetException::class);
        $wa->isAssetActive('script', 'test.nope');
    }

    /**
     * Test useAsset, when WA is locked
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testUseAssetWALocked(): void
    {
        $wa = new WebAssetManager($this->registry);
        $wa->lock();

        $this->expectException(InvalidActionException::class);
        $wa->useAsset('script', 'test1');
    }

    /**
     * Test disableAsset
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testDisableAsset(): void
    {
        $wa = new WebAssetManager($this->registry);
        $wa->useAsset('script', 'test1');

        $this->assertTrue($wa->isAssetActive('script', 'test1'));

        $wa->disableAsset('script', 'test1');

        $this->assertFalse($wa->isAssetActive('script', 'test1'));
    }

    /**
     * Test disableAsset with unknown asset
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testDisableAssetUnknownAsset(): void
    {
        $wa = new WebAssetManager($this->registry);
        $this->expectException(UnknownAssetException::class);
        $wa->disableAsset('script', 'test.nope');
    }

    /**
     * Test disableAsset, when WA is locked
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testDisableAssetWALocked(): void
    {
        $wa = new WebAssetManager($this->registry);
        $wa->lock();

        $this->expectException(InvalidActionException::class);
        $wa->disableAsset('script', 'test1');
    }

    /**
     * Test getAssets
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testGetAssets(): void
    {
        $wa = new WebAssetManager($this->registry);

        $wa->useAsset('script', 'test1');
        $wa->useAsset('script', 'test2');
        $wa->useAsset('script', 'test4');
        $wa->useAsset('script', 'test5');

        $assets = $wa->getAssets('script');
        $this->assertEquals(
            ['test3', 'test1', 'test2', 'test4', 'test5'],
            array_keys($assets),
            'Should return all active assets in FIFO order, with automatically enabled dependencies "at top"'
        );

        $assets2 = $wa->getAssets('script', true);
        $this->assertEquals(
            ['test3', 'test1', 'test2', 'test5', 'test4'],
            array_keys($assets2),
            'Should return all active assets in a Graph order'
        );
    }

    /**
     * Test getAssets with broken dependency
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testGetAssetsUnknownDep(): void
    {
        $wa = new WebAssetManager($this->registry);
        $wa->useAsset('script', 'test.bad-dep');

        $this->expectException(UnsatisfiedDependencyException::class);
        $wa->getAssets('script');
    }
}
