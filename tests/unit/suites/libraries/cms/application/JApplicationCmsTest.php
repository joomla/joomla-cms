<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

include_once __DIR__ . '/stubs/JApplicationCmsInspector.php';

/**
 * Test class for JApplicationCms.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.2
 */
class JApplicationCmsTest extends TestCaseDatabase
{
	/**
	 * Value for test host.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_HTTP_HOST = 'mydomain.com';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_USER_AGENT = 'Mozilla/5.0';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_REQUEST_URI = '/index.php';

	/**
	 * An instance of the class to test.
	 *
	 * @var    JApplicationCmsInspector
	 * @since  3.2
	 */
	protected $class;

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getRedirectData()
	{
		return array(
			// Note: url, base, request, (expected result)
			array('/foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=3.2', 'http://mydomain.com/foo'),
			array('foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=3.2', 'http://mydomain.com/foo'),
		);
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Set the config for the app
		$config = new JRegistry;
		$config->set('session', false);

		// Get a new JApplicationCmsInspector instance.
		$this->class = new JApplicationCmsInspector(null, $config);

		// We are coupled to Document and Language in JFactory.
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
	 * @since   3.2
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		TestReflection::setValue('JEventDispatcher', 'instance', null);

		// Reset some web inspector static settings.
		JApplicationCmsInspector::$headersSent = false;
		JApplicationCmsInspector::$connectionAlive = true;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_usergroups', JPATH_TEST_DATABASE . '/jos_usergroups.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Tests the JApplicationCms::__construct method.
	 *
	 * @return  void
	 *
	 * @since   3.2
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

		$this->assertInstanceOf(
			'JEventDispatcher',
			TestReflection::getValue($this->class, 'dispatcher'),
			'Client property wrong type'
		);
	}

	/**
	 * Tests the JApplicationCms::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   3.2
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

		$config = new JRegistry;
		$config->set('session', false);

		$mockClient = $this->getMock('JApplicationWebClient', array('test'), array(), '', false);
		$mockClient
			->expects($this->any())
			->method('test')
			->will(
			$this->returnValue('ok')
		);

		$inspector = new JApplicationCmsInspector($mockInput, $config, $mockClient);

		$this->assertThat(
			$inspector->input->test(),
			$this->equalTo('ok'),
			'Tests input injection.'
		);

		$this->assertThat(
			$inspector->get('session'),
			$this->isFalse(),
			'Tests config injection.'
		);

		$this->assertThat(
			$inspector->client->test(),
			$this->equalTo('ok'),
			'Tests client injection.'
		);
	}

	/**
	 * Tests the JApplicationCms::Execute method without a document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testExecuteWithoutDocument()
	{
		// Manually inject the dispatcher.
		TestReflection::setValue($this->class, 'dispatcher', $this->getMockDispatcher());

		// Register all the methods so that we can track if they have been fired.
		$this->class->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterRespond', 'JWebTestExecute-onAfterRespond');

		$this->class->execute();

		$this->assertThat(
			TestMockDispatcher::$triggered,
			$this->equalTo(
				array(
					'JWebDoExecute',
					'onAfterRespond',
				)
			),
			'Check that events fire in the right order.'
		);
	}

	/**
	 * Tests the JApplicationCms::Execute method with a document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testExecuteWithDocument()
	{
		JFactory::$application = $this->class;

		$dispatcher = $this->getMockDispatcher();
		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));

		// Manually inject the dispatcher.
		TestReflection::setValue($this->class, 'dispatcher', $dispatcher);
		TestReflection::setValue($this->class, 'document', $document);

		// Register all the methods so that we can track if they have been fired.
		$this->class->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onBeforeRender', 'JWebTestExecute-onBeforeRender')
			->registerEvent('onAfterRender', 'JWebTestExecute-onAfterRender')
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
					'JWebDoExecute',
					'onBeforeRender',
					'onAfterRender',
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
	 * Tests the JApplicationCms::getCfg method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetCfg()
	{
		$config = new JRegistry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertThat(
			$this->class->getCfg('foo', 'car'),
			$this->equalTo('bar'),
			'Checks a known configuration setting is returned.'
		);

		$this->assertThat(
			$this->class->getCfg('goo', 'car'),
			$this->equalTo('car'),
			'Checks an unknown configuration setting returns the default.'
		);
	}

	/**
	 * Tests the JApplicationCms::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetInstance()
	{
		TestReflection::setValue('JApplicationCms', 'instances', array('CmsInspector' => $this->class));

		$this->assertInstanceOf(
			'JApplicationCmsInspector',
			JApplicationCms::getInstance('CmsInspector'),
			'Tests that getInstance will instantiate a valid child class of JApplicationCms.'
		);

		TestReflection::setValue('JApplicationCms', 'instances', array('CmsInspector' => 'foo'));

		$this->assertThat(
			JApplicationCms::getInstance('CmsInspector'),
			$this->equalTo('foo'),
			'Tests that singleton value is returned.'
		);
	}

	/**
	 * Tests the JApplicationCms::getMenu method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetMenu()
	{
		$this->assertThat(
			$this->class->getMenu(''),
			$this->isInstanceOf('JMenu')
		);
	}

	/**
	 * Tests the JApplicationCms::getPathway method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetPathway()
	{
		$this->assertThat(
			$this->class->getPathway(''),
			$this->isInstanceOf('JPathway')
		);
	}

	/**
	 * Tests the JApplicationCms::getRouter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetRouter()
	{
		$this->assertThat(
			$this->class->getRouter(''),
			$this->isInstanceOf('JRouter')
		);
	}

	/**
	 * Tests the JApplicationCms::getTemplate method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTemplate()
	{
		$template = $this->class->getTemplate(true);

		$this->assertThat(
			$template->params,
			$this->isInstanceOf('JRegistry')
		);

		$this->assertThat(
			$template->template,
			$this->equalTo('system')
		);
	}

	/**
	 * Tests the JApplicationCms::isAdmin method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsAdmin()
	{
		$this->assertThat(
			$this->class->isAdmin(),
			$this->isFalse(),
			'By default, JApplicationCms is neither a site or admin app'
		);
	}

	/**
	 * Tests the JApplicationCms::isSite method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsSite()
	{
		$this->assertThat(
			$this->class->isSite(),
			$this->isFalse(),
			'By default, JApplicationCms is neither a site or admin app'
		);
	}

	/**
	 * Tests the JApplicationCms::redirect method.
	 *
	 * @return  void
	 *
	 * @since   3.2
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
	 * Tests the JApplicationCms::redirect method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRedirectLegacy()
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
		$config = new JRegistry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		$this->class->redirect($url, 'Test Message', 'message', false);

		$messageQueue = $this->class->getMessageQueue();

		$this->assertThat(
			$messageQueue,
			$this->equalTo(
				array(
					array(
						'message' => 'Test Message',
						'type' => 'message'
					)
				)
			),
			'Tests to ensure legacy redirect handling works'
		);

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
	 * Tests the JApplicationCms::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRedirectWithHeadersSent()
	{
		$base = 'http://mydomain.com/';
		$url = 'index.php';

		// Emulate headers already sent.
		JApplicationCmsInspector::$headersSent = true;

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
	 * Tests the JApplicationCms::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   3.2
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
	 * Tests the JApplicationCms::redirect method with moved option.
	 *
	 * @return  void
	 *
	 * @since   3.2
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
	 * Tests the JApplicationCms::redirect method with assorted URL's.
	 *
	 * @param   string  $url       @todo
	 * @param   string  $base      @todo
	 * @param   string  $request   @todo
	 * @param   string  $expected  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getRedirectData
	 * @since   3.2
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
	 * Tests the JApplicationCms::render method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRender()
	{
		JFactory::$application = $this->class;

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
}
