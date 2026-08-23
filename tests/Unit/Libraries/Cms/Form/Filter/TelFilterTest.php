<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\TelFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for TelFilter.
 *
 * The filter normalises a telephone number into the "countrycode.number" shape the telephone form
 * field stores. It tries the NANP, ITU-T and EPP patterns in turn and falls back to reducing the
 * value to its digits. The element, group, input registry and form are not read, so they are all
 * passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The telephone form filter')]
class TelFilterTest extends UnitTestCase
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
            // North American Numbering Plan, in each of the shapes the pattern accepts.
            'NANP with brackets and spaces'   => ['1.2025550125', '(202) 555-0125'],
            'NANP with dots'                  => ['1.2025550125', '202.555.0125'],
            'NANP with hyphens'               => ['1.2025550125', '202-555-0125'],
            'NANP with a leading +1'          => ['1.2025550125', '+1 202 555 0125'],
            'NANP with a leading 1'           => ['1.2025550125', '1-202-555-0125'],
            'NANP with surrounding spaces'    => ['1.2025550125', '  (202) 555-0125  '],

            // ITU-T, which splits the country code off at the first space.
            'ITU-T'                           => ['44.2079460000', '+44 2079460000'],

            // EPP, which keeps the country code and drops any extension.
            'EPP'                             => ['44.2079460000', '+44.2079460000'],
            'EPP with an extension'           => ['44.2079460000', '+44.2079460000x123'],

            // Already in the stored shape.
            'already normalised'              => ['44.2079460000', '44.2079460000'],

            // Fallback: reduce to digits, then split a country code off the front when there are
            // more than twelve of them.
            'seven digits stay local'         => ['.5550125', '5550125'],
            'twelve digits stay local'        => ['.123456789012', '123456789012'],
            'thirteen digits gain a code'     => ['1.234567890123', '1234567890123'],
            'fifteen digits gain a code'      => ['123.456789012345', '123456789012345'],

            // Anything the fallback cannot use is discarded.
            'sixteen digits are discarded'    => ['', '1234567890123456'],
            'letters only'                    => ['', 'not a number'],
            'empty string'                    => ['', ''],
        ];
    }

    /**
     * Tests that a telephone number is normalised to the expected shape.
     *
     * @param   string  $expected  The expected filtered value
     * @param   string  $value     The raw form field value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataFilter')]
    #[TestDox('normalises a telephone number')]
    public function testFilter(string $expected, string $value): void
    {
        $filter  = new TelFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="tel" filter="tel" />');

        $this->assertSame($expected, $filter->filter($element, $value, null, null, null));
    }

    /**
     * Tests an ITU-T number written without the space the branch relies on.
     *
     * The country code is split off at the first space, so a number which matches the ITU-T pattern
     * but contains no space loses everything and is stored as a bare separator. This pins the
     * current behaviour, it is not an endorsement of it.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @todo    An ITU-T number without a space should keep its digits.
     */
    #[TestDox('reduces a spaceless ITU-T number to a bare separator (known defect)')]
    public function testSpacelessItuNumberIsReducedToASeparator(): void
    {
        $filter  = new TelFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="tel" filter="tel" />');

        $this->assertSame('.', $filter->filter($element, '+442079460000', null, null, null));
    }

    /**
     * Tests that the element, group, input registry and form are not read.
     *
     * This pins the fact that no Form needs to be built to exercise this filter.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('ignores the element, group, input and form arguments')]
    public function testContextArgumentsAreIgnored(): void
    {
        $filter = new TelFilter();

        $bare      = new \SimpleXMLElement('<field />');
        $decorated = new \SimpleXMLElement('<field name="unittest" type="tel" filter="tel" relative="true" />');

        $this->assertSame(
            $filter->filter($bare, '+44 2079460000', null, null, null),
            $filter->filter($decorated, '+44 2079460000', 'somegroup', null, null)
        );
    }
}
