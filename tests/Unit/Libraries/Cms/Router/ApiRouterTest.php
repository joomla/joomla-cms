<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Router;

use Joomla\CMS\Router\ApiRouter;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Router\Route;
use Joomla\Tests\Unit\UnitTestCase;

class ApiRouterTest extends UnitTestCase
{
    public function testQueryMatchesAnExplicitRoute(): void
    {
        $router = $this->createPartialMock(ApiRouter::class, ['getRoutePath']);
        $router->method('getRoutePath')->willReturn('/v1/example/42');
        $router->addRoutes([
            new Route(['GET'], 'v1/example/:id', 'example.displayItem'),
            new Route(['QUERY'], 'v1/example/:id', 'example.query', [], ['component' => 'com_example']),
        ]);

        $this->assertSame(
            [
                'controller' => 'example',
                'task'       => 'query',
                'vars'       => ['component' => 'com_example', 'id' => '42'],
            ],
            $router->parseApiRoute('QUERY')
        );
        $this->assertSame('displayItem', $router->parseApiRoute('GET')['task']);
    }

    public function testQueryDoesNotMatchAGetOnlyRoute(): void
    {
        $router = $this->createPartialMock(ApiRouter::class, ['getRoutePath']);
        $router->method('getRoutePath')->willReturn('/v1/example');
        $router->createCRUDRoutes('v1/example', 'example');

        $this->expectException(RouteNotFoundException::class);
        $router->parseApiRoute('QUERY');
    }
}
