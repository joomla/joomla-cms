<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Pagination;

use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Pagination\PaginationObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for the responsive data guard of Pagination.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Pagination
 * @since       __DEPLOY_VERSION__
 */
class PaginationResponsiveTest extends UnitTestCase
{
    /**
     * Calls the protected guard on an instance created without the constructor (which needs the router).
     *
     * @param   string  $class  The pagination class
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private function supportsResponsive(string $class): bool
    {
        $reflection = new \ReflectionClass($class);
        $method     = $reflection->getMethod('supportsResponsive');

        return $method->invoke($reflection->newInstanceWithoutConstructor());
    }

    /**
     * The core class provides responsive data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCoreClassSupportsResponsive(): void
    {
        $this->assertTrue($this->supportsResponsive(Pagination::class));
    }

    /**
     * A subclass that does not change how links are built keeps the responsive data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNeutralSubclassSupportsResponsive(): void
    {
        $subclass = new class (0, 0, 0) extends Pagination {
            public function __construct($total, $limitstart, $limit)
            {
            }

            public function getRowOffset($index)
            {
                return parent::getRowOffset($index);
            }
        };

        $this->assertTrue($this->supportsResponsive($subclass::class));
    }

    /**
     * A subclass that overrides how links or items are built gets the classic list, so its overrides stay in effect.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSubclassOverridingLinksFallsBackToClassicList(): void
    {
        $itemActive = new class (0, 0, 0) extends Pagination {
            public function __construct($total, $limitstart, $limit)
            {
            }

            protected function _item_active(PaginationObject $item) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            {
                return 'custom';
            }
        };

        $itemInactive = new class (0, 0, 0) extends Pagination {
            public function __construct($total, $limitstart, $limit)
            {
            }

            protected function _item_inactive(PaginationObject $item) // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
            {
                return 'custom';
            }
        };

        $dataObject = new class (0, 0, 0) extends Pagination {
            public function __construct($total, $limitstart, $limit)
            {
            }

            protected function _buildDataObject()
            {
                return parent::_buildDataObject();
            }
        };

        $this->assertFalse($this->supportsResponsive($itemActive::class), '_item_active override');
        $this->assertFalse($this->supportsResponsive($itemInactive::class), '_item_inactive override');
        $this->assertFalse($this->supportsResponsive($dataObject::class), '_buildDataObject override');
    }
}
