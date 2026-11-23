<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Filter
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Filter;

use Joomla\CMS\Filter\InputFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\Filter\InputFilter.
 *
 * @since    6.2.0
 */
class InputFilterTest extends UnitTestCase
{
    /**
     * @return  array
     *
     * @since   6.2.0
     */
    public static function emailToPunycodeProvider(): array
    {
        return [
            'ascii address is unchanged'        => ['<a href="mailto:test@example.com">x</a>', '<a href="mailto:test@example.com">x</a>'],
            'ascii address with query'          => ['<a href="mailto:test@example.com?subject=a">x</a>', '<a href="mailto:test@example.com?subject=a">x</a>'],
            'utf-8 domain is converted'         => ['<a href="mailto:test@exämple.com">x</a>', '<a href="mailto:test@xn--exmple-cua.com">x</a>'],
            'utf-8 domain with query'           => ['<a href="mailto:test@exämple.com?subject=a">x</a>', '<a href="mailto:test@xn--exmple-cua.com?subject=a">x</a>'],
            'sub domain is converted'           => ['<a href="mailto:test@mail.exämple.com">x</a>', '<a href="mailto:test@mail.xn--exmple-cua.com">x</a>'],
            'text without mailto is unchanged'  => ['Contact test@exämple.com today', 'Contact test@exämple.com today'],
            'unterminated address is unchanged' => ['"mailto:test@exämple.com', '"mailto:test@exämple.com'],
        ];
    }

    /**
     * @param   string  $input     The text to filter
     * @param   string  $expected  The expected result
     *
     * @return  void
     *
     * @since   6.2.0
     */
    #[DataProvider('emailToPunycodeProvider')]
    #[TestDox('Test that emailToPunycode converts mailto addresses and leaves everything else alone')]
    public function testEmailToPunycode(string $input, string $expected)
    {
        $this->assertSame($expected, (new InputFilter())->emailToPunycode($input));
    }

    /**
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('Test that emailToPunycode runs in linear time for long runs of dots')]
    public function testEmailToPunycodeLongDotRun()
    {
        $filter = new InputFilter();
        $input  = '"mailto:a@' . str_repeat('.', 400000);

        $start  = microtime(true);
        $result = $filter->emailToPunycode($input);
        $time   = microtime(true) - $start;

        $this->assertSame($input, $result);
        // A quadratic pattern needs many seconds for this input, a linear one a few milliseconds
        $this->assertLessThan(1.0, $time);
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('Test that emailToPunycode runs in linear time for many repeated mailto prefixes')]
    #[RunInSeparateProcess]
    public function testEmailToPunycodeRepeatedPrefix()
    {
        // With JIT the quadratic case aborts early (JIT stack limit), without JIT it runs for seconds.
        // Own process: PHP caches the compiled pattern, so pcre.jit must be off before its first use.
        ini_set('pcre.jit', '0');

        $filter = new InputFilter();
        $input  = str_repeat('"mailto:', 12500) . 'a@b.c';

        $start  = microtime(true);
        $result = $filter->emailToPunycode($input);
        $time   = microtime(true) - $start;

        $this->assertSame($input, $result);
        $this->assertLessThan(1.0, $time);
    }
}
