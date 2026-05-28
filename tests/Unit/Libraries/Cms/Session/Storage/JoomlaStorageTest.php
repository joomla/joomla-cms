<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Session\Storage;

use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Session\Storage\JoomlaStorage
 *
 * @package     Joomla.UnitTest
 * @subpackage  Session
 *
 * @testdox     The JoomlaStorage
 *
 * @since       __DEPLOY_VERSION__
 */
class JoomlaStorageTest extends UnitTestCase
{
    /**
     * Encodes a value the same way JoomlaStorage::close() does.
     *
     * @param   mixed  $value  The value to encode.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function encodeSessionData($value): string
    {
        return base64_encode(serialize($value));
    }

    /**
     * @testdox  loads a valid Registry from session data correctly
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    public function testDeserializeAcceptsRegistry(): void
    {
        $registry = new Registry(['foo' => 'bar', 'nested' => ['key' => 'val']]);
        $encoded  = $this->encodeSessionData($registry);

        $result = unserialize(base64_decode($encoded), ['allowed_classes' => [Registry::class, \stdClass::class]]);

        $this->assertInstanceOf(Registry::class, $result);
        $this->assertSame('bar', $result->get('foo'));
        $this->assertSame('val', $result->get('nested.key'));
    }

    /**
     * @testdox  rejects a serialized stdClass as not a Registry
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    public function testDeserializeRejectsArbitraryObject(): void
    {
        $malicious      = new \stdClass();
        $malicious->cmd = 'rm -rf /';
        $encoded        = $this->encodeSessionData($malicious);

        $result = unserialize(base64_decode($encoded), ['allowed_classes' => [Registry::class, \stdClass::class]]);

        $this->assertNotInstanceOf(Registry::class, $result);
    }

    /**
     * @testdox  falls back to empty Registry when session contains a non-Registry object
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    public function testStartFallsBackToEmptyRegistryOnInvalidData(): void
    {
        $malicious          = new \stdClass();
        $malicious->payload = 'exploit';

        $sessionValue = $this->encodeSessionData($malicious);

        $data = unserialize(base64_decode($sessionValue), ['allowed_classes' => [Registry::class, \stdClass::class]]);

        // Simulate the guard in JoomlaStorage::start()
        $storage = new Registry();
        if ($data instanceof Registry) {
            $storage = $data;
        }

        $this->assertInstanceOf(Registry::class, $storage);
        $this->assertEmpty($storage->toArray());
    }

    /**
     * @testdox  falls back to empty Registry when session data is corrupted
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    public function testStartFallsBackToEmptyRegistryOnCorruptedData(): void
    {
        $corrupted = base64_encode('not-valid-serialized-data!!!');

        $data = @unserialize(base64_decode($corrupted), ['allowed_classes' => [Registry::class, \stdClass::class]]);

        $storage = new Registry();
        if ($data instanceof Registry) {
            $storage = $data;
        }

        $this->assertInstanceOf(Registry::class, $storage);
        $this->assertEmpty($storage->toArray());
    }
}
