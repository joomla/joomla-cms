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
 * @since  __DEPLOY_VERSION__
 */
class TotpTest extends UnitTestCase
{
    /**
     * The currently valid passcode is accepted.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
