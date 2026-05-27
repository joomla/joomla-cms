<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Application;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Application\ApplicationHelper
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @testdox     The ApplicationHelper
 *
 * @since       5.4.0
 */
class ApplicationHelperTest extends UnitTestCase
{
    /**
     * The original application stored before each test.
     *
     * @var    CMSApplicationInterface|null
     * @since  5.4.0
     */
    private $originalApplication;

    /**
     * Set up before each test: stash and replace Factory::$application.
     *
     * @return void
     * @since  5.4.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalApplication = Factory::$application;

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('get')->willReturnMap([
            ['secret', null, 'test-secret-key'],
        ]);

        Factory::$application = $app;
    }

    /**
     * Restore Factory::$application after each test.
     *
     * @return void
     * @since  5.4.0
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->originalApplication;

        parent::tearDown();
    }

    /**
     * @testdox  returns an HMAC-SHA256 hash of the seed keyed by the application secret
     *
     * @return void
     * @since  5.4.0
     */
    public function testGetHashReturnHmacSha256()
    {
        $seed   = 'some-seed-value';
        $secret = 'test-secret-key';

        $result   = ApplicationHelper::getHash($seed);
        $expected = hash_hmac('sha256', $seed, $secret);

        $this->assertSame($expected, $result);
    }

    /**
     * @testdox  returns a 64-character hex string (SHA-256 output)
     *
     * @return void
     * @since  5.4.0
     */
    public function testGetHashOutputLength()
    {
        $result = ApplicationHelper::getHash('any-seed');

        $this->assertSame(64, \strlen($result));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $result);
    }

    /**
     * @testdox  produces different hashes for different seeds
     *
     * @return void
     * @since  5.4.0
     */
    public function testGetHashDifferentSeedsProduceDifferentHashes()
    {
        $hash1 = ApplicationHelper::getHash('seed-one');
        $hash2 = ApplicationHelper::getHash('seed-two');

        $this->assertNotSame($hash1, $hash2);
    }

    /**
     * @testdox  is not equivalent to a plain MD5 concatenation of secret and seed
     *
     * @return void
     * @since  5.4.0
     */
    public function testGetHashIsNotMd5Concatenation()
    {
        $seed   = 'some-seed-value';
        $secret = 'test-secret-key';

        $result    = ApplicationHelper::getHash($seed);
        $legacyMd5 = md5($secret . $seed);

        $this->assertNotSame($legacyMd5, $result);
    }

    /**
     * @testdox  handles an empty seed without error
     *
     * @return void
     * @since  5.4.0
     */
    public function testGetHashWithEmptySeed()
    {
        $result   = ApplicationHelper::getHash('');
        $expected = hash_hmac('sha256', '', 'test-secret-key');

        $this->assertSame($expected, $result);
    }
}
