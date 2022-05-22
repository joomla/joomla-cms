<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Environment
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Environment;

use Joomla\CMS\Environment\Browser;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for JBrowser.
 *
 * @since   4.0.0
 */
class BrowserTest extends UnitTestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var  array
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected $backupServer;

	/**
	 * Object being tested
	 *
	 * @var  Browser
	 *
	 * @since   4.0.0
	 */
	protected $browser;

	/**
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function setUp():void
	{
		$this->browser = new Browser;

		parent::setUp();
	}

	/**
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function tearDown():void
	{
		unset($this->browser);

		parent::tearDown();
	}

	/**
	 * Data provider for the testBrowserMatching method
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function dataMatch(): array
	{
		return [
			'Edge 75' => [
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3738.0 Safari/537.36 Edg/75.0.107.0',
				'edg',
				'win',
				'75',
				false,
			],
			'Edge 14' => [
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14393',
				'edge',
				'win',
				'14',
				false,
			],
			'Opera 9.80 Mobile Linux' => [
				'Opera/9.80 (Android 3.2.1; Linux; Opera Tablet/ADR-1111101157; U; en) Presto/2.9.201 Version/11.50',
				'opera',
				'unix',
				'11',
				true,
			],
			'Chrome 13 OS X' => [
				'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_3) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.32 Safari/535.1',
				'chrome',
				'mac',
				'13',
				false,
			],
			'Chrome 12 Windows' => [
				'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.113 Safari/534.30',
				'chrome',
				'win',
				'12',
				false,
			],
			'Chrome 54 Windows' => [
				'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
				'chrome',
				'win',
				'54',
				false,
			],
			'Chrome 12 Ubuntu' => [
				'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko)
				 Ubuntu/10.04 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30',
				'chrome',
				'unix',
				'12',
				false,
			],
			'Internet Explorer 10' => [
				'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
				'msie',
				'win',
				'10',
				false,
			],
			'Internet Explorer 9' => [
				'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))',
				'msie',
				'win',
				'9',
				false,
			],
			'Firefox 12 Android Tablet' => [
				'Mozilla/5.0 (Android; Tablet; rv:12.0) Gecko/12.0 Firefox/12.0',
				'firefox',
				'unix',
				'12',
				true,
			],
			'Firefox 6 Windows' => [
				'Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0',
				'firefox',
				'win',
				'6',
				false,
			],
		];
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
	 *
	 * @return  void
	 * @since   4.0.0
	 */
	public function testBrowserMatching($userAgent, $expectedBrowser, $expectedPlatform, $expectedMajorVersion, $expectedMobile)
	{
		$this->browser->_match($userAgent);

		$this->assertSame($expectedBrowser, $this->browser->getBrowser());
		$this->assertSame($expectedPlatform, $this->browser->getPlatform());
		$this->assertSame($expectedMajorVersion, $this->browser->getMajor());
		$this->assertSame($expectedMobile, $this->browser->isMobile());
	}
}
