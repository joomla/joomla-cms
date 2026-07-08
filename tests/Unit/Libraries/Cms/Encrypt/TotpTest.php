<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Encrypt
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Encrypt;

use Joomla\CMS\Encrypt\Totp;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Encrypt\Totp
 *
 * @since  5.4.8
 */
class TotpTest extends UnitTestCase
{
    /**
     * The currently valid passcode is accepted.
     *
     * @return  void
     * @since   5.4.8
     */
    public function testCheckCodeAcceptsValidPasscode()
    {
        $totp   = new Totp();
        $secret = $totp->generateSecret();

        $this->assertTrue($totp->checkCode($secret, $totp->getCode($secret)));
    }

    /**
     * A numeric string that only equals the passcode under type juggling must be rejected.
     *
     * @return  void
     * @since   5.4.8
     */
    public function testCheckCodeRejectsTypeJuggledPasscode()
    {
        $totp   = new Totp();
        $secret = $totp->generateSecret();
        $code   = $totp->getCode($secret);

        $this->assertFalse($totp->checkCode($secret, '+' . $code));
        $this->assertFalse($totp->checkCode($secret, ' ' . $code));
    }
}
