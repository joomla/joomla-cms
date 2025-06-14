<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Tuf
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Tuf;

use Joomla\CMS\Http\Http;
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
 * @since       5.1.0
 */
class HttpLoaderTest extends UnitTestCase
{
    protected const REPOPATHMOCK = 'https://example.org/tuftest/';

    protected HttpLoader $object;

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testLoaderQueriesCorrectUrl()
    {
        $responseBody = $this->createMock(Stream::class);

        $object = new HttpLoader(
            self::REPOPATHMOCK,
            $this->getHttpMock(200, $responseBody, 'root.json')
        );

        $object->load('root.json', 2048);
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testLoaderForwardsReturnedBodyFromHttpClient()
    {
        $responseBody = $this->createMock(Stream::class);

        $object = new HttpLoader(
            self::REPOPATHMOCK,
            $this->getHttpMock(200, $responseBody, 'root.json')
        );

        $this->assertSame(
            $responseBody,
            $object->load('root.json', 2048)->wait()
        );
    }

    /**
     * @return void
     *
     * @since   5.1.0
     */
    public function testLoaderThrowsExceptionForNon200Response()
    {
        $this->expectException(RepoFileNotFound::class);

        $responseBody = $this->createMock(Stream::class);

        $object = new HttpLoader(
            self::REPOPATHMOCK,
            $this->getHttpMock(400, $responseBody, 'root.json')
        );

        $object->load('root.json', 2048);
    }

    /**
     * @param int     $responseCode
     * @param Stream  $responseBody
     * @param string  $expectedFile
     *
     * @since   5.1.0
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|(\stdClass&\PHPUnit\Framework\MockObject\MockObject)
     */
    protected function getHttpMock(int $responseCode, Stream $responseBody, string $expectedFile)
    {
        $responseMock = $this->createMock(Response::class);
        $responseMock->method('__get')->with('code')->willReturn($responseCode);
        $responseMock->method('getBody')->willReturn($responseBody);

        $httpClientMock = $this->createMock(Http::class);
        $httpClientMock->expects($this->once())
            ->method('get')
            ->with(self::REPOPATHMOCK . $expectedFile)
            ->willReturn($responseMock);

        return $httpClientMock;
    }
}
