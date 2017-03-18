<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

include_once __DIR__ . '/stubs/JApplicationWebInspector.php';

/**
 * Test class for JApplicationWeb.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.3
 */
class JApplicationWebTest extends TestCase
{
	/**
	 * Value for test host.
	 *
	 * @var    string
	 * @since  11.3
	 */
	const TEST_HTTP_HOST = 'mydomain.com';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  11.3
	 */
	const TEST_USER_AGENT = 'Mozilla/5.0';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  11.3
	 */
	const TEST_REQUEST_URI = '/index.php';

	/**
	 * An instance of the class to test.
	 *
	 * @var    JApplicationWebInspector
	 * @since  11.3
	 */
	protected $class;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $backupServer;

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getRedirectData()
	{
		return array(
			// Note: url, base, request, (expected result)
			array('/foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=11.3', 'http://mydomain.com/foo'),
			array('foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=11.3', 'http://mydomain.com/foo'),
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
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		JFactory::$language = $this->getMockLanguage();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Get a new JApplicationWebInspector instance.
		$this->class = new JApplicationWebInspector;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Reset some web inspector static settings.
		JApplicationWebInspector::$headersSent = false;
		JApplicationWebInspector::$connectionAlive = true;

		TestReflection::setValue('JApplicationWeb', 'instance', null);

		$_SERVER = $this->backupServer;
		unset($this->backupServer);
		unset($this->class);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the JApplicationWeb::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$this->assertAttributeInstanceOf('JInput', 'input', $this->class);
		$this->assertAttributeInstanceOf('\\Joomla\\Registry\\Registry', 'config', $this->class);
		$this->assertAttributeInstanceOf('\\Joomla\\Application\\Web\\WebClient', 'client', $this->class);

		// TODO Test that configuration data loaded.

		$this->assertGreaterThan(2001, $this->class->get('execution.datetime'), 'Tests execution.datetime was set.');
		$this->assertGreaterThan(1, $this->class->get('execution.timestamp'), 'Tests execution.timestamp was set.');
		$this->assertEquals('http://' . self::TEST_HTTP_HOST, $this->class->get('uri.base.host'));
	}

	/**
	 * Tests the JApplicationWeb::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__constructDependancyInjection()
	{
		if (PHP_VERSION == '5.5.13' || PHP_MINOR_VERSION == '6')
		{
			$this->markTestSkipped('Test is skipped due to a PHP bug in version 5.5.13 and a change in behavior in the 5.6 branch');
		}

		// Build the mock object.
		$mockInput = $this->getMockBuilder('JInput')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockInput->expects($this->any())
			->method('test')
			->willReturn('ok');

		$mockConfig = $this->getMockBuilder('\\Joomla\\Registry\\Registry')
					->setMethods(array('test'))
					->setConstructorArgs(array(null))
					->setMockClassName('')
					->getMock();
		$mockConfig
			->expects($this->any())
			->method('test')
			->willReturn('ok');

		$mockClient = $this->getMockBuilder('\\Joomla\\Application\\Web\\WebClient')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockClient->expects($this->any())
			->method('test')
			->willReturn('ok');

		$inspector = new JApplicationWebInspector($mockInput, $mockConfig, $mockClient);

		$this->assertEquals('ok', $inspector->client->test());
		$this->assertEquals('ok', $inspector->input->test());
		$this->assertEquals('ok', TestReflection::getValue($inspector, 'config')->test());
	}

	/**
	 * Tests the JApplicationWeb::allowCache method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAllowCache()
	{
		$this->assertFalse($this->class->allowCache());

		$this->assertTrue($this->class->allowCache(true));
	}

	/**
	 * Tests the JApplicationWeb::Execute method without a document.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecuteWithoutDocument()
	{
		$this->class->execute();
	}

	/**
	 * Tests the JApplicationWeb::Execute method with a document.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecuteWithDocument()
	{
		$dispatcher = $this->getMockDispatcher();
		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));

		// Manually inject the mocks.
		$this->class->setDispatcher($dispatcher);
		$this->class->loadDocument($document);

		// Buffer the execution.
		ob_start();
		$this->class->execute();
		$buffer = ob_get_clean();

		$this->assertEquals('JWeb Body', $this->class->getBody());

		$this->assertEquals('JWeb Body', $buffer);
	}

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getFetchConfigurationData()
	{
		return array(
			// Note: file, class, expectsClass, (expected result array), whether there should be an exception
			'Default configuration class' => array(JPATH_TEST_STUBS . '/configuration.php', null, 'JConfig', 'ConfigEval'),
			'Custom file, invalid class' => array(JPATH_TEST_STUBS . '/config.wrongclass.php', 'noclass', false, array(), true),
		);
	}

	/**
	 * Tests the JCli::fetchConfigurationData method.
	 *
	 * @param   string   $file               The name of the configuration file.
	 * @param   string   $class              The name of the class.
	 * @param   boolean  $expectsClass       The result is expected to be a class.
	 * @param   array    $expects            The expected result as an array.
	 * @param   bool     $expectedException  The expected exception.
	 *
	 * @return  void
	 *
	 * @dataProvider getFetchConfigurationData
	 * @since    11.3
	 */
	public function testFetchConfigurationData($file, $class, $expectsClass, $expects, $expectedException = false)
	{
		if ($expectedException)
		{
			$this->setExpectedException('RuntimeException');
		}

		if (is_null($file) && is_null($class))
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData');
		}
		elseif (is_null($class))
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData', $file);
		}
		else
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData', $file, $class);
		}

		if ($expects == 'ConfigEval')
		{
			$expects = new JConfig;
			$expects = (array) $expects;
		}

		if ($expectsClass)
		{
			$this->assertInstanceOf($expectsClass, $config);
		}

		$this->assertEquals($expects, (array) $config);
	}

	/**
	 * Tests the JApplicationWeb::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$app = JApplicationWeb::getInstance('JApplicationWebInspector');

		$this->assertInstanceOf('JApplicationWebInspector', $app);
		$this->assertSame($app, JApplicationWeb::getInstance('JApplicationWebInspector'), 'The same application object was not returned.');
	}

	/**
	 * Tests the JApplicationWeb::getInstance method for an unexisting class.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @expectedException  RuntimeException
	 */
	public function testGetInstanceForUnexistingClass()
	{
		JApplicationWeb::getInstance('Foo');
	}

	/**
	 * Tests the JApplicationWeb::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadConfiguration()
	{
		$this->assertSame(
			$this->class, $this->class->loadConfiguration(array('foo' => 'bar')));

		$this->assertEquals('bar', TestReflection::getValue($this->class, 'config')->get('foo'), 'Check the configuration array was loaded.');

		$this->class->loadConfiguration(
			(object) array(
				'goo' => 'car',
			)
		);

		$this->assertEquals('car', TestReflection::getValue($this->class, 'config')->get('goo'), 'Check the configuration object was loaded.');
	}

	/**
	 * Tests the JApplicationWeb::loadDocument method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDocument()
	{
		$this->class->loadDocument();

		$this->assertAttributeInstanceOf('JDocument', 'document', $this->class);
	}

	/**
	 * Tests the JApplicationWeb::loadLanguage method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadLanguage()
	{
		$this->class->loadLanguage();

		$this->assertAttributeInstanceOf('JLanguage', 'language', $this->class);
	}

	/**
	 * Tests the JApplicationWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithSiteUriSet()
	{
		// Set the site_uri value in the configuration.
		$config = new Registry(array('site_uri' => 'http://test.joomla.org/path/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris');

		$this->assertEquals('http://test.joomla.org/path/', $this->class->get('uri.base.full'));
		$this->assertEquals('http://test.joomla.org', $this->class->get('uri.base.host'));
		$this->assertEquals('/path/', $this->class->get('uri.base.path'));
		$this->assertEquals('http://test.joomla.org/path/media/', $this->class->get('uri.media.full'));
		$this->assertEquals('/path/media/', $this->class->get('uri.media.path'));
	}

	/**
	 * Tests the JApplicationWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriSet()
	{
		TestReflection::invoke($this->class, 'loadSystemUris', 'http://joom.la/application');

		$this->assertEquals('http://joom.la/', $this->class->get('uri.base.full'));
		$this->assertEquals('http://joom.la', $this->class->get('uri.base.host'));
		$this->assertEquals('/', $this->class->get('uri.base.path'));
		$this->assertEquals('http://joom.la/media/', $this->class->get('uri.media.full'));
		$this->assertEquals('/media/', $this->class->get('uri.media.path'));
	}

	/**
	 * Tests the JApplicationWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriWithMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new Registry(array('media_uri' => 'http://cdn.joomla.org/media/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris', 'http://joom.la/application');

		$this->assertEquals('http://joom.la/', $this->class->get('uri.base.full'));
		$this->assertEquals('http://joom.la', $this->class->get('uri.base.host'));
		$this->assertEquals('/', $this->class->get('uri.base.path'));
		$this->assertEquals('http://cdn.joomla.org/media/', $this->class->get('uri.media.full'));
		$this->assertEquals('http://cdn.joomla.org/media/', $this->class->get('uri.media.path'));
	}

	/**
	 * Tests the JApplicationWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriWithRelativeMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new Registry(array('media_uri' => '/media/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris', 'http://joom.la/application');

		$this->assertEquals('http://joom.la/', $this->class->get('uri.base.full'));
		$this->assertEquals('http://joom.la', $this->class->get('uri.base.host'));
		$this->assertEquals('/', $this->class->get('uri.base.path'));
		$this->assertEquals('http://joom.la/media/', $this->class->get('uri.media.full'));
		$this->assertEquals('/media/', $this->class->get('uri.media.path'));
	}

	/**
	 * Tests the JApplicationWeb::redirect method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirect()
	{
		$base = 'http://mydomain.com/';
		$url = 'index.php';

		// Inject the client information.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		$this->class->redirect($url, false);

		$this->assertEquals(
			array('HTTP/1.1 303 See other', true, null),
			$this->class->headers[0]
		);

		$this->assertEquals(
			array('Location: ' . $base . $url, true, null),
			$this->class->headers[1]
		);

		$this->assertEquals(
			array('Content-Type: text/html; charset=utf-8', true, null),
			$this->class->headers[2]
		);

		$this->assertRegexp('/Expires/',$this->class->headers[3][0]);

		$this->assertRegexp('/Last-Modified/',$this->class->headers[4][0]);

		$this->assertEquals(
			array('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true, null),
			$this->class->headers[5]
		);

		$this->assertEquals(
			array('Pragma: no-cache', true, null),
			$this->class->headers[6]
		);
	}

	/**
	 * Tests the JApplicationWeb::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithHeadersSent()
	{
		$base = 'http://mydomain.com/';
		$url = 'index.php';

		// Emulate headers already sent.
		JApplicationWebInspector::$headersSent = true;

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		// Capture the output for this test.
		ob_start();
		$this->class->redirect('index.php');
		$buffer = ob_get_clean();

		$this->assertEquals("<script>document.location.href='{$base}{$url}';</script>\n", $buffer);
	}

	/**
	 * Tests the JApplicationWeb::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithJavascriptRedirect()
	{
		$url = 'http://mydomain.com/index.php?phi=Φ';

		// Inject the client information.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'engine' => WebClient::TRIDENT,
			)
		);

		// Capture the output for this test.
		ob_start();
		$this->class->redirect($url);
		$buffer = ob_get_clean();

		$this->assertEquals(
			'<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"><script>document.location.href=\'' . $url . '\';</script></head><body></body></html>',
			trim($buffer)
		);
	}

	/**
	 * Tests the JApplicationWeb::redirect method with moved option.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithMoved()
	{
		$url = 'http://mydomain.com/index.php';

		// Inject the client information.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		$this->class->redirect($url, true);

		$this->assertEquals(
			array('HTTP/1.1 301 Moved Permanently', true, null),
			$this->class->headers[0]
		);

		$this->assertEquals(
			array('Location: ' . $url, true, null),
			$this->class->headers[1]
		);

		$this->assertEquals(
			array('Content-Type: text/html; charset=utf-8', true, null),
			$this->class->headers[2]
		);

		$this->assertRegexp('/Expires/',$this->class->headers[3][0]);

		$this->assertRegexp('/Last-Modified/',$this->class->headers[4][0]);

		$this->assertEquals(
			array('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true, null),
			$this->class->headers[5]
		);

		$this->assertEquals(
			array('Pragma: no-cache', true, null),
			$this->class->headers[6]
		);
	}

	/**
	 * Tests the JApplicationWeb::redirect method with assorted URL's.
	 *
	 * @param   string  $url       @todo
	 * @param   string  $base      @todo
	 * @param   string  $request   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getRedirectData
	 * @since   11.3
	 */
	public function testRedirectWithUrl($url, $base, $request, $expected)
	{
		// Inject the client information.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'engine' => WebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);
		$config->set('uri.request', $request);

		TestReflection::setValue($this->class, 'config', $config);

		$this->class->redirect($url, false);

		$this->assertEquals('Location: ' . $expected, $this->class->headers[1][0]);
	}

	/**
	 * Tests the JApplicationWeb::render method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRender()
	{
		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));

		// Manually inject the document.
		TestReflection::setValue($this->class, 'document', $document);

		TestReflection::invoke($this->class, 'render');

		$this->assertEquals('JWeb Body', (string) $this->class->getResponse()->getBody());
	}
}
