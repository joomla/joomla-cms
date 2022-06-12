<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Version
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\TUF;

use Joomla\Http\Http;
use Psr\Http\Message\StreamInterface;
use Joomla\CMS\TUF\HttpFileFetcher;
use Tuf\Exception\RepoFileNotFound;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * @coversDefaultClass \Joomla\CMS\TUF\HttpFileFetcher
 */
class HttpFileFetcherTest extends UnitTestCase
{
	/**
	 * The content of the mocked response(s).
	 *
	 * This is deliberately not readable by json_decode(), in order to prove
	 * that the fetcher does not try to parse or process the response content
	 * in any way.
	 *
	 * @var string
	 */
	private $testContent = 'Zombie ipsum reversus ab viral inferno, nam rick grimes malum cerebro.';


	/**
	 * Returns an instance of the file fetcher under test.
	 *
	 * @return HttpFileFetcher
	 *   An instance of the file fetcher under test.
	 */
	private function getFetcher($clientMock): HttpFileFetcher
	{
		return new HttpFileFetcher($clientMock, '/metadata/', '/targets/', "");
	}

	/**
	 * Data provider for testfetchFileError().
	 *
	 * @return array[]
	 *   Sets of arguments to pass to the test method.
	 */
	public function providerFetchFileError(): array
	{
		return [
			[404, RepoFileNotFound::class, 0],
			[403, 'RuntimeException']
		];
	}

	/**
	 * Data provider for testFetchFileIfExistsError().
	 *
	 * @return array[]
	 *   Sets of arguments to pass to the test method.
	 */
	public function providerFileIfExistsError(): array
	{
		return [
			[403, 'RuntimeException']
		];
	}

	/**
	 * Tests various error conditions when fetching a file with fetchFile().
	 *
	 * @param integer $statusCode
	 *   The response status code.
	 * @param string $exceptionClass
	 *   The expected exception class that will be thrown.
	 * @param integer|null $exceptionCode
	 *   (optional) The expected exception code. Defaults to the status code.
	 * @param integer|null $maxBytes
	 *   (optional) The maximum number of bytes to read from the response.
	 *   Defaults to the length of $this->testContent.
	 *
	 * @return void
	 *
	 * @dataProvider providerFetchFileError
	 *
	 * @covers ::fetchFile
	 */
	public function testFetchFileError(
		int $statusCode,
		string $exceptionClass,
		?int $exceptionCode = null,
		?int $maxBytes = null
	): void {
		$clientResponseMock = $this->getMockBuilder(\Joomla\Http\Response::class)->getMock();
		$clientResponseMock->method('getStatusCode')->willReturn($statusCode);

		$clientMock = $this->getMockBuilder(Http::class)->getMock();
		$clientMock->method('get')->willReturn($clientResponseMock);

		$this->expectException($exceptionClass);
		$this->expectExceptionCode($exceptionCode ?? $statusCode);
		$this->getFetcher($clientMock)
			->fetchMetadata('test.json', $maxBytes ?? strlen($this->testContent))
			->wait();
	}

	/**
	 * Tests various error conditions when fetching a file with fetchFileIfExists().
	 *
	 * @param integer $statusCode
	 *   The response status code.
	 * @param string $exceptionClass
	 *   The expected exception class that will be thrown.
	 * @param integer|null $exceptionCode
	 *   (optional) The expected exception code. Defaults to the status code.
	 * @param integer|null $maxBytes
	 *   (optional) The maximum number of bytes to read from the response.
	 *   Defaults to the length of $this->testContent.
	 *
	 * @return void
	 *
	 * @dataProvider providerFileIfExistsError
	 *
	 * @covers ::providerFileIfExists
	 */
	public function testFetchFileIfExistsError(
		int $statusCode,
		string $exceptionClass,
		?int $exceptionCode = null,
		?int $maxBytes = null
	): void {
		$clientResponseMock = $this->getMockBuilder(\Joomla\Http\Response::class)->getMock();
		$clientResponseMock->method('getStatusCode')->willReturn($statusCode);

		$clientMock = $this->getMockBuilder(Http::class)->getMock();
		$clientMock->method('get')->willReturn($clientResponseMock);

		$this->expectException($exceptionClass);
		$this->expectExceptionCode($exceptionCode ?? $statusCode);
		$this->getFetcher($clientMock)
			->fetchMetadataIfExists('test.json', $maxBytes ?? strlen($this->testContent));
	}

	/**
	 * Tests fetching a file without any errors.
	 *
	 * @return void
	 */
	public function testFetchMetadataReturnsCorrectResponseOnSuccessfulFetch(): void
	{
		$clientBodyMock = $this->getMockBuilder(StreamInterface::class)->getMock();
		$clientBodyMock->method('getContents')->willReturn($this->testContent);

		$clientResponseMock = $this->getMockBuilder(\Joomla\Http\Response::class)->getMock();
		$clientResponseMock->method('getStatusCode')->willReturn(200);
		$clientResponseMock->method('getBody')->willReturn($clientBodyMock);

		$clientMock = $this->getMockBuilder(Http::class)->getMock();
		$clientMock->method('get')->willReturn($clientResponseMock);

		$this->assertSame(
			$this->testContent,
			$this->getFetcher($clientMock)->fetchMetadata('test.json', 256)->wait()->getContents()
		);
	}

	/**
	 * Tests fetching a file without any errors.
	 *
	 * @return void
	 */
	public function testFetchMetadataIfExistsReturnsCorrectResponseOnSuccessfulFetch(): void
	{
		$clientBodyMock = $this->getMockBuilder(StreamInterface::class)->getMock();
		$clientBodyMock->method('rewind')->willReturnSelf();
		$clientBodyMock->method('__toString')->willReturn($this->testContent);

		$clientResponseMock = $this->getMockBuilder(\Joomla\Http\Response::class)->getMock();
		$clientResponseMock->method('getStatusCode')->willReturn(200);
		$clientResponseMock->method('getBody')->willReturn($clientBodyMock);

		$clientMock = $this->getMockBuilder(Http::class)->getMock();
		$clientMock->method('get')->willReturn($clientResponseMock);

		$this->assertSame(
			$this->testContent,
			$this->getFetcher($clientMock)->fetchMetadataIfExists('test.json', 256)
		);
	}

	/**
	 * Tests fetching a file without any errors.
	 *
	 * @return void
	 */
	public function testFetchMetadataIfExistsReturnsCorrectResponseOnNotFoundFetch(): void
	{
		$clientBodyMock = $this->getMockBuilder(StreamInterface::class)->getMock();
		$clientBodyMock->method('getContents')->willReturn($this->testContent);

		$clientResponseMock = $this->getMockBuilder(\Joomla\Http\Response::class)->getMock();
		$clientResponseMock->method('getStatusCode')->willReturn(404);
		$clientResponseMock->method('getBody')->willReturn($clientBodyMock);

		$clientMock = $this->getMockBuilder(Http::class)->getMock();
		$clientMock->method('get')->willReturn($clientResponseMock);

		$this->assertNull(
			$this->getFetcher($clientMock)->fetchMetadataIfExists('test.json', 256)
		);
	}

	/**
	 * Tests creating a file fetcher with a repo base URI.
	 *
	 * @return void
	 *
	 * @covers ::createFromUri
	 */
	public function testCreateFromUri(): void
	{
		$this->assertInstanceOf(HttpFileFetcher::class, HttpFileFetcher::createFromUri('https://example.com'));
	}
}
