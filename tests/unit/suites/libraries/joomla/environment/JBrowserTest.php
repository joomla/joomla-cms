<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Environment
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JBrowser.
 */
class JBrowserTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 */
	protected $backupServer;

	/**
	 * Object being tested
	 *
	 * @var  JBrowser
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->backupServer = $_SERVER;

		$this->object = new JBrowser;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Data provider for the testBrowserMatching method
	 *
	 * @return  array
	 */
	public function dataMatch()
	{
		return array(
			'Edge 14' => array(
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393',
				'edge',
				'win',
				'14',
				false,
			),
			'Opera 9.80 Mobile Linux' => array(
				'Opera/9.80 (Android 3.2.1; Linux; Opera Tablet/ADR-1111101157; U; en) Presto/2.9.201 Version/11.50',
				'opera',
				'unix',
				'11',
				true,
			),
			'Chrome 13 OS X' => array(
				'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_3) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.32 Safari/535.1',
				'chrome',
				'mac',
				'13',
				false,
			),
			'Chrome 12 Windows' => array(
				'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.113 Safari/534.30',
				'chrome',
				'win',
				'12',
				false,
			),
			'Chrome 54 Windows' => array(
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
				'chrome',
				'win',
				'54',
				false,
			),
			'Chrome 12 Ubuntu' => array(
				'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko) Ubuntu/10.04 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30',
				'chrome',
				'unix',
				'12',
				false,
			),
			'Internet Explorer 10' => array(
				'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
				'msie',
				'win',
				'10',
				false,
			),
			'Internet Explorer 9' => array(
				'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))',
				'msie',
				'win',
				'9',
				false,
			),
			'Firefox 12 Android Tablet' => array(
				'Mozilla/5.0 (Android; Tablet; rv:12.0) Gecko/12.0 Firefox/12.0',
				'firefox',
				'unix',
				'12',
				true,
			),
			'Firefox 6 Windows' => array(
				'Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0',
				'firefox',
				'win',
				'6',
				false,
			),
		);
	}

	/**
	 * @testdox  A browser with a given user agent is correctly detected
	 *
	 * @covers        JBrowser::match
	 * @dataProvider  dataMatch
	 *
	 * @param   string   $userAgent             The user agent to test
	 * @param   string   $expectedBrowser       The expected browser value
	 * @param   string   $expectedPlatform      The expected platform value
	 * @param   string   $expectedMajorVersion  The expected major version value
	 * @param   boolean  $expectedMobile        The expected mobile state
	 */
	public function testBrowserMatching($userAgent, $expectedBrowser, $expectedPlatform, $expectedMajorVersion, $expectedMobile)
	{
		$this->object->match($userAgent);

		$this->assertSame(
			$expectedBrowser,
			$this->object->getBrowser()
		);

		$this->assertSame(
			$expectedPlatform,
			$this->object->getPlatform()
		);

		$this->assertSame(
			$expectedMajorVersion,
			$this->object->getMajor()
		);

		$this->assertSame(
			$expectedMobile,
			$this->object->isMobile()
		);
	}

	/**
	 * @testdox  The isSSLConnection method correctly detects if a secure connection was made
	 *
	 * @covers   JBrowser::isSSLConnection
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertFalse($this->object->isSSLConnection());

		$_SERVER['HTTPS'] = 'on';

		$this->assertTrue($this->object->isSSLConnection());
	}
}
