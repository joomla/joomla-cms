<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * Data for detectRequestUri method.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getDetectRequestUriData()
	{
		return array(
			// HTTPS, PHP_SELF, REQUEST_URI, HTTP_HOST, SCRIPT_NAME, QUERY_STRING, (resulting uri)
			array(null, '/j/index.php', '/j/index.php?foo=bar', 'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php?foo=bar'),
			array('on', '/j/index.php', '/j/index.php?foo=bar', 'joom.la:3', '/j/index.php', '', 'https://joom.la:3/j/index.php?foo=bar'),
			array(null, '', '', 'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php'),
			array(null, '', '', 'joom.la:3', '/j/index.php', 'foo=bar', 'http://joom.la:3/j/index.php?foo=bar'),
		);
	}

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
		// Reset the dispatcher and session instances.
		TestReflection::setValue('JEventDispatcher', 'instance', null);
		TestReflection::setValue('JSession', 'instance', null);

		// Reset some web inspector static settings.
		JApplicationWebInspector::$headersSent = false;
		JApplicationWebInspector::$connectionAlive = true;

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
		$this->assertAttributeInstanceOf('JApplicationWebClient', 'client', $this->class);

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
		if (PHP_VERSION == '5.4.29' || PHP_VERSION == '5.5.13' || PHP_MINOR_VERSION == '6')
		{
			$this->markTestSkipped('Test is skipped due to a PHP bug in versions 5.4.29 and 5.5.13 and a change in behavior in the 5.6 branch');
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

		$mockClient = $this->getMockBuilder('JApplicationWebClient')
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
	 * Tests the JApplicationWeb::appendBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAppendBody()
	{
		// Similulate a previous call to setBody or appendBody.
		TestReflection::getValue($this->class, 'response')->body = array('foo');

		$this->class->appendBody('bar');

		$this->assertEquals(array('foo', 'bar'), TestReflection::getValue($this->class, 'response')->body);

		$this->class->appendBody(true);

		$this->assertEquals(array('foo', 'bar', '1'), TestReflection::getValue($this->class, 'response')->body);
	}

	/**
	 * Tests the JApplicationWeb::clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClearHeaders()
	{
		// Fill the header array with an arbitrary value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('foo'),
				'body' => array(),
			)
		);

		$this->class->clearHeaders();

		$this->assertEquals(array(), TestReflection::getValue($this->class, 'response')->headers);
	}

	/**
	 * Tests the JApplicationWeb::close method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClose()
	{
		// Make sure the application is not already closed.
		$this->assertNull($this->class->closed);

		$this->class->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame($this->class->closed, 3);
	}

	/**
	 * Tests the JApplicationWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithGzipEncoding()
	{
		// Fill the header body with a value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			)
		);

		// Load the client encoding with a value.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		TestReflection::invoke($this->class, 'compress');

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertLessThan(471, strlen($this->class->getBody()));

		// Ensure that the compression headers were set.
		$this->assertEquals(
			array(
				0 => array('name' => 'Content-Encoding', 'value' => 'gzip')
			),
			TestReflection::getValue($this->class, 'response')->headers
		);
	}

	/**
	 * Tests the JApplicationWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithDeflateEncoding()
	{
		// Fill the header body with a value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.'),
			)
		);

		// Load the client encoding with a value.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'encodings' => array('deflate', 'gzip'),
			)
		);

		TestReflection::invoke($this->class, 'compress');

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertLessThan(471, strlen($this->class->getBody()));

		// Ensure that the compression headers were set.
		$this->assertEquals(
			array(
				0 => array('name' => 'Content-Encoding', 'value' => 'deflate')
			),
			TestReflection::getValue($this->class, 'response')->headers
		);
	}

	/**
	 * Tests the JApplicationWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithNoAcceptEncodings()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'encodings' => array(),
			)
		);

		TestReflection::invoke($this->class, 'compress');

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertSame(471, strlen($this->class->getBody()));

		// Ensure that the compression headers were not set.
		$this->assertNull(TestReflection::getValue($this->class, 'response')->headers);
	}

	/**
	 * Tests the JApplicationWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithHeadersSent()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		// Set the headers sent flag to true.
		JApplicationWebInspector::$headersSent = true;

		TestReflection::invoke($this->class, 'compress');

		// Set the headers sent flag back to false.
		JApplicationWebInspector::$headersSent = false;

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertSame(471, strlen($this->class->getBody()));

		// Ensure that the compression headers were not set.
		$this->assertNull(TestReflection::getValue($this->class, 'response')->headers);
	}

	/**
	 * Tests the JApplicationWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithUnsupportedEncodings()
	{
		$string = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
					eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim
					veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
					consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
					dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident,
					sunt in culpa qui officia deserunt mollit anim id est laborum.';

		// Replace \r\n -> \n to ensure same length on all platforms
		// Fill the header body with a value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array(str_replace("\r\n", "\n", $string)),
			)
		);

		// Load the client encoding with a value.
		TestReflection::getValue(
			$this->class,
			'client',
			(object) array(
				'encodings' => array('foo', 'bar'),
			)
		);

		TestReflection::invoke($this->class, 'compress');

		// Ensure that the compressed body is the same as the raw body since there is no supported compression.
		$this->assertSame(471, strlen($this->class->getBody()));

		// Ensure that the compression headers were not set.
		$this->assertNull(TestReflection::getValue($this->class, 'response')->headers);
	}

	/**
	 * Tests the JApplicationWeb::detectRequestUri method.
	 *
	 * @param   string  $https        @todo
	 * @param   string  $phpSelf      @todo
	 * @param   string  $requestUri   @todo
	 * @param   string  $httpHost     @todo
	 * @param   string  $scriptName   @todo
	 * @param   string  $queryString  @todo
	 * @param   string  $expects      @todo
	 *
	 * @return  void
	 *
	 * @dataProvider getDetectRequestUriData
	 * @since   11.3
	 */
	public function testDetectRequestUri($https, $phpSelf, $requestUri, $httpHost, $scriptName, $queryString, $expects)
	{
		if ($https !== null)
		{
			$_SERVER['HTTPS'] = $https;
		}

		$_SERVER['PHP_SELF'] = $phpSelf;
		$_SERVER['REQUEST_URI'] = $requestUri;
		$_SERVER['HTTP_HOST'] = $httpHost;
		$_SERVER['SCRIPT_NAME'] = $scriptName;
		$_SERVER['QUERY_STRING'] = $queryString;

		$this->assertEquals($expects, TestReflection::invoke($this->class, 'detectRequestUri'));
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
		// Manually inject the dispatcher.
		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());

		// Register all the methods so that we can track if they have been fired.
		$this->class->registerEvent('onBeforeExecute', 'JWebTestExecute-onBeforeExecute')
			->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterExecute', 'JWebTestExecute-onAfterExecute')
			->registerEvent('onBeforeRespond', 'JWebTestExecute-onBeforeRespond')
			->registerEvent('onAfterRespond', 'JWebTestExecute-onAfterRespond');

		$this->class->execute();

		$this->assertEquals(
			array(
				'onBeforeExecute',
				'JWebDoExecute',
				'onAfterExecute',
				'onBeforeRespond',
				'onAfterRespond',
			),
			TestMockDispatcher::$triggered
		);
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
		TestReflection::setValue($this->class, 'dispatcher', $dispatcher);
		TestReflection::setValue($this->class, 'document', $document);

		// Register all the methods so that we can track if they have been fired.
		$this->class->registerEvent('onBeforeExecute', 'JWebTestExecute-onBeforeExecute')
			->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterExecute', 'JWebTestExecute-onAfterExecute')
			->registerEvent('onBeforeRender', 'JWebTestExecute-onBeforeRender')
			->registerEvent('onAfterRender', 'JWebTestExecute-onAfterRender')
			->registerEvent('onBeforeRespond', 'JWebTestExecute-onBeforeRespond')
			->registerEvent('onAfterRespond', 'JWebTestExecute-onAfterRespond');

		// Buffer the execution.
		ob_start();
		$this->class->execute();
		$buffer = ob_get_clean();

		$this->assertEquals(
			array(
				'onBeforeExecute',
				'JWebDoExecute',
				'onAfterExecute',
				'onBeforeRender',
				'onAfterRender',
				'onBeforeRespond',
				'onAfterRespond',
			),
			TestMockDispatcher::$triggered
		);

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
	 * Tests the JApplicationWeb::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$config = new Registry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertEquals('bar', $this->class->get('foo', 'car'));
		$this->assertEquals('car', $this->class->get('goo', 'car'));
	}

	/**
	 * Tests the JApplicationWeb::getBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetBody()
	{
		// Fill the header body with an arbitrary value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('foo', 'bar'),
			)
		);

		$this->assertEquals('foobar', $this->class->getBody());
		$this->assertEquals($this->class->getBody(false), $this->class->getBody());
		$this->assertEquals(array('foo', 'bar'), $this->class->getBody(true));
	}

	/**
	 * Tests the JApplicationWeb::getHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetHeaders()
	{
		// Fill the header body with an arbitrary value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('ok'),
				'body' => null,
			)
		);

		$this->assertEquals(array('ok'), $this->class->getHeaders());
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
		$this->assertInstanceOf('JApplicationWebInspector', JApplicationWeb::getInstance('JApplicationWebInspector'));

		TestReflection::setValue('JApplicationWeb', 'instance', 'foo');

		$this->assertEquals('foo', JApplicationWeb::getInstance('JApplicationWebInspector'));

		TestReflection::setValue('JApplicationWeb', 'instance', null);

		$this->assertInstanceOf('JApplicationWeb', JApplicationWeb::getInstance('Foo'));
	}

	/**
	 * Tests the JApplicationWeb::initialise method with default settings.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithDefaults()
	{
		// TODO JSession default is not tested properly.

		$this->class->initialise(false);

		$this->assertAttributeInstanceOf('JDocument', 'document', $this->class);
		$this->assertAttributeInstanceOf('JLanguage', 'language', $this->class);
		$this->assertAttributeInstanceOf('JEventDispatcher', 'dispatcher', $this->class);
	}

	/**
	 * Tests the JApplicationWeb::initialise method with false injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithFalse()
	{
		$this->class->initialise(false, false, false);

		$this->assertAttributeEmpty('session', $this->class);
		$this->assertAttributeEmpty('document', $this->class);
		$this->assertAttributeEmpty('language', $this->class);
	}

	/**
	 * Tests the JApplicationWeb::initialise method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithInjection()
	{
		// Build the mock object.
		$mockSession = $this->getMockBuilder('JSession')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockSession
			->expects($this->any())
			->method('test')
			->willReturnSelf();

		$mockDocument = $this->getMockBuilder('JDocument')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockDocument
			->expects($this->any())
			->method('test')
			->willReturnSelf();

		$mockLanguage = $this->getMockBuilder('JLanguage')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockLanguage
			->expects($this->any())
			->method('test')
			->willReturnSelf();

		$mockDispatcher = $this->getMockBuilder('JEventDispatcher')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();
		$mockDispatcher
			->expects($this->any())
			->method('test')
			->willReturnSelf();

		$this->class->initialise($mockSession, $mockDocument, $mockLanguage, $mockDispatcher);

		$this->assertSame($mockSession, $this->class->getSession()->test());
		$this->assertSame($mockDocument, $this->class->getDocument()->test());
		$this->assertSame($mockLanguage, $this->class->getLanguage()->test());
		$this->assertSame($mockDispatcher, TestReflection::getValue($this->class, 'dispatcher')->test());
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
		// Inject the mock dispatcher into the JEventDispatcher singleton.
		TestReflection::setValue('JEventDispatcher', 'instance', $this->getMockDispatcher());

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
	 * Tests the JApplicationWeb::prependBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testPrependBody()
	{
		// Similulate a previous call to a body method.
		TestReflection::getValue($this->class, 'response')->body = array('foo');

		$this->class->prependBody('bar');

		$this->assertEquals(array('bar', 'foo'), TestReflection::getValue($this->class, 'response')->body);

		$this->class->prependBody(true);

		$this->assertEquals(array('1', 'bar', 'foo'), TestReflection::getValue($this->class, 'response')->body);
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
				'engine' => JApplicationWebClient::GECKO,
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
				'engine' => JApplicationWebClient::TRIDENT,
			)
		);

		// Capture the output for this test.
		ob_start();
		$this->class->redirect($url);
		$buffer = ob_get_clean();

		$this->assertEquals(
			'<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><script>document.location.href=\'' . $url . '\';</script></head><body></body></html>',
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
				'engine' => JApplicationWebClient::GECKO,
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
				'engine' => JApplicationWebClient::GECKO,
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
	 * Tests the JApplicationWeb::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRegisterEvent()
	{
		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());

		$this->assertSame($this->class, $this->class->registerEvent('onJWebRegisterEvent', 'function'));

		$this->assertArrayHasKey(
			'onJWebRegisterEvent',
			TestMockDispatcher::$handlers,
			'Checks the events were passed to the mock dispatcher.'
		);
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

		$this->assertEquals(array('JWeb Body'), TestReflection::getValue($this->class, 'response')->body);
	}

	/**
	 * Tests the JApplicationWeb::sendHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSendHeaders()
	{
		// Similulate a previous call to a setHeader method.
		TestReflection::getValue($this->class, 'response')->headers = array(
			array('name' => 'Status', 'value' => 200),
			array('name' => 'X-JWeb-SendHeaders', 'value' => 'foo'),
		);

		$this->assertSame($this->class, $this->class->sendHeaders());

		$this->assertEquals(
			array(
				array('HTTP/1.1 200', null, 200),
				array('X-JWeb-SendHeaders: foo', true, null),
			),
			$this->class->headers
		);
	}

	/**
	 * Tests the JApplicationWeb::set method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSet()
	{
		$config = new Registry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertEquals('bar', $this->class->set('foo', 'car'));

		$this->assertEquals('car', $config->get('foo'));
	}

	/**
	 * Tests the JApplicationWeb::setBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSetBody()
	{
		$this->class->setBody('foo');

		$this->assertEquals(array('foo'), TestReflection::getValue($this->class, 'response')->body);

		$this->class->setBody(true);

		$this->assertEquals(array('1'), TestReflection::getValue($this->class, 'response')->body);
	}

	/**
	 * Tests the JApplicationWeb::setHeader method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSetHeader()
	{
		// Fill the header body with an arbitrary value.
		TestReflection::setValue(
			$this->class,
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array(
					array('name' => 'foo', 'value' => 'bar'),
				),
				'body' => null,
			)
		);

		$this->class->setHeader('foo', 'car');

		$this->assertEquals(
			array(
				array('name' => 'foo', 'value' => 'bar'),
				array('name' => 'foo', 'value' => 'car')
			),
			TestReflection::getValue($this->class, 'response')->headers
		);

		$this->class->setHeader('foo', 'car', true);

		$this->assertEquals(
			array(
				array('name' => 'foo', 'value' => 'car')
			),
			TestReflection::getValue($this->class, 'response')->headers
		);
	}

	/**
	 * Tests the isSSLConnection method
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertFalse($this->class->isSSLConnection());

		$_SERVER['HTTPS'] = 'on';

		$this->assertTrue($this->class->isSSLConnection());
	}
}
