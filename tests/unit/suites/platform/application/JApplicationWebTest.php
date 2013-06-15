<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

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
			array('/foo', 'http://j.org/', 'http://j.org/index.php?v=11.3', 'http://j.org/foo'),
			array('foo', 'http://j.org/', 'http://j.org/index.php?v=11.3', 'http://j.org/foo'),
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

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Get a new JApplicationWebInspector instance.
		$this->class = new JApplicationWebInspector;

		// We are only coupled to Document and Language in JFactory.
		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		JFactory::$language = $this->getMockLanguage();
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
		// Reset the dispatcher instance.
		TestReflection::setValue('JEventDispatcher', 'instance', null);

		// Reset some web inspector static settings.
		JApplicationWebInspector::$headersSent = false;
		JApplicationWebInspector::$connectionAlive = true;

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
		$this->assertInstanceOf(
			'JInput',
			$this->class->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'JRegistry',
			TestReflection::getValue($this->class, 'config'),
			'Config property wrong type'
		);

		$this->assertInstanceOf(
			'JApplicationWebClient',
			$this->class->client,
			'Client property wrong type'
		);

		// TODO Test that configuration data loaded.

		$this->assertThat(
			$this->class->get('execution.datetime'),
			$this->greaterThan('2001'),
			'Tests execution.datetime was set.'
		);

		$this->assertThat(
			$this->class->get('execution.timestamp'),
			$this->greaterThan(1),
			'Tests execution.timestamp was set.'
		);

		$this->assertThat(
			$this->class->get('uri.base.host'),
			$this->equalTo('http://' . self::TEST_HTTP_HOST),
			'Tests uri base host setting.'
		);
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
		$mockInput = $this->getMock('JInput', array('test'), array(), '', false);
		$mockInput
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockConfig = $this->getMock('JRegistry', array('test'), array(null), '', true);
		$mockConfig
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$mockClient = $this->getMock('JApplicationWebClient', array('test'), array(), '', false);
		$mockClient
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$inspector = new JApplicationWebInspector($mockInput, $mockConfig, $mockClient);

		$this->assertThat(
			$inspector->input->test(),
			$this->equalTo('ok'),
			'Tests input injection.'
		);

		$this->assertThat(
			TestReflection::getValue($inspector, 'config')->test(),
			$this->equalTo('ok'),
			'Tests config injection.'
		);

		$this->assertThat(
			$inspector->client->test(),
			$this->equalTo('ok'),
			'Tests client injection.'
		);
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
		$this->assertThat(
			$this->class->allowCache(),
			$this->isFalse(),
			'Return value of allowCache should be false by default.'
		);

		$this->assertThat(
			$this->class->allowCache(true),
			$this->isTrue(),
			'Return value of allowCache should return the new state.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->cachable,
			$this->isTrue(),
			'Checks the internal cache property has been set.'
		);
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

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('foo', 'bar')
			),
			'Checks the body array has been appended.'
		);

		$this->class->appendBody(true);

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('foo', 'bar', '1')
			),
			'Checks that non-strings are converted to strings.'
		);
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

		$this->assertEquals(
			array(),
			TestReflection::getValue($this->class, 'response')->headers,
			'Checks the headers were cleared.'
		);
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
		$this->assertSame(
			$this->class->closed,
			null,
			'Checks the application doesn\'t start closed.'
		);

		$this->class->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame(
			$this->class->closed,
			3,
			'Checks the application was closed with exit code 3.'
		);
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
		$this->assertThat(
			strlen($this->class->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(
				array(
					0 => array('name' => 'Content-Encoding', 'value' => 'gzip'),
					1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
				)
			),
			'Checks the headers were set correctly.'
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
		$this->assertThat(
			strlen($this->class->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(
				array(
					0 => array('name' => 'Content-Encoding', 'value' => 'deflate'),
					1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
				)
			),
			'Checks the headers were set correctly.'
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
		$this->assertThat(
			strlen($this->class->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
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
		$this->assertThat(
			strlen($this->class->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
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
		$this->assertThat(
			strlen($this->class->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no supported compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
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

		$this->assertThat(
			TestReflection::invoke($this->class, 'detectRequestUri'),
			$this->equalTo($expects)
		);
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

		$this->assertThat(
			TestMockDispatcher::$triggered,
			$this->equalTo(
				array(
					'onBeforeExecute',
					'JWebDoExecute',
					'onAfterExecute',
					'onBeforeRespond',
					'onAfterRespond',
				)
			),
			'Check that events fire in the right order.'
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

		// Manually inject the dispatcher.
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
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			TestMockDispatcher::$triggered,
			$this->equalTo(
				array(
					'onBeforeExecute',
					'JWebDoExecute',
					'onAfterExecute',
					'onBeforeRender',
					'onAfterRender',
					'onBeforeRespond',
					'onAfterRespond',
				)
			),
			'Check that events fire in the right order (with document).'
		);

		$this->assertThat(
			$this->class->getBody(),
			$this->equalTo('JWeb Body'),
			'Check that the body was set with the return value of document render method.'
		);

		$this->assertThat(
			$buffer,
			$this->equalTo('JWeb Body'),
			'Check that the body is output correctly.'
		);
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
			'Default configuration class' => array(JPATH_TESTS . '/tmp/configuration.php', null, 'JConfig', 'ConfigEval'),
			'Custom file, invalid class' => array(JPATH_TESTS . '/tmp/config.JCli-wrongclass.php', 'noclass', false, array(), true),
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
			$this->assertInstanceOf(
				$expectsClass,
				$config,
				'Checks the configuration object is the appropriate class.'
			);
		}

		$this->assertThat(
			(array) $config,
			$this->equalTo($expects),
			'Checks the content of the configuration object.'
		);
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
		$config = new JRegistry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertThat(
			$this->class->get('foo', 'car'),
			$this->equalTo('bar'),
			'Checks a known configuration setting is returned.'
		);

		$this->assertThat(
			$this->class->get('goo', 'car'),
			$this->equalTo('car'),
			'Checks an unknown configuration setting returns the default.'
		);
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

		$this->assertThat(
			$this->class->getBody(),
			$this->equalTo('foobar'),
			'Checks the default state returns the body as a string.'
		);

		$this->assertThat(
			$this->class->getBody(),
			$this->equalTo($this->class->getBody(false)),
			'Checks the default state is $asArray = false.'
		);

		$this->assertThat(
			$this->class->getBody(true),
			$this->equalTo(array('foo', 'bar')),
			'Checks that the body is returned as an array.'
		);
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

		$this->assertThat(
			$this->class->getHeaders(),
			$this->equalTo(array('ok')),
			'Checks the headers part of the response is returned correctly.'
		);
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
		$this->assertInstanceOf(
			'JApplicationWebInspector',
			JApplicationWeb::getInstance('JApplicationWebInspector'),
			'Tests that getInstance will instantiate a valid child class of JApplicationWeb.'
		);

		TestReflection::setValue('JApplicationWeb', 'instance', 'foo');

		$this->assertThat(
			JApplicationWeb::getInstance('JApplicationWebInspector'),
			$this->equalTo('foo'),
			'Tests that singleton value is returned.'
		);

		TestReflection::setValue('JApplicationWeb', 'instance', null);

		$this->assertInstanceOf(
			'JApplicationWeb',
			JApplicationWeb::getInstance('Foo'),
			'Tests that getInstance will instantiate a valid child class of JApplicationWeb given a non-existent type.'
		);
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

		$this->assertInstanceOf(
			'JDocument',
			TestReflection::getValue($this->class, 'document'),
			'Test that deafult document was initialised.'
		);

		$this->assertInstanceOf(
			'JLanguage',
			TestReflection::getValue($this->class, 'language'),
			'Test that deafult language was initialised.'
		);

		$this->assertInstanceOf(
			'JEventDispatcher',
			TestReflection::getValue($this->class, 'dispatcher'),
			'Test that deafult dispatcher was initialised.'
		);
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

		$this->assertThat(
			TestReflection::getValue($this->class, 'session'),
			$this->equalTo(null),
			'Test that no session is defined.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'document'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'language'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);
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
		$mockSession = $this->getMock('JSession', array('test'), array(), '', false);
		$mockSession
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('JSession')
		);

		$mockDocument = $this->getMock('JDocument', array('test'), array(), '', false);
		$mockDocument
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('JDocument')
		);

		$mockLanguage = $this->getMock('JLanguage', array('test'), array(), '', false);
		$mockLanguage
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('JLanguage')
		);

		$mockDispatcher = $this->getMock('JEventDispatcher', array('test'), array(), '', false);
		$mockDispatcher
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('JEventDispatcher')
		);

		$this->class->initialise($mockSession, $mockDocument, $mockLanguage, $mockDispatcher);

		$this->assertThat(
			TestReflection::getValue($this->class, 'session')->test(),
			$this->equalTo('JSession'),
			'Tests session injection.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'document')->test(),
			$this->equalTo('JDocument'),
			'Tests document injection.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'language')->test(),
			$this->equalTo('JLanguage'),
			'Tests language injection.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'dispatcher')->test(),
			$this->equalTo('JEventDispatcher'),
			'Tests dispatcher injection.'
		);
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
		$this->assertThat(
			$this->class->loadConfiguration(
				array(
					'foo' => 'bar',
				)
			),
			$this->identicalTo($this->class),
			'Check chaining.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('foo'),
			$this->equalTo('bar'),
			'Check the configuration array was loaded.'
		);

		$this->class->loadConfiguration(
			(object) array(
				'goo' => 'car',
			)
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('goo'),
			$this->equalTo('car'),
			'Check the configuration object was loaded.'
		);
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

		$this->assertInstanceOf(
			'JDocument',
			TestReflection::getValue($this->class, 'document'),
			'Tests that the document object is the correct class.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'document')->test(),
			$this->equalTo('ok'),
			'Tests that we got the document from the factory.'
		);
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

		$this->assertInstanceOf(
			'JLanguage',
			TestReflection::getValue($this->class, 'language'),
			'Tests that the language object is the correct class.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'language')->test(),
			$this->equalTo('ok'),
			'Tests that we got the language from the factory.'
		);
	}

	/**
	 * Tests the JApplicationWeb::loadSession method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSession()
	{
		$this->markTestIncomplete();
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
		$config = new JRegistry(array('site_uri' => 'http://test.joomla.org/path/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris');

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.full'),
			$this->equalTo('http://test.joomla.org/path/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.host'),
			$this->equalTo('http://test.joomla.org'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.path'),
			$this->equalTo('/path/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.full'),
			$this->equalTo('http://test.joomla.org/path/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.path'),
			$this->equalTo('/path/media/'),
			'Checks the media uri path.'
		);
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

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.path'),
			$this->equalTo('/media/'),
			'Checks the media uri path.'
		);
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
		$config = new JRegistry(array('media_uri' => 'http://cdn.joomla.org/media/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris', 'http://joom.la/application');

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.full'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.path'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the media uri path.'
		);
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
		$config = new JRegistry(array('media_uri' => '/media/'));
		TestReflection::setValue($this->class, 'config', $config);

		TestReflection::invoke($this->class, 'loadSystemUris', 'http://joom.la/application');

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.base.path'),
			$this->equalTo('/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			TestReflection::getValue($this->class, 'config')->get('uri.media.path'),
			$this->equalTo('/media/'),
			'Checks the media uri path.'
		);
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

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('bar', 'foo')
			),
			'Checks the body array has been prepended.'
		);

		$this->class->prependBody(true);

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('1', 'bar', 'foo')
			),
			'Checks that non-strings are converted to strings.'
		);
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
		$base = 'http://j.org/';
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
		$config = new JRegistry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		$this->class->redirect($url, false);

		$this->assertThat(
			$this->class->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 303 See other', true, null),
					array('Location: ' . $base . $url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
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
		$base = 'http://j.org/';
		$url = 'index.php';

		// Emulate headers already sent.
		JApplicationWebInspector::$headersSent = true;

		// Inject the internal configuration.
		$config = new JRegistry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		// Capture the output for this test.
		ob_start();
		$this->class->redirect('index.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			$buffer,
			$this->equalTo("<script>document.location.href='{$base}{$url}';</script>\n")
		);
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
		$url = 'http://j.org/index.php?phi=Φ';

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
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			trim($buffer),
			$this->equalTo(
				'<html><head>'
					. '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'
					. "<script>document.location.href='{$url}';</script>"
					. '</head><body></body></html>'
			)
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
		$url = 'http://j.org/index.php';

		// Inject the client information.
		TestReflection::setValue(
			$this->class,
			'client',
			(object) array(
				'engine' => JApplicationWebClient::GECKO,
			)
		);

		$this->class->redirect($url, true);

		$this->assertThat(
			$this->class->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 301 Moved Permanently', true, null),
					array('Location: ' . $url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
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
		$config = new JRegistry;
		$config->set('uri.base.full', $base);
		$config->set('uri.request', $request);

		TestReflection::setValue($this->class, 'config', $config);

		$this->class->redirect($url, false);

		$this->assertThat(
			$this->class->headers[1][0],
			$this->equalTo('Location: ' . $expected)
		);
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

		$this->assertThat(
			$this->class->registerEvent('onJWebRegisterEvent', 'function'),
			$this->identicalTo($this->class),
			'Check chaining.'
		);

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

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('JWeb Body')
			)
		);
	}

	/**
	 * Tests the JApplicationWeb::respond method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRespond()
	{
		$this->markTestIncomplete();
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

		$this->assertThat(
			$this->class->sendHeaders(),
			$this->identicalTo($this->class),
			'Check chaining.'
		);

		$this->assertThat(
			$this->class->headers,
			$this->equalTo(
				array(
					array('Status: 200', null, 200),
					array('X-JWeb-SendHeaders: foo', true, null),
				)
			)
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
		$config = new JRegistry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertThat(
			$this->class->set('foo', 'car'),
			$this->equalTo('bar'),
			'Checks set returns the previous value.'
		);

		$this->assertThat(
			$config->get('foo'),
			$this->equalTo('car'),
			'Checks the new value has been set.'
		);
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

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('foo')
			),
			'Checks the body array has been reset.'
		);

		$this->class->setBody(true);

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('1')
			),
			'Checks reset and that non-strings are converted to strings.'
		);
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
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'bar'),
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that a header is added.'
		);

		$this->class->setHeader('foo', 'car', true);
		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that headers of the same name are replaced.'
		);
	}

	/**
	 * Test...
	 *
	 * @covers JApplicationWeb::isSSLConnection
	 *
	 * @return void
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertThat(
			$this->class->isSSLConnection(),
			$this->equalTo(false)
		);

		$_SERVER['HTTPS'] = 'on';

		$this->assertThat(
			$this->class->isSSLConnection(),
			$this->equalTo(true)
		);
	}
}
