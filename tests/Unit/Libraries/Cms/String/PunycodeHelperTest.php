<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  String
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\String;

use Joomla\CMS\String\PunycodeHelper;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\String\PunycodeHelper.
 *
 * @since    __DEPLOY_VERSION__
 */
class PunycodeHelperTest extends UnitTestCase
{
    /**
     * Provides UTF-8 URLs and their Punycode equivalents.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function urlProvider(): array
    {
        return [
            'full url'              => ['https://exämple.com:8080/path/file.php?a=1&b=2#top', 'https://xn--exmple-cua.com:8080/path/file.php?a=1&b=2#top'],
            'user and password'     => ['https://user:secret@exämple.com/feed', 'https://user:secret@xn--exmple-cua.com/feed'],
            'user only'             => ['ftp://user@exämple.com/file', 'ftp://user@xn--exmple-cua.com/file'],
            'protocol-relative url' => ['//exämple.com/page', '//xn--exmple-cua.com/page'],
            'query and fragment 0'  => ['http://exämple.com/?0#0', 'http://xn--exmple-cua.com/?0#0'],
            'ascii url'             => ['http://www.example.com/index.php', 'http://www.example.com/index.php'],
            'relative url'          => ['/index.php?option=com_content', '/index.php?option=com_content'],
        ];
    }

    /**
     * Tests converting a UTF-8 URL to Punycode.
     *
     * @param   string  $utf8      The UTF-8 URL
     * @param   string  $punycode  The Punycode URL
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider  urlProvider
     */
    public function testUrlToPunycode(string $utf8, string $punycode): void
    {
        $this->assertSame($punycode, PunycodeHelper::urlToPunycode($utf8));
    }

    /**
     * Tests converting a Punycode URL to UTF-8.
     *
     * @param   string  $utf8      The UTF-8 URL
     * @param   string  $punycode  The Punycode URL
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider  urlProvider
     */
    public function testUrlToUTF8(string $utf8, string $punycode): void
    {
        $this->assertSame($utf8, PunycodeHelper::urlToUTF8($punycode));
    }
}
