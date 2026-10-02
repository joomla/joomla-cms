<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Finder\Administrator\Indexer;

use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\DebugAdapter;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\Component\Finder\Administrator\Indexer\Adapter
 *
 * @since  __DEPLOY_VERSION__
 */
class AdapterTest extends UnitTestCase
{
    /**
     * @param   string    $adapterClass            The adapter implementation to test.
     * @param   int       $itemAccess              The item's access level.
     * @param   int|null  $categoryAccess          The category's access level.
     * @param   int       $expectedCategoryAccess  The expected category access level.
     *
     * @return  void
     *
     * @dataProvider provideAccessLevels
     *
     * @since  __DEPLOY_VERSION__
     */
    public function testSetDefaultCategoryAccess(
        string $adapterClass,
        int $itemAccess,
        ?int $categoryAccess,
        int $expectedCategoryAccess
    ): void {
        $item         = (new \ReflectionClass(Result::class))->newInstanceWithoutConstructor();
        $item->access = $itemAccess;

        if ($categoryAccess !== null) {
            $item->cat_access = $categoryAccess;
        }

        $adapter = $this->getMockForAbstractClass($adapterClass, [], '', false);
        $method  = new \ReflectionMethod($adapterClass, 'setDefaultCategoryAccess');

        $method->invoke($adapter, $item);

        $this->assertSame($itemAccess, $item->access);
        $this->assertSame($expectedCategoryAccess, $item->cat_access);
    }

    /**
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function provideAccessLevels(): array
    {
        return [
            'adapter category ID is higher' => [Adapter::class, 3, 7, 7],
            'adapter category ID is lower'  => [Adapter::class, 7, 3, 3],
            'adapter item has no category'  => [Adapter::class, 4, null, 1],
            'debug adapter category access' => [DebugAdapter::class, 3, 7, 7],
        ];
    }
}
