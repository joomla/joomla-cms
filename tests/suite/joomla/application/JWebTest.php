<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/web.php';

/**
 * Test class for JDaemon.
 */
class JWebTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// Include the inspector.
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JWeb_Inspector.php';

		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

		// Get a new JWebInspector instance.
		$this->inspector = new JWebInspector();

		parent::setUp();
	}

	public static function userAgentProvider()
	{
		// Platform, Mobile, Engine, Browser, Version, User Agent
		return array(
			array('windows', false, 'trident', 'Internet_Explorer', '10', 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)'),
			array('windows', false, 'trident', 'Internet_Explorer', '9', 'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))'),
			array('windows', false, 'trident', 'Internet_Explorer', '8', 'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)'),
			array('windows', false, 'trident', 'Internet_Explorer', '7.0b', 'Mozilla/4.0(compatible; MSIE 7.0b; Windows NT 6.0)'),
			array('windows', false, 'trident', 'Internet_Explorer', '7.0b', 'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; Media Center PC 3.0; .NET CLR 1.0.3705; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.1)'),
			array('windows', false, 'trident', 'Internet_Explorer', '7', 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 5.2)'),
			array('windows', false, 'trident', 'Internet_Explorer', '6.1', 'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)'),
			array('windows', false, 'trident', 'Internet_Explorer', '6', 'Mozilla/4.0 (compatible;MSIE 6.0;Windows 98;Q312461)'),
			array('windows', false, 'trident', 'Internet_Explorer', '7', 'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.6; AOLBuild 4340.128; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022)'),
			array('windows', false, 'trident', 'Internet_Explorer', '8', 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C; Maxthon 2.0)'),
			array('windows', false, 'trident', 'Internet_Explorer', '7', 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; SlimBrowser)'),
			array('mac', false, 'webkit', 'Chrome', '13.0.782.32', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_3) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.32 Safari/535.1'),
			array('windows', false, 'webkit', 'Chrome', '12.0.742.113', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.113 Safari/534.30'),
			array('linux', false, 'webkit', 'Chrome', '12.0.742.112', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.30 (KHTML, like Gecko) Ubuntu/10.04 Chromium/12.0.742.112 Chrome/12.0.742.112 Safari/534.30'),
			array('windows', false, 'webkit', 'Chrome', '15.0.864.0', 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.864.0 Safari/535.2'),
			array('blackberry', true, 'webkit', 'Safari', '6.0.0.546', 'Mozilla/5.0 (BlackBerry; U; BlackBerry 9700; pt) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.546 Mobile Safari/534.8+'),
			array('blackberry', true, 'webkit', '', '', 'BlackBerry9700/5.0.0.862 Profile/MIDP-2.1 Configuration/CLDC-1.1 VendorID/120'),
			array('android', true, 'webkit', 'Safari', '999.9', 'Mozilla/5.0 (Linux; U; Android 2.3; en-us) AppleWebKit/999+ (KHTML, like Gecko) Safari/999.9'),
			array('android', true, 'webkit', 'Safari', '4', 'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1'),
			array('ipad', true, 'webkit', 'Safari', '4.0.4', 'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B314 Safari/531.21.10gin_lib.cc'),
			array('iphone', true, 'webkit', 'Safari', '4.0.5', 'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8B5097d Safari/6531.22.7'),
			array('windows', false, 'webkit', 'Safari', '5.0.4', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27'),
			array('mac', false, 'webkit', 'Safari', '5.0.3', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; ar) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4'),
			array('windows', false, 'gecko', 'Firefox', '3.6.9', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9 ( .NET CLR 3.5.30729; .NET CLR 4.0.20506)'),
			array('windows', false, 'gecko', 'Firefox', '4.0b8pre', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101213 Firefox/4.0b8pre'),
			array('windows', false, 'gecko', 'Firefox', '5', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20100101 Firefox/5.0'),
			array('windows', false, 'gecko', 'Firefox', '6', 'Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0'),
			array('mac', false, 'gecko', '', '', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en; rv:1.9.2.14pre) Gecko/20101212 Camino/2.1a1pre (like Firefox/3.6.14pre)'),
			array('linux', false, 'khtml', '', '', 'Mozilla/5.0 (compatible; Konqueror/4.4; Linux 2.6.32-22-generic; X11; en_US) KHTML/4.4.3 (like Gecko) Kubuntu'),
			array('', false, 'amaya', '', '', 'amaya/11.3.1 libwww/5.4.1')
		);
	}

	/**
	 * @dataProvider userAgentProvider
	 */
	public function testDetectClientBrowser($p, $m, $e, $b, $v, $ua)
	{
		$data = $this->inspector->detectClientBrowser($ua);

		// Test the assertions.
		$this->assertEquals($data['browser'], $b);
		$this->assertEquals($data['version'], $v);
	}

	/**
	 * @dataProvider userAgentProvider
	 */
	public function testDetectClientPlatform($p, $m, $e, $b, $v, $ua)
	{
		$data = $this->inspector->detectClientPlatform($ua);

		// Test the assertions.
		$this->assertEquals($data['mobile'], $m);
		$this->assertEquals($data['platform'], $p);
	}

	/**
	 * @dataProvider userAgentProvider
	 */
	public function testDetectClientEngine($p, $m, $e, $b, $v, $ua)
	{
		$data = $this->inspector->detectClientEngine($ua);

		// Test the assertion.
		$this->assertEquals($data, $e);
	}
}
