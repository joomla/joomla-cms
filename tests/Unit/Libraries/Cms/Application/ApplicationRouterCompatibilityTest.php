<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Application;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Router;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the compatibility boundary for legacy application router access.
 *
 * @since  __DEPLOY_VERSION__
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ApplicationRouterCompatibilityTest extends UnitTestCase
{
    /**
     * @since  __DEPLOY_VERSION__
     */
    public function testWebApplicationInterfaceDoesNotRequireLegacyRouterAccess(): void
    {
        $this->assertFalse(method_exists(CMSWebApplicationInterface::class, 'getRouter'));
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    #[DataProvider('applicationClasses')]
    public function testRouterAccessRequiresCompatibility(string $applicationClass): void
    {
        // The guard must run before accessing the global application or router services.
        Factory::$application = null;
        Factory::$container   = null;

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('compatibility');

        $applicationClass::getRouter();
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public static function applicationClasses(): array
    {
        return [
            'base application' => [CMSApplication::class],
            'site application' => [SiteApplication::class],
            'administrator'    => [AdministratorApplication::class],
            'console'          => [ConsoleApplication::class],
        ];
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    #[DataProvider('compatibleRouterCalls')]
    public function testCompatibilityPreservesRouterSelection(string $applicationClass, array $arguments, string $client): void
    {
        \define('COMPAT_JOOMLA_7', true);

        $application = $this->getMockBuilder(CMSApplication::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['doExecute', 'get', 'getName'])
            ->getMock();
        $application->method('getName')->willReturn('current');
        $application->expects($this->once())->method('get')->with('sef')->willReturn(1);

        $router               = new Router();
        Factory::$application = $application;
        Factory::$container   = new Container();
        Factory::$container->set('JRouter' . ucfirst($client), $router, true);

        $deprecations = [];
        set_error_handler(static function (int $severity, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $this->assertSame($router, $applicationClass::getRouter(...$arguments));
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('getRouter()', $deprecations[0]);
        $this->assertStringContainsString('deprecated', $deprecations[0]);
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public static function compatibleRouterCalls(): array
    {
        return [
            'base defaults to current application' => [CMSApplication::class, [], 'current'],
            'site default'                         => [SiteApplication::class, [], 'site'],
            'administrator default'                => [AdministratorApplication::class, [], 'administrator'],
            'base explicit client'                 => [CMSApplication::class, ['custom', ['mode' => 0]], 'custom'],
            'site explicit client'                 => [SiteApplication::class, ['custom'], 'custom'],
            'administrator explicit client'        => [AdministratorApplication::class, ['custom'], 'custom'],
            'console explicit client'              => [ConsoleApplication::class, ['custom'], 'custom'],
        ];
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public function testConsoleStillRequiresAnExplicitRouterNameInCompatibilityMode(): void
    {
        \define('COMPAT_JOOMLA_7', true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A router name must be set in console application.');

        ConsoleApplication::getRouter();
    }
}
