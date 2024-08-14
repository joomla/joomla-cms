<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Updater
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
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
 * @since       5.1.0
 */
class ConstraintCheckerTest extends UnitTestCase
{
    /**
     * @var    ConstraintChecker
     * @since  5.1.0
     */
    protected $checker;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function setUp(): void
    {
        $this->checker = new ConstraintChecker(Version::MAJOR_VERSION . '.x');
    }

    /**
     * Overrides the parent tearDown method.
     *
     * @return  void
     *
     * @see     \PHPUnit\Framework\TestCase::tearDown()
     * @since   5.1.0
     */
    protected function tearDown(): void
    {
        unset($this->checker);
        parent::tearDown();
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testCheckMethodReturnsFalseIfPlatformIsMissing()
    {
        $constraint = [];
        $this->assertFalse($this->checker->check($constraint));
    }

    /**
     * @return void
     *
     * @since   5.1.0
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
     * @since   5.1.0
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
     * @since   5.1.0
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
     * @since   5.1.0
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
     * @since   5.1.0
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
     * @since   5.1.0
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
            [(array) ["name" => "joomla", "version" => "6.*"], true],
            [(array) ["name" => "joomla", "version" => ""], true],
            [(array) ["name" => "joomla", "version" => ".*"], true],
            [(array) ["name" => "joomla", "version" => JVERSION], true],
            [(array) ["name" => "joomla", "version" => "5.*"], false],
        ];
    }

    /**
     * Internal helper method to get access to protected methods
     *
     * @since   5.1.0
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
