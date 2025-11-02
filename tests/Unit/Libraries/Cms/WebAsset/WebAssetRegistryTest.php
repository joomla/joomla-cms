<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  WebAsset
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\WebAsset;

use Joomla\CMS\Event\WebAsset\WebAssetRegistryAssetChanged;
use Joomla\CMS\WebAsset\AssetItem\CoreAssetItem;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\WebAssetItem;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for WebAssetRegistry.
 *
 * @package     Joomla.UnitTest
 * @subpackage  WebAsset
 * @since       5.1.0
 */
class WebAssetRegistryTest extends UnitTestCase
{
    /**
     * Test asset add
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testAddGet(): void
    {
        $r = new WebAssetRegistry();
        $a = new WebAssetItem('test', 'test.js');

        $r->add('script', $a);

        $this->assertTrue($r->get('script', 'test') === $a, 'Should return same instance');

        $this->expectException(UnknownAssetException::class);
        $r->get('script', 'test.nope');
    }

    /**
     * Test asset exists
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testExists(): void
    {
        $r = new WebAssetRegistry();
        $a = new WebAssetItem('test', 'test.js');

        $r->add('script', $a);

        $this->assertTrue($r->exists('script', 'test'));
        $this->assertFalse($r->exists('script', 'test.nope'));
    }

    /**
     * Test asset removing
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testRemove(): void
    {
        $r = new WebAssetRegistry();
        $a = new WebAssetItem('test', 'test.js');

        $r->add('script', $a);
        $this->assertTrue($r->exists('script', 'test'));

        $r->remove('script', $a->getName());
        $this->assertFalse($r->exists('script', 'test'));

        // Removing non-existing asset should not throw an exception
        $r->remove('script', 'test.nope');
    }

    /**
     * Test asset events
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testEvents(): void
    {
        $r  = new WebAssetRegistry();
        $d  = $r->getDispatcher();
        $a1 = new WebAssetItem('test', 'test1.js');
        $a2 = new WebAssetItem('test', 'test2.js');

        $eventName  = '';
        $eventAsset = '';
        $change     = '';

        $d->addListener(
            'onWebAssetRegistryChangedAssetNew',
            function (WebAssetRegistryAssetChanged $event) use (&$eventName, &$eventAsset, &$change) {
                $eventName  = $event->getName();
                $eventAsset = $event->getAsset();
                $change     = $event->getChange();
            }
        );
        $d->addListener(
            'onWebAssetRegistryChangedAssetOverride',
            function (WebAssetRegistryAssetChanged $event) use (&$eventName, &$eventAsset, &$change) {
                $eventName  = $event->getName();
                $eventAsset = $event->getAsset();
                $change     = $event->getChange();
            }
        );
        $d->addListener(
            'onWebAssetRegistryChangedAssetRemove',
            function (WebAssetRegistryAssetChanged $event) use (&$eventName, &$eventAsset, &$change) {
                $eventName  = $event->getName();
                $eventAsset = $event->getAsset();
                $change     = $event->getChange();
            }
        );

        // Test: new
        $r->add('script', $a1);
        $this->assertEquals('onWebAssetRegistryChangedAssetNew', $eventName, 'Should trigger correct event');
        $this->assertTrue($a1 === $eventAsset, 'Should provide correct asset instance');
        $this->assertEquals('new', $change, 'Should provide correct "change"');

        // Test: override
        $r->add('script', $a2);
        $this->assertEquals('onWebAssetRegistryChangedAssetOverride', $eventName, 'Should trigger correct event');
        $this->assertTrue($a1 === $eventAsset, 'Should provide original asset instance');
        $this->assertEquals('override', $change, 'Should provide correct "change"');

        // Test: remove
        $r->remove('script', $a2->getName());
        $this->assertEquals('onWebAssetRegistryChangedAssetRemove', $eventName, 'Should trigger correct event');
        $this->assertTrue($a2 === $eventAsset, 'Should provide original asset instance');
        $this->assertEquals('remove', $change, 'Should provide correct "change"');
    }

    /**
     * Test asset createAsset
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testCreateAsset(): void
    {
        $r = new WebAssetRegistry();
        $a = $r->createAsset('test', 'test.js', ['foo' => 'bar'], ['type' => 'module'], ['core']);

        $this->assertInstanceOf(WebAssetItem::class, $a);
        $this->assertEquals(['foo' => 'bar'], $a->getOptions());
        $this->assertEquals(['type' => 'module'], $a->getAttributes());
        $this->assertEquals(['core'], $a->getDependencies());

        // Create with the specified class name
        $b = $r->createAsset(
            'test2',
            'test2.js',
            [
                'namespace' => 'Joomla\CMS\WebAsset\AssetItem',
                'class'     => 'CoreAssetItem',
            ]
        );

        $this->assertInstanceOf(CoreAssetItem::class, $b);
    }

    /**
     * Test asset parseRegistryFiles
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function testParseRegistryFiles(): void
    {
        $r = new WebAssetRegistry();
        $r->addRegistryFile('tests/Unit/Libraries/Cms/WebAsset/asset.registry.json');

        $this->assertTrue($r->exists('script', 'test1'));
        $this->assertTrue($r->exists('style', 'test1'));

        // Parsing of the broken json should throw an exception
        $r->addRegistryFile('tests/Unit/Libraries/Cms/WebAsset/asset.registry-corrupted.json');

        $this->expectException(\RuntimeException::class);
        $r->exists('script', 'test1');

        // @TODO: Test that parsing happens only once per registry file
    }
}
