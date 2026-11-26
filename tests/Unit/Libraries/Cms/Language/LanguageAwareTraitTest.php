<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Language;

use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageAwareTrait;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for \Joomla\CMS\Language\LanguageAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       4.4.0
 */
class LanguageAwareTraitTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The user language can be set and accessed by the trait')]
    public function testGetSetLanguage()
    {
        $language = new Language();
        $trait    = new class () {
            use LanguageAwareTrait;

            public function get(): Language
            {
                return $this->getLanguage();
            }
        };

        $trait->setLanguage($language);

        $this->assertSame($language, $trait->get());
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The user language can be set and accessed by the trait')]
    public function testGetLanguageThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $trait = new class () {
            use LanguageAwareTrait;

            public function get(): Language
            {
                return $this->getLanguage();
            }
        };

        $trait->get();
    }
}
