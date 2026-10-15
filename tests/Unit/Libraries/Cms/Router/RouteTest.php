<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Router;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests router service resolution and the compatibility-only legacy fallback.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 * @since  __DEPLOY_VERSION__
 */
class RouteTest extends UnitTestCase
{
    /**
     * @dataProvider compatibilityModes
     *
     * @since  __DEPLOY_VERSION__
     */
    public function testLinkUsesTheContainerRouterWithoutALegacyApplication(bool $compatibility): void
    {
        if ($compatibility) {
            \define('COMPAT_JOOMLA_7', true);
        }

        $router = $this->createMock(Router::class);
        $router->expects($this->once())->method('build')
            ->with('index.php?option=com_content')
            ->willReturn(new Uri('/article?first=1&second=2'));

        Factory::$application = null;
        Factory::$container   = new Container();
        Factory::$container->set('SiteRouter', $router, true);

        $this->assertSame('/article?first=1&amp;second=2', Route::link('site', 'index.php?option=com_content'));
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public static function compatibilityModes(): array
    {
        return [
            'compatibility disabled' => [false],
            'compatibility enabled'  => [true],
        ];
    }

    /**
     * @dataProvider missingRouterServices
     *
     * @since  __DEPLOY_VERSION__
     */
    public function testMissingContainerRouterRespectsCompatibility(bool $compatibility, string $serviceState): void
    {
        if ($compatibility) {
            \define('COMPAT_JOOMLA_7', true);
        }

        $application = $this->getMockBuilder(CMSApplication::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['doExecute', 'get'])
            ->getMock();
        $application->expects($compatibility ? $this->once() : $this->never())
            ->method('get')->with('sef')->willReturn(1);

        $router = $this->createMock(Router::class);
        $router->expects($compatibility ? $this->once() : $this->never())
            ->method('build')->with('index.php?option=com_example')
            ->willReturn(new Uri('/legacy-result'));

        Factory::$application = $application;
        Factory::$container   = new Container();
        Factory::$container->set('JRouterLegacy', $router, true);

        // A missing key exercises KeyNotFoundException; false and null exercise falsey services.
        if ($serviceState !== 'missing') {
            Factory::$container->set('LegacyRouter', $serviceState === 'false' ? false : null);
        }

        if (!$compatibility) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionCode(500);
        }

        $deprecations = [];
        set_error_handler(static function (int $severity, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $this->assertSame('/legacy-result', Route::link('legacy', 'index.php?option=com_example'));
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $deprecations);
        $this->assertStringContainsString('getRouter()', $deprecations[0]);
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public static function missingRouterServices(): array
    {
        return [
            'missing service without compatibility' => [false, 'missing'],
            'false service without compatibility'   => [false, 'false'],
            'null service without compatibility'    => [false, 'null'],
            'missing service with compatibility'    => [true, 'missing'],
            'false service with compatibility'      => [true, 'false'],
            'null service with compatibility'       => [true, 'null'],
        ];
    }
}
