<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Version
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms;

use Joomla\CMS\Factory;
use Joomla\CMS\Updater\ConstraintChecker;
use Joomla\Database\DatabaseDriver;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Version.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Version
 * @since       __DEPLOY_VERSION__
 */
class ConstraintCheckerTest extends UnitTestCase
{
	/**
	 * @var    ConstraintChecker
	 * @since  3.0
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
	protected function setUp():void
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
	protected function tearDown():void
	{
		unset($this->checker);
		parent::tearDown();
	}

	public function testCheckMethodReturnsFalseIfPlatformIsMissing()
	{
		$constraint = [];
		$this->assertFalse($this->checker->check($constraint));
	}

	public function testCheckMethodReturnsTrueIfPlatformIsOnlyConstraint()
	{
		$constraint = ['targetplatform' => (object) ["name" => "joomla", "version" => "4.*"]];
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
				(object) ['mysql' => '5.6', 'mariadb' => '10.3'],
				true
			],
			[
				['type' => 'mysql', 'version' => '5.6.0-log-cll-lve'],
				(object) ['mysql' => '5.6', 'mariadb' => '10.3'],
				true
			],
			[
				['type' => 'mysql', 'version' => '10.3.34-MariaDB-0+deb10u1'],
				(object) ['mysql' => '5.6', 'mariadb' => '10.3'],
				true
			],
			[
				['type' => 'mysql', 'version' => '5.7.37-log-cll-lve'],
				(object) ['mysql' => '5.8', 'mariadb' => '10.3'],
				false
			],
			[
				['type' => 'pgsql', 'version' => '14.3'],
				(object) ['mysql' => '5.8', 'mariadb' => '10.3'],
				false
			],
			[
				['type' => 'mysql', 'version' => '10.3.34-MariaDB-0+deb10u1'],
				(object) ['mysql' => '5.6', 'mariadb' => '10.4'],
				false
			],
			[
				['type' => 'mysql', 'version' => '5.5.5-10.3.34-MariaDB-0+deb10u1'],
				(object) ['mysql' => '5.6', 'mariadb' => '10.3'],
				true
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
			[(object) ["name" => "foobar", "version" => "1.*"], false],
			[(object) ["name" => "foobar", "version" => "4.*"], false],
			[(object) ["name" => "joomla", "version" => "1.*"], false],
			[(object) ["name" => "joomla", "version" => "3.1.2"], false],
			[(object) ["name" => "joomla", "version" => ""], true],
			[(object) ["name" => "joomla", "version" => ".*"], true],
			[(object) ["name" => "joomla", "version" => JVERSION], true],
			[(object) ["name" => "joomla", "version" => "4.*"], true],
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
		$method = $reflectionClass->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}
}
