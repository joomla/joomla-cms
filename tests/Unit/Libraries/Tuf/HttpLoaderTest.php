<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Tuf;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactoryInterface;
use Joomla\Http\Response;
use Joomla\CMS\TUF\HttpLoader;
use Joomla\Tests\Unit\UnitTestCase;
use Laminas\Diactoros\Stream;
use Tuf\Exception\RepoFileNotFound;

class HttpLoaderTest extends UnitTestCase
{
    const REPOPATHMOCK = 'https://example.org/tuftest/';

    protected HttpLoader $object;

    public function testLoaderQueriesCorrectUrl()
    {
        $responseBody = $this->createMock(Stream::class);

        Factory::getContainer()->set(
            HttpFactoryInterface::class,
            $this->getHttpFactoryMock(200, $responseBody, 'root.json')
        );

        $this->object->load('root.json', 2048);
    }

    public function testLoaderForwardsReturnedBodyFromHttpClient()
    {
        $responseBody = $this->createMock(Stream::class);

        Factory::getContainer()->set(
            HttpFactoryInterface::class,
            $this->getHttpFactoryMock(200, $responseBody, 'root.json')
        );

        $this->assertSame(
            $responseBody,
            $this->object->load('root.json', 2048)->wait()
        );
    }

    public function testLoaderThrowsExceptionForNon200Response()
    {
        $this->expectException(RepoFileNotFound::class);

        $responseBody = $this->createMock(Stream::class);

        Factory::getContainer()->set(
            HttpFactoryInterface::class,
            $this->getHttpFactoryMock(400, $responseBody, 'root.json')
        );

        $this->object->load('root.json', 2048);
    }

    protected function getHttpFactoryMock($responseCode, $responseBody, $expectedFile)
    {
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('__get')->with('code')->willReturn($responseCode);
        $responseMock->method('getBody')->willReturn($responseBody);

        $httpClientMock = $this->createMock(Http::class);
        $httpClientMock->expects($this->once())
            ->method('get')
            ->with(self::REPOPATHMOCK . $expectedFile)
            ->willReturn($responseMock);

        $httpFactoryMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getHttp'])
            ->getMock();
        $httpFactoryMock->method('getHttp')->willReturn($httpClientMock);

        return $httpFactoryMock;
    }

    public function setUp(): void
    {
        $this->object = new HttpLoader(self::REPOPATHMOCK);

        parent::setUp();
    }
}
