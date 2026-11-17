<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\IntarrayFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for IntarrayFilter.
 *
 * The filter casts the value to an array of integers. Only the element is read, and only to raise
 * the deprecation for the legacy `INT_ARRAY` filter name, so the group, input registry and form are
 * passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The intarray form filter')]
class IntarrayFilterTest extends UnitTestCase
{
    /**
     * Test data for the testFilter method.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataFilter(): array
    {
        return [
            'numeric string is wrapped'    => [[5], '5'],
            'array of numeric strings'     => [[1, 2], ['1', '2']],
            'non numeric member becomes 0' => [[1, 2, 0], ['1', '2', 'x']],
            'empty array stays empty'      => [[], []],
            'string keys are preserved'    => [['x' => 7], ['x' => '7']],
            'non numeric string becomes 0' => [[0], 'abc'],
            'null becomes zero'            => [[0], null],
            'float is truncated'           => [[3], 3.9],
            'boolean true'                 => [[1], true],
        ];
    }

    /**
     * Tests that the value is cast to an array of integers.
     *
     * @param   array  $expected  The expected integer array
     * @param   mixed  $value     The raw form field value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataFilter')]
    #[TestDox('casts the value to an array of integers')]
    public function testFilter(array $expected, $value): void
    {
        $filter  = new IntarrayFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="intarray" />');

        $this->assertSame($expected, $filter->filter($element, $value, null, null, null));
    }

    /**
     * Tests that an object is converted through its public properties, keeping their names as keys.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('converts an object through its public properties, keeping the property names as keys')]
    public function testObjectKeepsItsPropertyNamesAsKeys(): void
    {
        $filter  = new IntarrayFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="intarray" />');

        $this->assertSame(
            ['a' => 3, 'b' => 4],
            $filter->filter($element, (object) ['a' => '3', 'b' => '4'], null, null, null)
        );
    }

    /**
     * Tests that the deprecated INT_ARRAY filter name still filters the value.
     *
     * The deprecation itself is raised with a suppressed trigger_error() and is therefore not
     * observable from the test, so only the filtering is asserted here.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('still filters when the deprecated INT_ARRAY filter name is used')]
    public function testDeprecatedFilterNameStillFilters(): void
    {
        $filter  = new IntarrayFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="INT_ARRAY" />');

        $this->assertSame([1, 2], $filter->filter($element, ['1', '2'], null, null, null));
    }
}
