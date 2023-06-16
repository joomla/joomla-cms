<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Language;

use Joomla\CMS\Language\LanguageAwareTrait;
use Joomla\CMS\Language\Language;
use Joomla\Tests\Unit\UnitTestCase;
use UnexpectedValueException;

/**
 * Test class for \Joomla\CMS\Language\LanguageAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class LanguageAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  The user language can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
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

        $this->assertEquals($language, $trait->get());
    }

    /**
     * @testdox  The user language can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetLanguageThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);

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
