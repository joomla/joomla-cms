<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Installer;

use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Http\Response;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for InstallerHelper
 *
 * @since   __DEPLOY_VERSION__
 */
class InstallerHelperTest extends UnitTestCase
{
    /**
     * Helper to create a Response object for testing.
     *
     * @param   string  $body         The response body.
     * @param   string  $contentType  The content type header.
     *
     * @return  Response
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function createResponse(string $body, string $contentType = ''): Response
    {
        $headers = [];

        if ($contentType !== '') {
            $headers['content-type'] = [$contentType];
        }

        $response = new Response('php://memory', 200, $headers);

        if ($body !== '') {
            $response->getBody()->write($body);
        }

        return $response;
    }

    /**
     * Test HTML response detection via Content-Type header.
     *
     * @param   string  $contentType  The Content-Type to test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('htmlContentTypeProvider')]
    public function testIsHtmlResponseWithHtmlContentType(string $contentType): void
    {
        $response = $this->createResponse('Some text', $contentType);

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            "Failed asserting that Content-Type '$contentType' is detected as HTML."
        );
    }

    /**
     * Data provider for HTML content types.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function htmlContentTypeProvider(): array
    {
        return [
            'text/html'                          => ['text/html'],
            'text/html with charset'             => ['text/html; charset=UTF-8'],
            'application/xhtml+xml'              => ['application/xhtml+xml'],
            'application/xhtml+xml with charset' => ['application/xhtml+xml; charset=utf-8'],
        ];
    }

    /**
     * Test HTML response detection via HTML tags in the body.
     *
     * @param   string  $body  The HTML body snippet.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('htmlBodyProvider')]
    public function testIsHtmlResponseWithHtmlBody(string $body): void
    {
        $response = $this->createResponse($body, 'application/octet-stream');

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'Failed asserting that HTML content in body is detected as HTML.'
        );
    }

    /**
     * Data provider for HTML body snippets.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function htmlBodyProvider(): array
    {
        return [
            'doctype lowercase'        => ['<!doctype html><html><body>Challenge</body></html>'],
            'doctype uppercase'        => ['<!DOCTYPE HTML><html><body>Challenge</body></html>'],
            'doctype with whitespace'  => ["  \n\t <!doctype html><html><body>Challenge</body></html>"],
            'html tag'                 => ['<html><head><title>Verify</title></head><body>Verify</body></html>'],
            'html tag with whitespace' => ["  \n <html><body>Challenge</body></html>"],
            'head tag'                 => ['<head><title>Cloudflare</title></head><body>Challenge</body>'],
            'utf-8 BOM with doctype'   => ["\xEF\xBB\xBF<!DOCTYPE html><html><body>Challenge</body></html>"],
        ];
    }

    /**
     * Test that an empty response body is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithEmptyBody(): void
    {
        $response = $this->createResponse('', 'application/octet-stream');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'Empty response body should not be detected as HTML.'
        );
    }

    /**
     * Test that valid archive packages are NOT detected as HTML.
     *
     * @param   string  $binaryData   Binary archive data.
     * @param   string  $contentType  Content type header.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('validArchiveProvider')]
    public function testIsHtmlResponseWithValidArchive(string $binaryData, string $contentType): void
    {
        $response = $this->createResponse($binaryData, $contentType);

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            "Archive with Content-Type '$contentType' should not be detected as HTML."
        );
    }

    /**
     * Data provider for valid archive packages.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function validArchiveProvider(): array
    {
        return [
            'ZIP application/zip'          => ["PK\x03\x04" . str_repeat("\x00", 50), 'application/zip'],
            'ZIP application/octet-stream' => ["PK\x03\x04" . str_repeat("\x00", 50), 'application/octet-stream'],
            'GZIP application/gzip'        => ["\x1f\x8b" . str_repeat("\x00", 50), 'application/gzip'],
            'BZIP2 application/x-bzip2'    => ["BZh91AY&SY" . str_repeat("\x00", 50), 'application/x-bzip2'],
            'Plain arbitrary binary'       => ["\x00\x01\x02\x03\x04\x05", 'application/octet-stream'],
        ];
    }
}
