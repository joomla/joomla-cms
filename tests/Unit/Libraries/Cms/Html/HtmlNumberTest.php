<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\HTML\Helpers\Number as HtmlNumber;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for HtmlNumberTest.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class HtmlNumberTest extends UnitTestCase
{
    /**
     * Test...
     *
     * @return  array
     *
     * @since   3.1
     */
    public function dataTestBytes(): array
    {
        return [
            // Element order: result, bytes, unit, precision
            [
                '1.00 b',
                1,
            ],
            [
                '1.00 kB',
                1024,
            ],
            [
                '1.00 MB',
                1024 * 1024,
            ],
            [
                '1.00 GB',
                1024 * 1024 * 1024,
            ],
            [
                '1.00 TB',
                1024 * 1024 * 1024 * 1024,
            ],
            [
                '1.00 PB',
                1024 * 1024 * 1024 * 1024 * 1024,
            ],
            [
                '0',
                0,
            ],

            // Test units.
            [
                '1.00 YB',
                1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
                'auto',
            ],
            [
                '1.00 YB',
                1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
                'YB',
            ],
            [
                '1,024.00 ZB',
                1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
                'ZB',
            ],
            [
                '1,048,576.00 EB',
                1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024,
                'EB',
            ],
            [
                '1.00 PB',
                1024 * 1024 * 1024 * 1024 * 1024,
                'PB',
            ],
            [
                '1,024.00 TB',
                1024 * 1024 * 1024 * 1024 * 1024,
                'TB',
            ],
            [
                '1,048,576.00 GB',
                1024 * 1024 * 1024 * 1024 * 1024,
                'GB',
            ],
            [
                '1,073,741,824.00 MB',
                1024 * 1024 * 1024 * 1024 * 1024,
                'MB',
            ],
            [
                '1,099,511,627,776.00 kB',
                1024 * 1024 * 1024 * 1024 * 1024,
                'kB',
            ],
            [
                '1,125,899,906,842,624.00 b',
                1024 * 1024 * 1024 * 1024 * 1024,
                'b',
            ],
            [
                '1.1258999068426E+15',
                1024 * 1024 * 1024 * 1024 * 1024,
                '',
            ],

            // Test precision
            [
                '1.33 kB',
                1357,
            ],
            [
                '1.3 kB',
                1357,
                null,
                1,
            ],
            [
                '1.33 kB',
                1357,
                null,
                2,
            ],
            [
                '1.325 kB',
                1357,
                null,
                3,
            ],
            [
                '1.3252 kB',
                1357,
                null,
                4,
            ],

            // Test unit suffixed inputs
            [
                '1.00 MB',
                '1024K',
            ],
            [
                '1,024.00 MB',
                '1 GB',
                'MB',
            ],
            [
                '10.50 GB',
                '1.0752E+4 MB',
                'GB',
            ],

            // Test IEC aware input
            [
                '1024000',
                '1024 KB',
                '',
                2,
                true,
            ],
            [
                '1048576',
                '1024 KiB',
                '',
                2,
                true,
            ],

            // Test IEC aware output with automatic unit
            [
                '1.00 MB',
                1000 * 1000,
                'auto',
                2,
                true,
            ],

            // Test automatic binary units output
            [
                '1.00 MiB',
                1024 * 1024,
                'binary',
                2,
                true,
            ],
            [
                '1.00 MiB',
                1024 * 1024,
                'binary',
                2,
                false,
            ],

            // Test IEC aware specific unit output
            [
                '1,000.00 KiB',
                '1024 KB',
                'KiB',
                2,
                true,
            ],
            [
                '1,048.58 kB',
                '1024 KiB',
                'kB',
                2,
                true,
            ],
        ];
    }

    /**
     * Tests the HtmlNumber::bytes method.
     *
     * @param   string   $result     The expected result to match against.
     * @param   string   $bytes      The number of bytes. Can be either numeric or suffixed format: 32M, 60K, 12G or 812b
     * @param   string   $unit       The type of unit to return, few special values are:
     *                               Blank string '' for no unit,
     *                               'auto' to choose automatically (default)
     *                               'binary' to choose automatically but use binary unit prefix
     * @param   integer  $precision  The number of digits to be used after the decimal place.
     * @param   bool     $iec        Whether to be aware of IEC standards. IEC prefixes are always acceptable in input.
     *                               When IEC is ON:  KiB = 1024 B, KB = 1000 B
     *                               When IEC is OFF: KiB = 1024 B, KB = 1024 B
     *
     * @return  void
     *
     * @since        3.1
     * @dataProvider dataTestBytes
     */
    public function testBytes($result, $bytes, $unit = 'auto', $precision = 2, $iec = false)
    {
        $this->assertEquals($result, HtmlNumber::bytes($bytes, $unit, $precision, $iec));
    }
}
