<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\UnsetFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for UnsetFilter.
 *
 * The filter always discards the value. The element, group, input registry and form are not read,
 * so they are all passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The unset form filter')]
class UnsetFilterTest extends UnitTestCase
{
    /**
     * Values which must all be discarded.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataFilter(): array
    {
        return [
            'string' => ['a value'],
            'empty'  => [''],
            'zero'   => [0],
            'null'   => [null],
            'array'  => [['a', 'b']],
            'object' => [(object) ['title' => 'Test']],
        ];
    }

    /**
     * Tests that the value is always discarded.
     *
     * @param   mixed  $value  The value handed to the filter
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataFilter')]
    #[TestDox('discards the value whatever it is')]
    public function testValueIsAlwaysDiscarded($value): void
    {
        $filter  = new UnsetFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="unset" />');

        $this->assertNull($filter->filter($element, $value, null, null, null));
    }
}
