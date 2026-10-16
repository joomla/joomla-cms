<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Router;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\SiteRouter;
use Joomla\Component\Contact\Site\Service\Router as ContactRouter;
use Joomla\Component\Content\Site\Service\Router as ContentRouter;
use Joomla\Component\Newsfeeds\Site\Service\Router as NewsfeedsRouter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;
use Joomla\DI\Container;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that component redirects taint the shared router without compatibility mode.
 *
 * @since  __DEPLOY_VERSION__
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ComponentRouterTaintTest extends UnitTestCase
{
    /**
     * @since  __DEPLOY_VERSION__
     */
    #[DataProvider('componentRouters')]
    public function testOutdatedAliasTaintsTheSharedSiteRouter(string $routerClass, string $parseMethod): void
    {
        $query = $this->createStub(QueryInterface::class);

        foreach (['select', 'from', 'where', 'bind'] as $method) {
            $query->method($method)->willReturnSelf();
        }

        $database = $this->createMock(DatabaseInterface::class);
        $database->method('quoteName')->willReturnArgument(0);
        $database->expects($this->once())->method('createQuery')->willReturn($query);
        $database->expects($this->once())->method('setQuery')->with($query);
        $database->expects($this->once())->method('loadResult')->willReturn('current-alias');

        // Parsing an ID needs only the database; bypass unrelated application/menu setup.
        $componentRouter = (new \ReflectionClass($routerClass))->newInstanceWithoutConstructor();
        (new \ReflectionProperty($routerClass, 'db'))->setValue($componentRouter, $database);

        $siteRouter           = (new \ReflectionClass(SiteRouter::class))->newInstanceWithoutConstructor();
        Factory::$application = null;
        Factory::$container   = new Container();
        Factory::$container->set(SiteRouter::class, $siteRouter, true);

        $this->assertFalse(\defined('COMPAT_JOOMLA_7'));
        $this->assertFalse($siteRouter->isTainted());
        $this->assertSame(42, $componentRouter->$parseMethod('42-old-alias', []));
        $this->assertTrue($siteRouter->isTainted());
    }

    /**
     * @since  __DEPLOY_VERSION__
     */
    public static function componentRouters(): array
    {
        return [
            'article'  => [ContentRouter::class, 'getArticleId'],
            'contact'  => [ContactRouter::class, 'getContactId'],
            'newsfeed' => [NewsfeedsRouter::class, 'getNewsfeedId'],
        ];
    }
}
