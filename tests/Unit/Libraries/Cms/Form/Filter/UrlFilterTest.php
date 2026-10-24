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
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class UrlFilterTest extends UnitTestCase
{
    /**
     * Prepares the environment for each test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();
        Uri::reset();

        $_SERVER['HTTP_HOST']   = 'example.com';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF']    = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php';
    }

    /**
     * Cleans up the environment after each test.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Uri::reset();
        unset($_SERVER['HTTP_HOST'], $_SERVER['SCRIPT_NAME'], $_SERVER['PHP_SELF'], $_SERVER['REQUEST_URI']);
        parent::tearDown();
    }

    /**
     * Test data for the testFilter method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataFilter(): array
    {
        $xmlUrl = new \SimpleXMLElement('<field name="test" type="url" />');
        $xmlUrlRelative = new \SimpleXMLElement('<field name="test" type="url" relative="true" />');
        $xmlUrlRelativeNumeric = new \SimpleXMLElement('<field name="test" type="url" relative="1" />');
        $xmlUrlRelativeKeyword = new \SimpleXMLElement('<field name="test" type="url" relative="relative" />');
        $xmlUrlNotRelative = new \SimpleXMLElement('<field name="test" type="url" relative="false" />');
        $xmlUrlNotRelativeNumeric = new \SimpleXMLElement('<field name="test" type="url" relative="0" />');

        return [
            'empty value'                  => [false, $xmlUrl, ''],
            'external url with scheme'     => ['https://example.com', $xmlUrl, 'https://example.com'],
            'external url relative=false'  => ['https://example.com', $xmlUrlNotRelative, 'https://example.com'],
            'no scheme default'            => ['http://example.com', $xmlUrl, 'example.com'],
            'no scheme relative=false'     => ['http://example.com', $xmlUrlNotRelative, 'example.com'],
            'no scheme relative=0'         => ['http://example.com', $xmlUrlNotRelativeNumeric, 'example.com'],
            'relative path relative=true'  => ['/relative/path', $xmlUrlRelative, '/relative/path'],
            'relative path relative=1'     => ['/relative/path', $xmlUrlRelativeNumeric, '/relative/path'],
            'relative path relative=name'  => ['/relative/path', $xmlUrlRelativeKeyword, '/relative/path'],
        ];
    }

    /**
     * Tests UrlFilter::filter
     *
     * @param   mixed              $expected  The expected filtered result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag
     * @param   string             $value     The value to filter
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @dataProvider dataFilter
     */
    public function testFilter($expected, \SimpleXMLElement $element, string $value): void
    {
        $filter = new UrlFilter();

        $this->assertEquals($expected, $filter->filter(clone $element, $value));
    }
}
