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
        $this->assertThat(
            HtmlTelephone::tel('1.9419555555', 'US'),
            $this->stringContains('(941) 955-5555'),
            'Testing for US format'
        );

        $this->assertThat(
            HtmlTelephone::tel('49.15123456789', 'EPP'),
            $this->stringContains('+49.15123456789'),
            'Testing for EPP format'
        );

        $this->assertThat(
            HtmlTelephone::tel('82.12345678', 'ITU-T'),
            $this->stringContains('+82 12 34 56 78'),
            'Testing for ITU-T format'
        );

        $this->assertThat(
            HtmlTelephone::tel('1.9413216789', 'ARPA'),
            $this->stringContains('+9.8.7.6.1.2.3.1.4.9.1.e164.arpa'),
            'Testing for ARPA format'
        );
    }
}
