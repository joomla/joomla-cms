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

/**
 * Test class for InstallerHelper
 *
 * @since   __DEPLOY_VERSION__
 * @covers  \Joomla\CMS\Installer\InstallerHelper
 */
class InstallerHelperTest extends UnitTestCase
{
    /**
     * Creates a mock HTTP Response with the given body and content-type header.
     *
     * @param   string       $body         The response body content.
     * @param   string|null  $contentType  The Content-Type header value, or null for no header.
     * @param   int          $statusCode   The HTTP status code.
     *
     * @return  Response
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createResponse(string $body, ?string $contentType = null, int $statusCode = 200): Response
    {
        return new Response(
            $this->createBodyStream($body),
            $statusCode,
            $contentType !== null ? ['Content-Type' => $contentType] : []
        );
    }

    /**
     * Creates a PSR-7 stream from a string body.
     *
     * @param   string  $body  The body string.
     *
     * @return  \Psr\Http\Message\StreamInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createBodyStream(string $body): \Psr\Http\Message\StreamInterface
    {
        $stream = new \Laminas\Diactoros\Stream('php://temp', 'wb+');
        $stream->write($body);
        $stream->rewind();

        return $stream;
    }

    /**
     * Test that an HTML CAPTCHA page with text/html header is detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithHtmlCaptchaPage(): void
    {
        $htmlBody = '<!DOCTYPE html><html><head><title>Cloudflare Challenge</title></head>'
            . '<body><h1>Please verify you are human</h1></body></html>';

        $response = $this->createResponse($htmlBody, 'text/html; charset=UTF-8');

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'HTML CAPTCHA page with text/html Content-Type should be detected as HTML.'
        );
    }

    /**
     * Test that an XHTML firewall block page with application/xhtml+xml header is detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithXhtmlFirewallPage(): void
    {
        $xhtmlBody = '<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml">'
            . '<head><title>Access Denied</title></head><body>Blocked by WAF</body></html>';

        $response = $this->createResponse($xhtmlBody, 'application/xhtml+xml');

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'XHTML firewall page with application/xhtml+xml Content-Type should be detected as HTML.'
        );
    }

    /**
     * Test that an HTML body starting with <!DOCTYPE html> is detected even without Content-Type header.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithDoctypeWithoutContentTypeHeader(): void
    {
        $htmlBody = "<!DOCTYPE html>\n<html><head><title>Challenge</title></head><body>Verify</body></html>";

        $response = $this->createResponse($htmlBody);

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'HTML body starting with <!DOCTYPE html> should be detected as HTML even without Content-Type header.'
        );
    }

    /**
     * Test that an HTML body starting with <html> tag is detected even when served as application/octet-stream.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithHtmlTagAsOctetStream(): void
    {
        $htmlBody = "<html><head><title>Firewall Block</title></head><body>Access Denied</body></html>";

        $response = $this->createResponse($htmlBody, 'application/octet-stream');

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'HTML body starting with <html> should be detected as HTML even if served as application/octet-stream.'
        );
    }

    /**
     * Test that leading whitespace before HTML tags is handled properly.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithLeadingWhitespace(): void
    {
        $htmlBody = "   \r\n\t  <!DOCTYPE html><html><body>Challenge</body></html>";

        $response = $this->createResponse($htmlBody, 'text/plain');

        $this->assertTrue(
            InstallerHelper::isHtmlResponse($response),
            'HTML with leading whitespace before <!DOCTYPE html> should be detected as HTML.'
        );
    }

    /**
     * Test that an empty response body is NOT detected as HTML (distinguishing empty downloads from HTML).
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
     * Test that a valid ZIP package is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithValidZipPackage(): void
    {
        $zipBody = "PK\x03\x04" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/zip');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'Valid ZIP package should not be detected as HTML.'
        );
    }

    /**
     * Test that a ZIP package served with application/octet-stream Content-Type is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithZipAsOctetStream(): void
    {
        $zipBody = "PK\x03\x04" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/octet-stream');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'ZIP package served as application/octet-stream should not be detected as HTML.'
        );
    }

    /**
     * Test that a valid GZIP package is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithValidGzipPackage(): void
    {
        $gzipBody = "\x1f\x8b" . str_repeat("\x00", 100);

        $response = $this->createResponse($gzipBody, 'application/gzip');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'Valid GZIP package should not be detected as HTML.'
        );
    }

    /**
     * Test that a valid BZIP2 package is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithValidBzip2Package(): void
    {
        $bzipBody = "BZh91AY&SY" . str_repeat("\x00", 100);

        $response = $this->createResponse($bzipBody, 'application/x-bzip2');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'Valid BZIP2 package should not be detected as HTML.'
        );
    }

    /**
     * Test that an arbitrary non-HTML binary/text string is NOT detected as HTML.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsHtmlResponseWithNonHtmlData(): void
    {
        $plainData = "Some arbitrary non-HTML text content";

        $response = $this->createResponse($plainData, 'application/octet-stream');

        $this->assertFalse(
            InstallerHelper::isHtmlResponse($response),
            'Arbitrary non-HTML data should not be detected as HTML.'
        );
    }

    /**
     * Test getFilenameFromUrl returns a clean filename.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetFilenameFromUrl(): void
    {
        $this->assertSame(
            'com_example-1.0.0.zip',
            InstallerHelper::getFilenameFromUrl('https://example.com/downloads/com_example-1.0.0.zip')
        );
    }

    /**
     * Test getFilenameFromUrl handles URLs with query strings.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetFilenameFromUrlWithQueryString(): void
    {
        $url    = 'https://example.com/downloads/com_example.zip?key=abc123&ver=1.0';
        $result = InstallerHelper::getFilenameFromUrl($url);

        $this->assertSame('com_example.zip_key_abc123_ver_1.0', $result);
    }

    /**
     * Test isChecksumValid returns HASH_NOT_PROVIDED when no hash is present in the update object.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testIsChecksumValidWithNoHash(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jtest_');
        file_put_contents($tmpFile, 'test content');

        $update = $this->createStub(\Joomla\CMS\Updater\Update::class);
        $update->method('get')->willReturn(false);

        $result = InstallerHelper::isChecksumValid($tmpFile, $update);

        @unlink($tmpFile);

        $this->assertSame(InstallerHelper::HASH_NOT_PROVIDED, $result);
    }
}
