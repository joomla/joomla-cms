<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\UrlFilter;
use Joomla\CMS\Uri\Uri;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for UrlFilter.
 *
 * @since  __DEPLOY_VERSION__
 */
class UrlFilterTest extends UnitTestCase
{
    /**
     * Backup of the $_SERVER variable
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $server;

    /**
     * Sets up the request for the current site.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->server = $_SERVER;
        Uri::reset();

        $_SERVER['HTTP_HOST']   = 'www.example.com';
        $_SERVER['SCRIPT_NAME'] = '/joomla/administrator/index.php';
        $_SERVER['PHP_SELF']    = '/joomla/administrator/index.php';
        $_SERVER['REQUEST_URI'] = '/joomla/administrator/index.php';

        Uri::root(false, '/joomla');
    }

    /**
     * Restores the request.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        Uri::reset();

        parent::tearDown();
    }

    /**
     * Test data for the testRelativeUrl method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataRelativeUrl(): array
    {
        return [
            'host only'          => ['www.example.com', 'http://www.example.com'],
            'host and path'      => ['www.example.com/blog/post', 'http://www.example.com/blog/post'],
            'host and port'      => ['www.example.com:8080/blog', 'http://www.example.com:8080/blog'],
            'host and query'     => ['www.example.com?option=com_content', 'http://www.example.com?option=com_content'],
            'host in upper case' => ['WWW.EXAMPLE.COM/blog', 'http://WWW.EXAMPLE.COM/blog'],
            'other host prefix'  => ['www.example.com.evil.org/page', '/joomla/www.example.com.evil.org/page'],
            'relative path'      => ['blog/post', '/joomla/blog/post'],
            'absolute path'      => ['/blog/post', '/blog/post'],
            'full url'           => ['https://www.example.com/blog', 'https://www.example.com/blog'],
        ];
    }

    /**
     * Tests filtering a URL of a url field that allows relative URLs.
     *
     * @param   string  $value     The value to filter
     * @param   string  $expected  The expected result
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     *
     * @dataProvider  dataRelativeUrl
     */
    public function testRelativeUrl(string $value, string $expected): void
    {
        $element = new \SimpleXMLElement('<field name="url" type="url" relative="true" />');

        $this->assertSame($expected, (new UrlFilter())->filter($element, $value));
    }
}
