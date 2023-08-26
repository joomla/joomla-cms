<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Version
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms;

use Joomla\CMS\Version;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Version.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Version
 * @since       3.0
 */
class VersionTest extends UnitTestCase
{
    /**
     * @var    Version
     * @since  3.0
     */
    protected $version;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function setUp(): void
    {
        $this->version = new Version();
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     \PHPUnit\Framework\TestCase::tearDown()
     * @since   3.6
     */
    protected function tearDown(): void
    {
        unset($this->version);
        parent::tearDown();
    }

    /**
     * Tests the isCompatible method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testIsCompatible()
    {
        $this->assertTrue($this->version->isCompatible('2.5'));
    }

    /**
     * Tests the getHelpVersion method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetHelpVersion()
    {
        $this->assertIsString($this->version->getHelpVersion());
    }

    /**
     * Tests the getShortVersion method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetShortVersion()
    {
        $this->assertEquals(JVERSION, $this->version->getShortVersion());
    }

    /**
     * Tests the getLongVersion method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetLongVersion()
    {
        $this->assertIsString($this->version->getLongVersion());
    }

    /**
     * Tests the getUserAgent method for a mask not containing the Mozilla version string
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetUserAgentForMaskNotContainingMozillaVersion()
    {
        $this->assertStringNotContainsString('Mozilla/5.0 ', $this->version->getUserAgent('', false, true));
    }

    /**
     * Tests the getUserAgent method for a mask containing the Mozilla version string
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetUserAgentForMaskContainingMozillaVersion()
    {
        $this->assertStringContainsString('Mozilla/5.0 ', $this->version->getUserAgent('', true, true));
    }

    /**
     * Tests the getUserAgent method for a null component string
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetUserAgentForEmptyComponentString()
    {
        $this->assertStringContainsString('Framework', $this->version->getUserAgent('', false, true));
    }

    /**
     * Tests the getUserAgent method for a component string matching the specified option
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetUserAgentForComponentMatchingTheSpecifiedOption()
    {
        $this->assertStringContainsString('Component_test', $this->version->getUserAgent('Component_test', false, true));
    }
}
