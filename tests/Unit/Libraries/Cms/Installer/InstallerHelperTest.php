<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Installer
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Installer;

use Joomla\CMS\Installer\InstallerHelper;
use Joomla\Http\Response;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for InstallerHelper
 *
 * @since   5.4.0
 * @covers  \Joomla\CMS\Installer\InstallerHelper
 */
class InstallerHelperTest extends UnitTestCase
{
    /**
     * Creates a mock HTTP Response with the given body and content-type header.
     *
     * @param   string       $body         The response body content
     * @param   string|null  $contentType  The Content-Type header value, or null for no header
     * @param   int          $statusCode   The HTTP status code
     *
     * @return  Response
     *
     * @since   5.4.0
     */
    private function createResponse(string $body, ?string $contentType = null, int $statusCode = 200): Response
    {
        $response = new Response(
            $this->createBodyStream($body),
            $statusCode,
            $contentType ? ['Content-Type' => $contentType] : []
        );

        return $response;
    }

    /**
     * Creates a PSR-7 stream from a string body.
     *
     * @param   string  $body  The body string
     *
     * @return  \Psr\Http\Message\StreamInterface
     *
     * @since   5.4.0
     */
    private function createBodyStream(string $body): \Psr\Http\Message\StreamInterface
    {
        $stream = new \Laminas\Diactoros\Stream('php://temp', 'wb+');
        $stream->write($body);
        $stream->rewind();

        return $stream;
    }

    /**
     * Test that an HTML CAPTCHA page is detected as a blocked response.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithHtmlCaptchaPage(): void
    {
        $htmlBody = '<!DOCTYPE html><html><head><title>Cloudflare Challenge</title></head>'
            . '<body><h1>Please verify you are human</h1></body></html>';

        $response = $this->createResponse($htmlBody, 'text/html; charset=UTF-8');

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'HTML CAPTCHA page should be detected as a blocked response'
        );
    }

    /**
     * Test that an XHTML firewall page is detected as a blocked response.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithXhtmlFirewallPage(): void
    {
        $xhtmlBody = '<?xml version="1.0"?><html xmlns="http://www.w3.org/1999/xhtml">'
            . '<head><title>Access Denied</title></head><body>Blocked by WAF</body></html>';

        $response = $this->createResponse($xhtmlBody, 'application/xhtml+xml');

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'XHTML firewall page should be detected as a blocked response'
        );
    }

    /**
     * Test that a valid ZIP package is not detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithValidZipPackage(): void
    {
        // ZIP magic bytes: PK\x03\x04
        $zipBody = "PK\x03\x04" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/zip');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Valid ZIP package should not be detected as a blocked response'
        );
    }

    /**
     * Test that a valid ZIP package without Content-Type header is not detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithZipPackageNoContentType(): void
    {
        // ZIP magic bytes: PK\x03\x04
        $zipBody = "PK\x03\x04" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody);

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'ZIP package without Content-Type should not be detected as blocked'
        );
    }

    /**
     * Test that a valid GZIP package is not detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithValidGzipPackage(): void
    {
        // GZIP magic bytes: \x1f\x8b
        $gzipBody = "\x1f\x8b" . str_repeat("\x00", 100);

        $response = $this->createResponse($gzipBody, 'application/gzip');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Valid GZIP package should not be detected as a blocked response'
        );
    }

    /**
     * Test that a valid TAR package is not detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithValidTarPackage(): void
    {
        // TAR format has "ustar" at offset 257
        $tarBody = str_repeat("\x00", 257) . 'ustar' . str_repeat("\x00", 100);

        $response = $this->createResponse($tarBody, 'application/x-tar');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Valid TAR package should not be detected as a blocked response'
        );
    }

    /**
     * Test that a valid BZ2 package is not detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithValidBz2Package(): void
    {
        // BZ2 magic bytes: BZ
        $bz2Body = "BZ" . str_repeat("\x00", 100);

        $response = $this->createResponse($bz2Body, 'application/x-bzip2');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Valid BZ2 package should not be detected as a blocked response'
        );
    }

    /**
     * Test that an empty response is detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithEmptyBody(): void
    {
        $response = $this->createResponse('', 'application/octet-stream');

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'Empty response should be detected as a blocked response'
        );
    }

    /**
     * Test that a plain text response (error page, etc.) is detected as blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithPlainTextError(): void
    {
        $response = $this->createResponse('Access denied. Please contact the administrator.', 'text/plain');

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'Plain text error response should be detected as a blocked response (no valid archive magic bytes)'
        );
    }

    /**
     * Test that an HTML response without Content-Type header but containing HTML is detected.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithHtmlBodyNoContentTypeHeader(): void
    {
        $htmlBody = '<html><body><h1>403 Forbidden</h1></body></html>';

        // No Content-Type header - detection falls through to magic bytes check
        $response = $this->createResponse($htmlBody);

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'HTML body without Content-Type should be detected as blocked (no valid archive magic bytes)'
        );
    }

    /**
     * Test that a Cloudflare-style challenge page with text/html content type is detected.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithCloudflareChallengePage(): void
    {
        $cfBody = '<!DOCTYPE html><html><head>'
            . '<title>Just a moment...</title>'
            . '<script src="/cdn-cgi/challenge-platform/scripts/managed/abc.js"></script>'
            . '</head><body><h2>Checking your browser before accessing</h2></body></html>';

        $response = $this->createResponse($cfBody, 'text/html');

        $this->assertTrue(
            InstallerHelper::isBlockedResponse($response),
            'Cloudflare challenge page should be detected as a blocked response'
        );
    }

    /**
     * Test that a valid ZIP with application/octet-stream Content-Type is not blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithZipAsOctetStream(): void
    {
        // ZIP magic bytes with generic content type (common for download servers)
        $zipBody = "PK\x03\x04" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/octet-stream');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'ZIP package served as application/octet-stream should not be detected as blocked'
        );
    }

    /**
     * Test that an empty ZIP archive (PK\x05\x06) is not blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithEmptyZipArchive(): void
    {
        // Empty ZIP archive magic bytes
        $zipBody = "PK\x05\x06" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/zip');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Empty ZIP archive should not be detected as a blocked response'
        );
    }

    /**
     * Test that a spanned ZIP archive (PK\x07\x08) is not blocked.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsBlockedResponseWithSpannedZipArchive(): void
    {
        // Spanned ZIP archive magic bytes
        $zipBody = "PK\x07\x08" . str_repeat("\x00", 100);

        $response = $this->createResponse($zipBody, 'application/zip');

        $this->assertFalse(
            InstallerHelper::isBlockedResponse($response),
            'Spanned ZIP archive should not be detected as a blocked response'
        );
    }

    /**
     * Tests the getFilenameFromUrl method returns a cleaned filename.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testGetFilenameFromUrl(): void
    {
        $this->assertSame(
            'com_example-1.0.0.zip',
            InstallerHelper::getFilenameFromUrl('https://example.com/downloads/com_example-1.0.0.zip')
        );
    }

    /**
     * Tests that getFilenameFromUrl handles URLs with query strings.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testGetFilenameFromUrlWithQueryString(): void
    {
        $result = InstallerHelper::getFilenameFromUrl('https://example.com/downloads/com_example.zip?key=abc123&ver=1.0');

        $this->assertSame('com_example.zip_key_abc123_ver_1.0', $result);
    }

    /**
     * Tests that getFilenameFromUrl returns a unique ID for non-URL strings.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testGetFilenameFromUrlWithNonUrl(): void
    {
        $result = InstallerHelper::getFilenameFromUrl('not-a-url');

        // Should return a unique ID (uniqid produces a hex string)
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $result);
    }

    /**
     * Test isChecksumValid returns HASH_NOT_PROVIDED when no hashes in update object.
     *
     * @return  void
     *
     * @since   5.4.0
     */
    public function testIsChecksumValidWithNoHash(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'jtest_');
        file_put_contents($tmpFile, 'test content');

        $update = $this->getMockBuilder(\Joomla\CMS\Updater\Update::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $update->method('get')->willReturn(false);

        $result = InstallerHelper::isChecksumValid($tmpFile, $update);

        @unlink($tmpFile);

        $this->assertSame(InstallerHelper::HASH_NOT_PROVIDED, $result);
    }
}
