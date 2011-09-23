<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/web.php';
require_once JPATH_TESTS.'/suite/joomla/event/JDispatcherInspector.php';
include_once __DIR__.'/TestStubs/JWeb_Inspector.php';

/**
 * Test class for JWeb.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.3
 */
class JWebTest extends JoomlaTestCase
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
	 * An instance of a JWeb inspector.
	 *
	 * @var    JWebInspector
	 * @since  11.3
	 */
	protected $inspector;

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
			array(null, '/j/index.php', '/j/index.php?foo=bar',  'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php?foo=bar'),
			array('on', '/j/index.php', '/j/index.php?foo=bar',  'joom.la:3', '/j/index.php', '', 'https://joom.la:3/j/index.php?foo=bar'),
			array(null, '', '',  'joom.la:3', '/j/index.php', '', 'http://joom.la:3/j/index.php'),
			array(null, '', '',  'joom.la:3', '/j/index.php', 'foo=bar', 'http://joom.la:3/j/index.php?foo=bar'),
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
			// fileName, expectsClass, (expected result array)
			'Default configuration class' => array(null, true, array('foo' => 'bar')),
			'Custom file with array' => array('config.jweb-array', false, array('foo' => 'bar')),
// 			'Custom file, invalid class' => array('config.jweb-wrongclass', false, array()),
			'Custom file, snooping' => array('../test_application/config.jweb-snoopy', false, array()),
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
			// url, base, request, (expected result)
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

		// Setup the system logger to echo all.
		//JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;

		// Get a new JWebInspector instance.
		$this->inspector = new JWebInspector;

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
		JDispatcherInspector::setInstance(null);

		// Reset some web inspector static settings.
		JWebInspector::$headersSent = false;
		JWebInspector::$connectionAlive = true;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the JWeb::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'JInput',
			$this->inspector->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'JRegistry',
			$this->inspector->getClassProperty('config'),
			'Config property wrong type'
		);

		$this->assertInstanceOf(
			'JWebClient',
			$this->inspector->client,
			'Client property wrong type'
		);

		// TODO Test that configuration data loaded.

		$this->assertThat(
			$this->inspector->get('execution.datetime'),
			$this->greaterThan('2001'),
			'Tests execution.datetime was set.'
		);

		$this->assertThat(
			$this->inspector->get('execution.timestamp'),
			$this->greaterThan(1),
			'Tests execution.timestamp was set.'
		);

		$this->assertThat(
			$this->inspector->get('uri.base.host'),
			$this->equalTo('http://'.self::TEST_HTTP_HOST),
			'Tests uri base host setting.'
		);
	}

	/**
	 * Tests the JWeb::__construct method with dependancy injection.
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

		$mockConfig = $this->getMock('JRegistry', array('test'), array(), '', false);
		$mockConfig
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('ok')
			);

		$mockClient = $this->getMock('JWebClient', array('test'), array(), '', false);
		$mockClient
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('ok')
			);

		$inspector = new JWebInspector($mockInput, $mockConfig, $mockClient);

		$this->assertThat(
			$inspector->input->test(),
			$this->equalTo('ok'),
			'Tests input injection.'
		);

		$this->assertThat(
			$inspector->getClassProperty('config')->test(),
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
	 * Tests the JWeb::allowCache method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAllowCache()
	{
		$this->assertThat(
			$this->inspector->allowCache(),
			$this->isFalse(),
			'Return value of allowCache should be false by default.'
		);

		$this->assertThat(
			$this->inspector->allowCache(true),
			$this->isTrue(),
			'Return value of allowCache should return the new state.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('response')->cachable,
			$this->isTrue(),
			'Checks the internal cache property has been set.'
		);
	}

	/**
	 * Tests the JWeb::appendBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAppendBody()
	{
		// Similulate a previous call to setBody or appendBody.
		$this->inspector->getClassProperty('response')->body = array('foo');

		$this->inspector->appendBody('bar');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo', 'bar')
			),
			'Checks the body array has been appended.'
		);

		$this->inspector->appendBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo', 'bar', 'Array')
			),
			'Checks that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the JWeb::clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClearHeaders()
	{
		// Fill the header array with an arbitrary value.
		$this->inspector->setClassProperty(
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('foo'),
				'body' => array(),
			)
		);

		$this->inspector->clearHeaders();

		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(array()),
			'Checks the headers were cleared.'
		);
	}

	/**
	 * Tests the JWeb::close method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClose()
	{
		// Make sure the application is not already closed.
		$this->assertSame(
			$this->inspector->closed,
			null,
			'Checks the application doesn\'t start closed.'
		);

		$this->inspector->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame(
			$this->inspector->closed,
			3,
			'Checks the application was closed with exit code 3.'
		);
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithGzipEncoding()
	{
		// Fill the header body with a value.
		$this->inspector->setClassProperty(
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		$this->inspector->compress();

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertThat(
			strlen($this->inspector->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(array(
				0 => array('name' => 'Content-Encoding', 'value' => 'gzip'),
				1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
			)),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithDeflateEncoding()
	{
		// Fill the header body with a value.
		$this->inspector->setClassProperty(
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'encodings' => array('deflate', 'gzip'),
			)
		);

		$this->inspector->compress();

		// Ensure that the compressed body is shorter than the raw body.
		$this->assertThat(
			strlen($this->inspector->getBody()),
			$this->lessThan(471),
			'Checks the compressed output is smaller than the uncompressed output.'
		);

		// Ensure that the compression headers were set.
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(array(
				0 => array('name' => 'Content-Encoding', 'value' => 'deflate'),
				1 => array('name' => 'X-Content-Encoded-By', 'value' => 'Joomla')
			)),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithNoAcceptEncodings()
	{
		// Fill the header body with a value.
		$this->inspector->setClassProperty(
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'encodings' => array(),
			)
		);

		$this->inspector->compress();

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertThat(
			strlen($this->inspector->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithHeadersSent()
	{
		// Fill the header body with a value.
		$this->inspector->setClassProperty(
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'encodings' => array('gzip', 'deflate'),
			)
		);

		// Set the headers sent flag to true.
		JWebInspector::$headersSent = true;

		$this->inspector->compress();

		// Set the headers sent flag back to false.
		JWebInspector::$headersSent = false;

		// Ensure that the compressed body is the same as the raw body since there is no compression.
		$this->assertThat(
			strlen($this->inspector->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no compression.'
		);
		// Ensure that the compression headers were not set.
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompressWithUnsupportedEncodings()
	{
		// Fill the header body with a value.
		$this->inspector->setClassProperty(
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'encodings' => array('foo', 'bar'),
			)
		);

		$this->inspector->compress();

		// Ensure that the compressed body is the same as the raw body since there is no supported compression.
		$this->assertThat(
			strlen($this->inspector->getBody()),
			$this->equalTo(471),
			'Checks the compressed output is the same as the uncompressed output -- no supported compression.'
		);

		// Ensure that the compression headers were not set.
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(null),
			'Checks the headers were set correctly.'
		);
	}

	/**
	 * Tests the JWeb::detectRequestUri method.
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
			$this->inspector->detectRequestUri(),
			$this->equalTo($expects)
		);
	}

	/**
	 * Tests the JWeb::Execute method without a document.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecuteWithoutDocument()
	{
		// Manually inject the dispatcher.
		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());

		// Register all the methods so that we can track if they have been fired.
		$this->inspector->registerEvent('onBeforeExecute', 'JWebTestExecute-onBeforeExecute')
			->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterExecute', 'JWebTestExecute-onAfterExecute')
			->registerEvent('onBeforeRespond', 'JWebTestExecute-onBeforeRespond')
			->registerEvent('onAfterRespond', 'JWebTestExecute-onAfterRespond');

		$this->inspector->execute();

		$this->assertThat(
			JDispatcherGlobalMock::$triggered,
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
	 * Tests the JWeb::Execute method with a document.
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
		$this->inspector->setClassProperty('dispatcher', $dispatcher);
		$this->inspector->setClassProperty('document', $document);

		// Register all the methods so that we can track if they have been fired.
		$this->inspector->registerEvent('onBeforeExecute', 'JWebTestExecute-onBeforeExecute')
			->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterExecute', 'JWebTestExecute-onAfterExecute')
			->registerEvent('onBeforeRender', 'JWebTestExecute-onBeforeRender')
			->registerEvent('onAfterRender', 'JWebTestExecute-onAfterRender')
			->registerEvent('onBeforeRespond', 'JWebTestExecute-onBeforeRespond')
			->registerEvent('onAfterRespond', 'JWebTestExecute-onAfterRespond');

		// Buffer the execution.
		ob_start();
		$this->inspector->execute();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			JDispatcherGlobalMock::$triggered,
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
			$this->inspector->getBody(),
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
	 * Tests the JWeb::fetchConfigurationData method.
	 *
	 * @param   string   $fileName      The name of the configuration file.
	 * @param   boolean  $expectsClass  The result is expected to be a class.
	 * @param   array    $expects       The expected result as an array.
	 *
	 * @return  void
	 *
	 * @dataProvider getFetchConfigurationData
	 * @since   11.3
	 */
	public function testFetchConfigurationData($fileName, $expectsClass, $expects)
	{
		$config = $this->inspector->fetchConfigurationData($fileName);

		if ($expectsClass)
		{
			$this->assertInstanceOf(
				'JConfig',
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
	 * Tests the JWeb::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$config = new JRegistry(array('foo' => 'bar'));

		$this->inspector->setClassProperty('config', $config);

		$this->assertThat(
			$this->inspector->get('foo', 'car'),
			$this->equalTo('bar'),
			'Checks a known configuration setting is returned.'
		);

		$this->assertThat(
			$this->inspector->get('goo', 'car'),
			$this->equalTo('car'),
			'Checks an unknown configuration setting returns the default.'
		);
	}

	/**
	 * Tests the JWeb::getBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetBody()
	{
		// Fill the header body with an arbitrary value.
		$this->inspector->setClassProperty(
			'response',
			(object) array(
				'cachable' => null,
				'headers' => null,
				'body' => array('foo', 'bar'),
			)
		);

		$this->assertThat(
			$this->inspector->getBody(),
			$this->equalTo('foobar'),
			'Checks the default state returns the body as a string.'
		);

		$this->assertThat(
			$this->inspector->getBody(),
			$this->equalTo($this->inspector->getBody(false)),
			'Checks the default state is $asArray = false.'
		);

		$this->assertThat(
			$this->inspector->getBody(true),
			$this->equalTo(array('foo', 'bar')),
			'Checks that the body is returned as an array.'
		);
	}

	/**
	 * Tests the JWeb::getHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetHeaders()
	{
		// Fill the header body with an arbitrary value.
		$this->inspector->setClassProperty(
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array('ok'),
				'body' => null,
			)
		);

		$this->assertThat(
			$this->inspector->getHeaders(),
			$this->equalTo(array('ok')),
			'Checks the headers part of the response is returned correctly.'
		);
	}

	/**
	 * Tests the JWeb::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JWebInspector',
			JWeb::getInstance('JWebInspector'),
			'Tests that getInstance will instantiate a valid child class of JWeb.'
		);

		$this->inspector->setClassInstance('foo');

		$this->assertThat(
			JWeb::getInstance('JWebInspector'),
			$this->equalTo('foo'),
			'Tests that singleton value is returned.'
		);

		$this->inspector->setClassInstance(null);

		$this->assertInstanceOf(
			'JWeb',
			JWeb::getInstance('Foo'),
			'Tests that getInstance will instantiate a valid child class of JWeb given a non-existent type.'
		);
	}

	/**
	 * Tests the JWeb::initialise method with default settings.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithDefaults()
	{
		// TODO JSession default is not tested properly.

		$this->inspector->initialise(false);

		$this->assertInstanceOf(
			'JDocument',
			$this->inspector->getClassProperty('document'),
			'Test that deafult document was initialised.'
		);

		$this->assertInstanceOf(
			'JLanguage',
			$this->inspector->getClassProperty('language'),
			'Test that deafult language was initialised.'
		);

		$this->assertInstanceOf(
			'JDispatcher',
			$this->inspector->getClassProperty('dispatcher'),
			'Test that deafult dispatcher was initialised.'
		);
	}

	/**
	 * Tests the JWeb::initialise method with false injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithFalse()
	{
		$this->inspector->initialise(false, false, false);

		$this->assertThat(
			$this->inspector->getClassProperty('session'),
			$this->equalTo(null),
			'Test that no session is defined.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('document'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('language'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);
	}

	/**
	 * Tests the JWeb::initialise method with dependancy injection.
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

		$mockDispatcher = $this->getMock('JDispatcher', array('test'), array(), '', false);
		$mockDispatcher
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('JDispatcher')
			);

		$this->inspector->initialise($mockSession, $mockDocument, $mockLanguage, $mockDispatcher);

		$this->assertThat(
			$this->inspector->getClassProperty('session')->test(),
			$this->equalTo('JSession'),
			'Tests session injection.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('document')->test(),
			$this->equalTo('JDocument'),
			'Tests document injection.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('language')->test(),
			$this->equalTo('JLanguage'),
			'Tests language injection.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('dispatcher')->test(),
			$this->equalTo('JDispatcher'),
			'Tests dispatcher injection.'
		);
	}

	/**
	 * Tests the JWeb::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadConfiguration()
	{
		$this->assertThat(
			$this->inspector->loadConfiguration(
				array(
					'foo' => 'bar',
				)
			),
			$this->identicalTo($this->inspector),
			'Check chaining.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('foo'),
			$this->equalTo('bar'),
			'Check the configuration array was loaded.'
		);

		$this->inspector->loadConfiguration(
			(object) array(
				'goo' => 'car',
			)
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('goo'),
			$this->equalTo('car'),
			'Check the configuration object was loaded.'
		);
	}

	/**
	 * Tests the JWeb::loadDispatcher method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDispatcher()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadDocument method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDocument()
	{
		// Inject the mock dispatcher into the JDispatcher singleton.
		JDispatcherInspector::setInstance($this->getMockDispatcher());

		$this->inspector->loadDocument();

		$this->assertInstanceOf(
			'JDocument',
			$this->inspector->getClassProperty('document'),
			'Tests that the document object is the correct class.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('document')->test(),
			$this->equalTo('ok'),
			'Tests that we got the document from the factory.'
		);
	}

	/**
	 * Tests the JWeb::loadLanguage method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadLanguage()
	{
		$this->inspector->loadLanguage();

		$this->assertInstanceOf(
			'JLanguage',
			$this->inspector->getClassProperty('language'),
			'Tests that the language object is the correct class.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('language')->test(),
			$this->equalTo('ok'),
			'Tests that we got the language from the factory.'
		);
	}

	/**
	 * Tests the JWeb::loadSession method.
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
	 * Tests the JWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithSiteUriSet()
	{
		// Set the site_uri value in the configuration.
		$config = new JRegistry(array('site_uri' => 'http://test.joomla.org/path/'));
		$this->inspector->setClassProperty('config', $config);

		$this->inspector->loadSystemUris();

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.full'),
			$this->equalTo('http://test.joomla.org/path/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.host'),
			$this->equalTo('http://test.joomla.org'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.path'),
			$this->equalTo('/path/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.full'),
			$this->equalTo('http://test.joomla.org/path/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.path'),
			$this->equalTo('/path/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the JWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriSet()
	{
		$this->inspector->loadSystemUris('http://joom.la/application');

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/application/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.path'),
			$this->equalTo('/application/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/application/media/'),
			'Checks the full media uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.path'),
			$this->equalTo('/application/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the JWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriWithMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new JRegistry(array('media_uri' => 'http://cdn.joomla.org/media/'));
		$this->inspector->setClassProperty('config', $config);

		$this->inspector->loadSystemUris('http://joom.la/application');

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/application/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.path'),
			$this->equalTo('/application/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.full'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.path'),
			$this->equalTo('http://cdn.joomla.org/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the JWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUrisWithoutSiteUriWithRelativeMediaUriSet()
	{
		// Set the media_uri value in the configuration.
		$config = new JRegistry(array('media_uri' => '/media/'));
		$this->inspector->setClassProperty('config', $config);

		$this->inspector->loadSystemUris('http://joom.la/application');

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.full'),
			$this->equalTo('http://joom.la/application/'),
			'Checks the full base uri.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.host'),
			$this->equalTo('http://joom.la'),
			'Checks the base uri host.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.base.path'),
			$this->equalTo('/application/'),
			'Checks the base uri path.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.full'),
			$this->equalTo('http://joom.la/media/'),
			'Checks the full media uri.'
		);

		// Since this is on a different domain we need the full url for this too.
		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('uri.media.path'),
			$this->equalTo('/media/'),
			'Checks the media uri path.'
		);
	}

	/**
	 * Tests the JWeb::prependBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testPrependBody()
	{
		// Similulate a previous call to a body method.
		$this->inspector->getClassProperty('response')->body = array('foo');

		$this->inspector->prependBody('bar');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('bar', 'foo')
			),
			'Checks the body array has been prepended.'
		);

		$this->inspector->prependBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('Array', 'bar', 'foo')
			),
			'Checks that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the JWeb::redirect method.
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
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'engine' => JWebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new JRegistry;
		$config->set('uri.base.full', $base);

		$this->inspector->setClassProperty('config', $config);

		$this->inspector->redirect($url, false);

		$this->assertThat(
			$this->inspector->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 303 See other', true, null),
					array('Location: '.$base.$url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
		);
	}

	/**
	 * Tests the JWeb::redirect method with headers already sent.
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
		JWebInspector::$headersSent = true;

		// Inject the internal configuration.
		$config = new JRegistry;
		$config->set('uri.base.full', $base);

		$this->inspector->setClassProperty('config', $config);

		// Capture the output for this test.
		ob_start();
		$this->inspector->redirect('index.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			$buffer,
			$this->equalTo("<script>document.location.href='{$base}{$url}';</script>\n")
		);
	}

	/**
	 * Tests the JWeb::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithJavascriptRedirect()
	{
		$url = 'http://j.org/index.php?phi=Φ';

		// Inject the client information.
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'engine' => JWebClient::TRIDENT,
			)
		);

		// Capture the output for this test.
		ob_start();
		$this->inspector->redirect($url);
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			trim($buffer),
			$this->equalTo(
				'<html><head>' .
				'<meta http-equiv="content-type" content="text/html; charset=utf-8" />' .
				"<script>document.location.href='{$url}';</script>" .
				'</head><body></body></html>'
			)
		);
	}

	/**
	 * Tests the JWeb::redirect method with moved option.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithMoved()
	{
		$url = 'http://j.org/index.php';

		// Inject the client information.
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'engine' => JWebClient::GECKO,
			)
		);

		$this->inspector->redirect($url, true);

		$this->assertThat(
			$this->inspector->headers,
			$this->equalTo(
				array(
					array('HTTP/1.1 301 Moved Permanently', true, null),
					array('Location: '.$url, true, null),
					array('Content-Type: text/html; charset=utf-8', true, null),
				)
			)
		);
	}

	/**
	 * Tests the JWeb::redirect method with assorted URL's.
	 *
	 * @return  void
	 *
	 * @dataProvider  getRedirectData
	 * @since   11.3
	 */
	public function testRedirectWithUrl($url, $base, $request, $expected)
	{
		// Inject the client information.
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'engine' => JWebClient::GECKO,
			)
		);

		// Inject the internal configuration.
		$config = new JRegistry;
		$config->set('uri.base.full', $base);
		$config->set('uri.request', $request);

		$this->inspector->setClassProperty('config', $config);

		$this->inspector->redirect($url, false);

		$this->assertThat(
			$this->inspector->headers[1][0],
			$this->equalTo('Location: '.$expected)
		);
	}

	/**
	 * Tests the JWeb::redirect method with webkit bug.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirectWithWebkitBug()
	{
		$url = 'http://j.org/index.php';

		// Inject the client information.
		$this->inspector->setClassProperty(
			'client',
			(object) array(
				'engine' => JWebClient::WEBKIT,
			)
		);

		// Capture the output for this test.
		ob_start();
		$this->inspector->redirect($url);
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertThat(
			trim($buffer),
			$this->equalTo(
				'<html><head>' .
				'<meta http-equiv="refresh" content="0; url=' . $url . '" />' .
				'<meta http-equiv="content-type" content="text/html; charset=utf-8" />' .
				'</head><body></body></html>'
			)
		);
	}

	/**
	 * Tests the JWeb::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRegisterEvent()
	{
		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());

		$this->assertThat(
			$this->inspector->registerEvent('onJWebRegisterEvent', 'function'),
			$this->identicalTo($this->inspector),
			'Check chaining.'
		);

		$this->assertArrayHasKey(
			'onJWebRegisterEvent',
			JDispatcherGlobalMock::$handlers,
			'Checks the events were passed to the mock dispatcher.'
		);
	}

	/**
	 * Tests the JWeb::render method.
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
		$this->inspector->setClassProperty('document', $document);

		$this->inspector->render();

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('JWeb Body')
			)
		);
	}

	/**
	 * Tests the JWeb::respond method.
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
	 * Tests the JWeb::sendHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSendHeaders()
	{
		// Similulate a previous call to a setHeader method.
		$this->inspector->getClassProperty('response')->headers = array(
			array('name' => 'Status', 'value' => 200),
			array('name' => 'X-JWeb-SendHeaders', 'value' => 'foo'),
		);

		$this->assertThat(
			$this->inspector->sendHeaders(),
			$this->identicalTo($this->inspector),
			'Check chaining.'
		);

		$this->assertThat(
			$this->inspector->headers,
			$this->equalTo(
				array(
					array('Status: 200', null, 200),
					array('X-JWeb-SendHeaders: foo', true, null),
				)
			)
		);
	}

	/**
	 * Tests the JWeb::set method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSet()
	{
		$config = new JRegistry(array('foo' => 'bar'));

		$this->inspector->setClassProperty('config', $config);

		$this->assertThat(
			$this->inspector->set('foo', 'car'),
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
	 * Tests the JWeb::setBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSetBody()
	{
		$this->inspector->setBody('foo');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo')
			),
			'Checks the body array has been reset.'
		);

		$this->inspector->setBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('Array')
			),
			'Checks reset and that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the JWeb::setHeader method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSetHeader()
	{
		// Fill the header body with an arbitrary value.
		$this->inspector->setClassProperty(
			'response',
			(object) array(
				'cachable' => null,
				'headers' => array(
					array('name' => 'foo', 'value' => 'bar'),
				),
				'body' => null,
			)
		);

		$this->inspector->setHeader('foo', 'car');
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'bar'),
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that a header is added.'
		);

		$this->inspector->setHeader('foo', 'car', true);
		$this->assertThat(
			$this->inspector->getClassProperty('response')->headers,
			$this->equalTo(
				array(
					array('name' => 'foo', 'value' => 'car')
				)
			),
			'Tests that headers of the same name are replaced.'
		);
	}

	/**
	 * Tests the JWeb::triggerEvents method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTriggerEvents()
	{
		$this->inspector->setClassProperty('dispatcher', null);
		$this->assertThat(
			$this->inspector->triggerEvent('onJWebTriggerEvent'),
			$this->isNull(),
			'Checks that for a non-dispatcher object, null is returned.'
		);

		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());
		$this->inspector->registerEvent('onJWebTriggerEvent', 'function');

		$this->assertThat(
			$this->inspector->triggerEvent('onJWebTriggerEvent'),
			$this->equalTo(
				array('function' => null)
			),
			'Checks the correct dispatcher method is called.'
		);
	}
}
