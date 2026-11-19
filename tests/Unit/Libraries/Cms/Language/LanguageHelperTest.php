<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Language
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Language;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Language\LanguageHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       5.4.11
 */
class LanguageHelperTest extends UnitTestCase
{
    /**
     * @param   mixed    $tag       The tag
     * @param   boolean  $expected  Expected result
     *
     * @dataProvider  tagProvider
     *
     * @return  void
     *
     * @since   5.4.11
     */
    public function testIsValidTag($tag, bool $expected): void
    {
        $this->assertSame($expected, LanguageHelper::isValidTag($tag));
    }

    /**
     * @return  array
     *
     * @since   5.4.11
     */
    public static function tagProvider(): array
    {
        return [
            'en-GB'            => ['en-GB', true],
            'sr-YU'            => ['sr-YU', true],
            'three letters'    => ['ast-ES', true],
            'srp-ME'           => ['srp-ME', true],
            'four letters'     => ['abcd-ES', false],
            'one letter'       => ['e-GB', false],
            'numeric region'   => ['es-419', false],
            'lowercase region' => ['en-gb', false],
            'uppercase lang'   => ['EN-GB', false],
            'no region'        => ['en', false],
            'dotdot'           => ['..', false],
            'traversal'        => ['../../../tmp/x', false],
            'backslash'        => ['..\\..\\x', false],
            'trailing dotdot'  => ['en-GB/..', false],
            'trailing newline' => ["en-GB\n", false],
            'empty'            => ['', false],
            'array'            => [['en-GB'], false],
        ];
    }

    /**
     * @return  void
     *
     * @since   5.4.11
     */
    public function testExistsRejectsTraversal(): void
    {
        $this->assertFalse(LanguageHelper::exists('../../', JPATH_ROOT . '/tests'));
        $this->assertFalse(LanguageHelper::exists('..', JPATH_ROOT));
    }
}
