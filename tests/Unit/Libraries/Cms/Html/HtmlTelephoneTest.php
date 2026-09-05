<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\HTML\Helpers\Telephone as HtmlTelephone;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for HtmlTelephone.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       3.1
 */
class HtmlTelephoneTest extends UnitTestCase
{
    /**
     * Tests the HtmlTelephone::tel method.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function testTel()
    {
        $this->assertSame(
            '(941) 955-5555',
            HtmlTelephone::tel('1.9419555555', 'US'),
            'Testing for US format'
        );

        $this->assertSame(
            '+49.15123456789',
            HtmlTelephone::tel('49.15123456789', 'EPP'),
            'Testing for EPP format'
        );

        $this->assertSame(
            '+82 12 34 56 78',
            HtmlTelephone::tel('82.12345678', 'ITU-T'),
            'Testing for ITU-T format'
        );

        $this->assertSame(
            '+9.8.7.6.1.2.3.1.4.9.1.e164.arpa',
            HtmlTelephone::tel('1.9413216789', 'ARPA'),
            'Testing for ARPA format'
        );
    }
}
