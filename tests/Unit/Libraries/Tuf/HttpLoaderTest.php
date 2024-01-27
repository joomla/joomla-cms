<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Tuf
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Tuf;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\HttpFactoryInterface;
use Joomla\CMS\TUF\HttpLoader;
use Joomla\Http\Response;
use Joomla\Tests\Unit\UnitTestCase;
use Laminas\Diactoros\Stream;
use Tuf\Exception\RepoFileNotFound;

/**
 * Test class for HttpLoader
 *
 * @package     Joomla.UnitTest
 * @subpackage  Tuf
 * @since       __DEPLOY_VERSION__
 */
class HttpLoaderTest extends UnitTestCase
{
    protected const REPOPATHMOCK = 'https://example.org/tuftest/';

    protected HttpLoader $object;

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testLoaderQueriesCorrectUrl()
    {
        $responseBody = $this->createMock(Stream::class);

        Factory::getContainer()->set(
            HttpFactoryInterface::class,
            $this->getHttpFactoryMock(200, $responseBody, 'root.json')
        );

        $this->object->load('root.json', 2048);
    }

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
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

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
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

    /**
     * @param int     $responseCode
     * @param Stream  $responseBody
     * @param string  $expectedFile
     *
     * @since   __DEPLOY_VERSION__
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|(\stdClass&\PHPUnit\Framework\MockObject\MockObject)
     */
    protected function getHttpFactoryMock(int $responseCode, Stream $responseBody, string $expectedFile)
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

    /**
     * @return void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setUp(): void
    {
        $this->object = new HttpLoader(self::REPOPATHMOCK);

        parent::setUp();
    }
}
