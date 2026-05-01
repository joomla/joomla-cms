<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Encrypt
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Encrypt\AES;

use Joomla\CMS\Encrypt\AES\OpenSSL;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for OpenSSL AES adapter.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Encrypt
 * @since       __DEPLOY_VERSION__
 */
class OpenSSLTest extends UnitTestCase
{
    /**
     * Tests that ECB mode is accepted and selected.
     *
     * @return  void
     */
    public function testSetEncryptionModeAcceptsEcbMode(): void
    {
        if (!\function_exists('openssl_get_cipher_methods')) {
            $this->markTestSkipped('OpenSSL extension is not available.');
        }

        $algorithms = openssl_get_cipher_methods();

        if (!\in_array('aes-128-ecb', $algorithms, true)) {
            $this->markTestSkipped('aes-128-ecb is not available in this OpenSSL build.');
        }

        $adapter = new OpenSSL();
        $method  = (new \ReflectionClass($adapter))->getProperty('method');

        $adapter->setEncryptionMode('ecb', 128);

        $this->assertSame('aes-128-ecb', $method->getValue($adapter));
    }
}
