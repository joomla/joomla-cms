<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Uri
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Uri;

use Joomla\CMS\Uri\Uri;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Uri.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Uri
 * @since       1.7.0
 */
class UriTest extends UnitTestCase
{
    /**
     * @var    Uri
     */
    protected $object;

    /**
     * Backup of the SERVER superglobal
     *
     * @var    array
     * @since  3.6
     */
    protected $backupServer;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->backupServer = $_SERVER;
        Uri::reset();

        $_SERVER['HTTP_HOST']   = 'www.example.com:80';
        $_SERVER['SCRIPT_NAME'] = '/joomla/index.php';
        $_SERVER['PHP_SELF']    = '/joomla/index.php';
        $_SERVER['REQUEST_URI'] = '/joomla/index.php?var=value 10';

        $this->object = new Uri();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     *
     * @see     \PHPUnit\Framework\TestCase::tearDown()
     * @since   3.6
     */
    protected function tearDown(): void
    {
        $_SERVER = $this->backupServer;
        unset($this->backupServer);
        unset($this->object);
        parent::tearDown();
    }

    /**
     * Test the getInstance method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::getInstance
     */
    public function testGetInstance(): void
    {
        $customUri  = Uri::getInstance('http://someuser:somepass@www.example.com:80/path/file.html?var=value#fragment');
        $defaultUri = Uri::getInstance();

        $this->assertNotSame(
            $customUri,
            $defaultUri,
            'Uri::getInstance() should not return the same object for different URIs'
        );
    }

    /**
     * Test the root method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::root
     */
    public function testRoot(): void
    {
        $this->assertSame(
            Uri::root(false, '/administrator'),
            'http://www.example.com:80/administrator/'
        );
    }

    /**
     * Test the current method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::current
     */
    public function testCurrent(): void
    {
        $this->assertSame(
            Uri::current(),
            'http://www.example.com:80/joomla/index.php'
        );
    }

    /**
     * Test the parse method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::parse
     */
    public function testParse(): void
    {
        $this->assertTrue($this->object->parse('http://someuser:somepass@www.example.com:80/path/file.html?var=value&amp;test=true#fragment'));
    }

    /**
     * Test the buildQuery method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::buildQuery
     */
    public function testBuildQuery(): void
    {
        $params = [
            'field' => [
                'price' => [
                    'from' => 5,
                    'to'   => 10,
                ],
                'name' => 'foo',
            ],
            'v' => 45,
        ];

        $expected = 'field[price][from]=5&field[price][to]=10&field[name]=foo&v=45';
        $this->assertEquals($expected, Uri::buildQuery($params), 'The query string was not built correctly.');
    }

    /**
     * Test the setPath method.
     *
     * @return  void
     *
     * @since   1.7.0
     * @covers  Uri::setPath
     */
    public function testSetPath(): void
    {
        $this->object->setPath('/this/is/a/path/to/a/file.htm');

        $this->assertSame(
            '/this/is/a/path/to/a/file.htm',
            $this->object->getPath(),
            "The URI's path attribute was not set correctly."
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testParseWhenNoSchemeGiven(): void
    {
        $this->object->parse('www.myotherexample.com');
        $this->assertFalse(Uri::isInternal('www.myotherexample.com'));
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithSefUrl(): void
    {
        $this->object->parse('/login');
        $this->assertFalse(Uri::isInternal('/login'));
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithNoSchemeAndNotInternal(): void
    {
        $this->assertFalse(
            Uri::isInternal('www.myotherexample.com'),
            'www.myotherexample.com should NOT be resolved as internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithNoSchemeAndNoHostnameAndNotInternal(): void
    {
        $this->assertFalse(
            Uri::isInternal('myotherexample.com'),
            'myotherexample.com should NOT be resolved as internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithSchemeAndNotInternal(): void
    {
        $this->assertFalse(
            Uri::isInternal('http://www.myotherexample.com'),
            'http://www.myotherexample.com should NOT be resolved as  internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWhenInternalWithNoDomainOrScheme(): void
    {
        $this->assertTrue(
            Uri::isInternal('index.php?option=com_something'),
            'index.php?option=com_something should be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWhenInternalWithDomainAndSchemeAndPort(): void
    {
        $this->assertTrue(
            Uri::isInternal(Uri::base() . 'index.php?option=com_something'),
            Uri::base() . 'index.php?option=com_something should be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWhenInternalWithDomainAndSchemeAndPortNoSubFolder(): void
    {
        Uri::reset();

        $_SERVER['HTTP_HOST']   = 'www.example.com:80';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF']    = '/index.php';
        $_SERVER['REQUEST_URI'] = '/index.php?var=value 10';

        $this->object = new Uri();

        $this->assertTrue(
            Uri::isInternal(Uri::base() . 'index.php?option=com_something'),
            Uri::base() . 'index.php?option=com_something should be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWhenNOTInternalWithDomainAndSchemeAndPortAndIndex(): void
    {
        $this->assertFalse(
            Uri::isInternal('http://www.myotherexample.com/index.php?option=com_something'),
            'http://www.myotherexample.com/index.php?option=com_something should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWhenNOTInternalWithDomainAndNoSchemeAndPortAndIndex(): void
    {
        $this->assertFalse(
            Uri::isInternal('www.myotherexample.com/index.php?option=com_something'),
            'www.myotherexample.comindex.php?option=com_something should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternal3rdPartyDevs(): void
    {
        $this->assertFalse(
            Uri::isInternal('/customDevScript.php'),
            '/customDevScript.php should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalAppendingOfBaseToTheEndOfTheUrl(): void
    {
        $this->assertFalse(
            Uri::isInternal('/customDevScript.php?www.example.com'),
            '/customDevScript.php?www.example.com should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalAppendingOfBaseToTheEndOfTheUrl2(): void
    {
        $this->assertFalse(
            Uri::isInternal('www.otherexample.com/www.example.com'),
            'www.otherexample.com/www.example.com should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalSchemeEmptyButHostAndPortMatch(): void
    {
        $this->assertTrue(
            Uri::isInternal('www.example.com:80'),
            'www.example.com:80 should be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalPregMatch(): void
    {
        $this->assertFalse(
            Uri::isInternal('wwwhexample.com'),
            'wwwhexample.com should NOT be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithUser(): void
    {
        $this->assertTrue(
            Uri::isInternal('http://someuser.com@www.example.com:80'),
            'http://someuser@www.example.com:80 should be internal'
        );
    }

    /**
     * Test hardening of Uri::isInternal against non internal links
     *
     * @return void
     *
     * @covers Uri::isInternal
     */
    public function testIsInternalWithBackslashInUser(): void
    {
        $this->assertFalse(
            Uri::isInternal('http://someuser.com\@www.example.com:80'),
            'http://someuser\@www.example.com:80 should NOT be internal'
        );
    }
}
