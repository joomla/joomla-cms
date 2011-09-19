<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/web/webclient.php';
require_once __DIR__.'/TestStubs/JWebClient_Inspector.php';

/**
 * Test class for JWebClient.
 */
class JWebClientTest extends PHPUnit_Framework_TestCase
{
	/**
	 * An instance of a JWebClient inspector.
	 *
	 * @var    JWebClientInspector
	 * @since  11.3
	 */
	protected $inspector;

	/**
	 * Provides test data for user agent parsing.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public static function getUserAgentData()
	{
		// Platform, Mobile, Engine, Browser, Version, User Agent
		return array(
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '10', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '9', 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '8', 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '7.0b', 'Mozilla/4.0(compatible; MSIE 7.0b; Windows NT 6.0)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '7.0b', 'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; Media Center PC 3.0; .NET CLR 1.0.3705; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.1)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '7', 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 5.2)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '6.1', 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '6', 'Mozilla/4.0 (compatible;MSIE 6.0;Windows 98;Q312461)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '7', 'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.6; AOLBuild 4340.128; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '8', 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; Maxthon 2.0)'),
			array(JWebClient::WINDOWS, false, JWebClient::TRIDENT, JWebClient::IE, '7', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; SlimBrowser)'),
			array(JWebClient::MAC, false, JWebClient::WEBKIT, JWebClient::CHROME, '13.0.782.32', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_3) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.32 Safari/535.1'),
			array(JWebClient::WINDOWS, false, JWebClient::WEBKIT, JWebClient::CHROME, '12.0.742.113', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.113 Safari/534.30'),
			array(JWebClient::LINUX, false, JWebClient::WEBKIT, JWebClient::CHROME, '12.0.742.112', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko) Ubuntu/10.04 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30'),
			array(JWebClient::WINDOWS, false, JWebClient::WEBKIT, JWebClient::CHROME, '15.0.864.0', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.864.0 Safari/535.2'),
			array(JWebClient::BLACKBERRY, true, JWebClient::WEBKIT, JWebClient::SAFARI, '6.0.0.546', 'Mozilla/5.0 (BlackBerry; U; BlackBerry 9700; pt) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.546 Mobile Safari/534.8+'),
			array(JWebClient::BLACKBERRY, true, JWebClient::WEBKIT, '', '', 'BlackBerry9700/5.0.0.862 Profile/MIDP-2.1 Configuration/CLDC-1.1 VendorID/120'),
			array(JWebClient::ANDROID, true, JWebClient::WEBKIT, JWebClient::SAFARI, '999.9', 'Mozilla/5.0 (Linux; U; Android 2.3; en-us) AppleWebKit/999+ (KHTML, like Gecko) Safari/999.9'),
			array(JWebClient::ANDROID, true, JWebClient::WEBKIT, JWebClient::SAFARI, '4', 'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1'),
			array(JWebClient::IPAD, true, JWebClient::WEBKIT, JWebClient::SAFARI, '4.0.4', 'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10gin_lib.cc'),
			array(JWebClient::IPHONE, true, JWebClient::WEBKIT, JWebClient::SAFARI, '4.0.5', 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B5097d Safari/6531.22.7'),
			array(JWebClient::WINDOWS, false, JWebClient::WEBKIT, JWebClient::SAFARI, '5.0.4', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'),
			array(JWebClient::MAC, false, JWebClient::WEBKIT, JWebClient::SAFARI, '5.0.3', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; ar) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4'),
			array(JWebClient::WINDOWS, false, JWebClient::GECKO, JWebClient::FIREFOX, '3.6.9', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9 ( .NET CLR 3.5.30729; .NET CLR 4.0.20506)'),
			array(JWebClient::WINDOWS, false, JWebClient::GECKO, JWebClient::FIREFOX, '4.0b8pre', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101213 Firefox/4.0b8pre'),
			array(JWebClient::WINDOWS, false, JWebClient::GECKO, JWebClient::FIREFOX, '5', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20100101 Firefox/5.0'),
			array(JWebClient::WINDOWS, false, JWebClient::GECKO, JWebClient::FIREFOX, '6', 'Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0'),
			array(JWebClient::MAC, false, JWebClient::GECKO, '', '', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en; rv:1.9.2.14pre) Gecko/20101212 Camino/2.1a1pre (like Firefox/3.6.14pre)'),
			array(JWebClient::LINUX, false, JWebClient::KHTML, '', '', 'Mozilla/5.0 (compatible; Konqueror/4.4; Linux 2.6.32-22-generic; X11; en_US) KHTML/4.4.3 (like Gecko) Kubuntu'),
			array('', false, JWebClient::AMAYA, '', '', 'amaya/11.3.1 libwww/5.4.1')
		);
	}

	/**
	 * Provides test data for encoding parsing.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public static function getEncodingData()
	{
		// HTTP_ACCEPT_ENCODING, Supported Encodings
		return array(
			array('gzip, deflate', array('gzip', 'deflate')),
			array('x-gzip, deflate', array('x-gzip', 'deflate')),
			array('gzip, x-gzip, deflate', array('gzip', 'x-gzip', 'deflate')),
			array(' gzip, deflate ', array('gzip', 'deflate')),
			array('deflate, x-gzip', array('deflate', 'x-gzip')),
			array('goober , flasm', array('goober', 'flasm')),
			array('b2z, base64', array('b2z', 'base64'))
		);
	}

	/**
	 * Provides test data for language parsing.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public static function getLanguageData()
	{
		// HTTP_ACCEPT_LANGUAGE, Supported Language
		return array(
			array('en-US, en-GB', array('en-US', 'en-GB')),
			array('fr-FR, de-DE', array('fr-FR', 'de-DE')),
			array('en-AU, en-CA, en-GB', array('en-AU', 'en-CA', 'en-GB')),
			array(' nl-NL, de-DE ', array('nl-NL', 'de-DE')),
			array('en, nl-NL', array('en', 'nl-NL')),
			array('nerd , geek', array('nerd', 'geek')),
			array('xx-XX, xx', array('xx-XX', 'xx'))
		);
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

		// Get a new JWebInspector instance.
		$this->inspector = new JWebClientInspector();

		parent::setUp();
	}

	/**
	 * Tests the JWebClient::__construct method.
	 */
	public function test__construct()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWebClient::__get method.
	 */
	public function test__get()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWebClient::detectBrowser method.
	 *
	 * @param   string   $p   The expected platform.
	 * @param   boolean  $m   The expected mobile result.
	 * @param   string   $e   The expected engine.
	 * @param   string   $b   The expected browser.
	 * @param   string   $v   The expected browser version.
	 * @param   string   $ua  The input user agent.
	 *
	 * @return  void
	 *
	 * @dataProvider getUserAgentData
	 * @since   11.3
	 */
	public function testDetectBrowser($p, $m, $e, $b, $v, $ua)
	{
		$this->inspector->detectBrowser($ua);

		// Test the assertions.
		$this->assertEquals($this->inspector->browser, $b, 'Browser detection failed');
		$this->assertEquals($this->inspector->browserVersion, $v, 'Version detection failed');
	}

	/**
	 * Tests the JWebClient::detectEncoding method.
	 *
	 * @param   string  $ae  The input accept encoding.
	 * @param   array   $e   The expected array of encodings.
	 *
	 * @return  void
	 *
	 * @dataProvider getEncodingData
	 * @since   11.3
	 */
	public function testDetectEncoding($ae, $e)
	{
		$this->inspector->detectEncoding($ae);

		// Test the assertions.
		$this->assertEquals($this->inspector->encodings, $e, 'Encoding detection failed');
	}

	/**
	 * Tests the JWebClient::detectEngine method.
	 *
	 * @param   string   $p   The expected platform.
	 * @param   boolean  $m   The expected mobile result.
	 * @param   string   $e   The expected engine.
	 * @param   string   $b   The expected browser.
	 * @param   string   $v   The expected browser version.
	 * @param   string   $ua  The input user agent.
	 *
	 * @return  void
	 *
	 * @dataProvider getUserAgentData
	 * @since   11.3
	 */
	public function testDetectEngine($p, $m, $e, $b, $v, $ua)
	{
		$this->inspector->detectEngine($ua);

		// Test the assertion.
		$this->assertEquals($this->inspector->engine, $e, 'Engine detection failed.');
	}

	/**
	 * Tests the JWebClient::detectLanguage method.
	 *
	 * @param   string  $al  The input accept language.
	 * @param   array   $l   The expected array of languages.
	 *
	 * @return  void
	 *
	 * @dataProvider getLanguageData
	 * @since   11.3
	 */
	public function testDetectLanguage($al, $l)
	{
		$this->inspector->detectLanguage($al);

		// Test the assertions.
		$this->assertEquals($this->inspector->languages, $l, 'Language detection failed');
	}

	/**
	 * Tests the JWebClient::detectPlatform method.
	 *
	 * @param   string   $p   The expected platform.
	 * @param   boolean  $m   The expected mobile result.
	 * @param   string   $e   The expected engine.
	 * @param   string   $b   The expected browser.
	 * @param   string   $v   The expected browser version.
	 * @param   string   $ua  The input user agent.
	 *
	 * @return  void
	 *
	 * @dataProvider getUserAgentData
	 * @since   11.3
	 */
	public function testDetectPlatform($p, $m, $e, $b, $v, $ua)
	{
		$this->inspector->detectPlatform($ua);

		// Test the assertions.
		$this->assertEquals($this->inspector->mobile, $m, 'Mobile detection failed.');
		$this->assertEquals($this->inspector->platform, $p, 'Platform detection failed.');
	}
}
