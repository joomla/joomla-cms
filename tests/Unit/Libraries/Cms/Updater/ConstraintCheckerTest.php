<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Updater
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Updater;

use Joomla\CMS\Factory;
use Joomla\CMS\Updater\ConstraintChecker;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Constraint Checker.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Updater
 * @since       __DEPLOY_VERSION__
 */
class ConstraintCheckerTest extends UnitTestCase
{
    /**
     * @var    ConstraintChecker
     * @since  __DEPLOY_VERSION__
     */
    protected $checker;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        $this->checker = new ConstraintChecker();
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     \PHPUnit\Framework\TestCase::tearDown()
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        unset($this->checker);
        parent::tearDown();
    }

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckMethodReturnsFalseIfPlatformIsMissing()
    {
        $constraint = [];
        $this->assertFalse($this->checker->check($constraint));
    }

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckMethodReturnsTrueIfPlatformIsOnlyConstraint()
    {
        $constraint = ['targetplatform' => (array) ["name" => "joomla", "version" => JVERSION]];
        $this->assertTrue($this->checker->check($constraint));
    }

    /**
     * Tests the checkSupportedDatabases method
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider supportedDatabasesDataProvider
     */
    public function testCheckSupportedDatabases($currentDatabase, $supportedDatabases, $expectedResult)
    {
        $dbMock = $this->createMock(DatabaseDriver::class);
        $dbMock->method('getServerType')->willReturn($currentDatabase['type']);
        $dbMock->method('getVersion')->willReturn($currentDatabase['version']);
        Factory::$database = $dbMock;

        $method = $this->getPublicMethod('checkSupportedDatabases');
        $result = $method->invoke($this->checker, $supportedDatabases);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the checkPhpMinimum method
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider targetplatformDataProvider
     */
    public function testCheckPhpMinimumReturnFalseForFuturePhp()
    {
        $method = $this->getPublicMethod('checkPhpMinimum');

        $this->assertFalse($method->invoke($this->checker, '99.9.9'));
    }

    /**
     * Tests the checkTargetplatform method
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider targetplatformDataProvider
     */
    public function testCheckTargetplatform($targetPlatform, $expectedResult)
    {
        $method = $this->getPublicMethod('checkTargetplatform');
        $result = $method->invoke($this->checker, $targetPlatform);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Data provider for testCheckSupportedDatabases method
     *
     * @since   __DEPLOY_VERSION__
     *
     * @return array[]
     */
    protected function supportedDatabasesDataProvider()
    {
        return [
            [
                ['type' => 'mysql', 'version' => '5.7.37-log-cll-lve'],
                (array) ['mysql' => '5.6', 'mariadb' => '10.3'],
                true,
            ],
            [
                ['type' => 'mysql', 'version' => '5.6.0-log-cll-lve'],
                (array) ['mysql' => '5.6', 'mariadb' => '10.3'],
                true,
            ],
            [
                ['type' => 'mysql', 'version' => '10.3.34-MariaDB-0+deb10u1'],
                (array) ['mysql' => '5.6', 'mariadb' => '10.3'],
                true,
            ],
            [
                ['type' => 'mysql', 'version' => '5.7.37-log-cll-lve'],
                (array) ['mysql' => '5.8', 'mariadb' => '10.3'],
                false,
            ],
            [
                ['type' => 'pgsql', 'version' => '14.3'],
                (array) ['mysql' => '5.8', 'mariadb' => '10.3'],
                false,
            ],
            [
                ['type' => 'mysql', 'version' => '10.3.34-MariaDB-0+deb10u1'],
                (array) ['mysql' => '5.6', 'mariadb' => '10.4'],
                false,
            ],
            [
                ['type' => 'mysql', 'version' => '5.5.5-10.3.34-MariaDB-0+deb10u1'],
                (array) ['mysql' => '5.6', 'mariadb' => '10.3'],
                true,
            ],
        ];
    }

    /**
     * Data provider for testCheckTargetplatform method
     *
     * @since   __DEPLOY_VERSION__
     *
     * @return array[]
     */
    protected function targetplatformDataProvider()
    {
        return [
            [(array) ["name" => "foobar", "version" => "1.*"], false],
            [(array) ["name" => "foobar", "version" => "4.*"], false],
            [(array) ["name" => "joomla", "version" => "1.*"], false],
            [(array) ["name" => "joomla", "version" => "3.1.2"], false],
            [(array) ["name" => "joomla", "version" => "6.*"], false],
            [(array) ["name" => "joomla", "version" => ""], true],
            [(array) ["name" => "joomla", "version" => ".*"], true],
            [(array) ["name" => "joomla", "version" => JVERSION], true],
            [(array) ["name" => "joomla", "version" => "5.*"], true],
        ];
    }

    /**
     * Internal helper method to get access to protected methods
     *
     * @since   __DEPLOY_VERSION__
     *
     * @param $method
     *
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getPublicMethod($method)
    {
        $reflectionClass = new \ReflectionClass($this->checker);
        $method          = $reflectionClass->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }
}
