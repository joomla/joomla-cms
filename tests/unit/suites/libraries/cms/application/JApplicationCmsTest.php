<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Application\Web\WebClient;
use Joomla\Registry\Registry;

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

		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		JFactory::$language = $this->getMockLanguage();
		JFactory::$session  = $this->getMockSession();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Set the config for the app
		$config = new Registry;
		$config->set('session', false);

		// Get a new JApplicationCmsInspector instance.
		$this->class = new JApplicationCmsInspector($this->getMockInput(), $config);
		$this->class->setSession(JFactory::$session);
		$this->class->setDispatcher($this->getMockDispatcher());

		JFactory::$application = $this->class;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.2
	 */
	protected function tearDown()
	{
		TestReflection::setValue('JPluginHelper', 'plugins', null);

		// Reset some web inspector static settings.
		JApplicationCmsInspector::$headersSent = false;
		JApplicationCmsInspector::$connectionAlive = true;

		$_SERVER = $this->backupServer;
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $config, $this->class);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

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
	 * @covers  JApplicationCms::__construct
	 */
	public function test__construct()
	{
		$this->assertInstanceOf('JInput', $this->class->input);

		$this->assertAttributeInstanceOf('\\Joomla\\Registry\\Registry', 'config', $this->class);
		$this->assertAttributeInstanceOf('\\Joomla\\Application\\Web\\WebClient', 'client', $this->class);
		$this->assertAttributeInstanceOf('\\Joomla\\Event\\DispatcherInterface', 'dispatcher', $this->class);
	}

	/**
	 * Tests the JApplicationCms::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::__construct
	 */
	public function test__constructDependancyInjection()
	{
		$mockInput = $this->getMockInput();

		$config = new Registry;
		$config->set('session', false);

		// Build the mock object.
		$mockClient = $this->getMockBuilder('\\Joomla\\Application\\Web\\WebClient')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		$inspector = new JApplicationCmsInspector($mockInput, $config, $mockClient);

		$this->assertAttributeSame($mockInput, 'input', $inspector);
		$this->assertFalse($inspector->get('session'));
		$this->assertAttributeSame($mockClient, 'client', $inspector);
	}

	/**
	 * Tests the JApplicationCms::Execute method without a document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::execute
	 */
	public function testExecuteWithoutDocument()
	{
		$this->class->execute();

		// Nothing happened, we just assert TRUE to pass test.
		self::assertTrue(true);
	}

	/**
	 * Tests the JApplicationCms::Execute method with a document.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::execute
	 */
	public function testExecuteWithDocument()
	{
		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));

		// Manually inject the document.
		$this->class->loadDocument($document);

		// Buffer the execution.
		ob_start();
		$this->class->execute();
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEquals('JWeb Body', $this->class->getBody());
		$this->assertEquals('JWeb Body', $buffer);
	}

	/**
	 * Tests the JApplicationCms::getCfg method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getCfg
	 */
	public function testGetCfg()
	{
		$config = new Registry(array('foo' => 'bar'));

		TestReflection::setValue($this->class, 'config', $config);

		$this->assertEquals('bar', $this->class->getCfg('foo', 'car'));
		$this->assertEquals('car', $this->class->getCfg('goo', 'car'));
	}

	/**
	 * Tests the JApplicationCms::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getInstance
	 */
	public function testGetInstance()
	{
		TestReflection::setValue('JApplicationCms', 'instances', array('CmsInspector' => $this->class));

		$this->assertInstanceOf('JApplicationCmsInspector', JApplicationCms::getInstance('CmsInspector'));

		TestReflection::setValue('JApplicationCms', 'instances', array('CmsInspector' => 'foo'));

		$this->assertEquals('foo', JApplicationCms::getInstance('CmsInspector'));
	}

	/**
	 * Tests the JApplicationCms::getMenu method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getMenu
	 */
	public function testGetMenu()
	{
		$this->assertInstanceOf('JMenu', $this->class->getMenu('Administrator'));
	}

	/**
	 * Tests the JApplicationCms::getPathway method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getPathway
	 */
	public function testGetPathway()
	{
		$this->assertInstanceOf('JPathway', $this->class->getPathway(''));
	}

	/**
	 * Tests the JApplicationCms::getRouter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getRouter
	 */
	public function testGetRouter()
	{
		$this->assertInstanceOf('JRouter', JApplicationCmsInspector::getRouter(''));
	}

	/**
	 * Tests the JApplicationCms::getTemplate method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::getTemplate
	 */
	public function testGetTemplate()
	{
		$template = $this->class->getTemplate(true);

		$this->assertInstanceOf('\\Joomla\\Registry\\Registry', $template->params);

		$this->assertEquals('system', $template->template);
	}

	/**
	 * Tests the JApplicationCms::isAdmin method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::isAdmin
	 */
	public function testIsAdmin()
	{
		$this->assertFalse($this->class->isAdmin());
	}

	/**
	 * Tests the JApplicationCms::isSite method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::isSite
	 */
	public function testIsSite()
	{
		$this->assertFalse($this->class->isSite());
	}

	/**
	 * Tests the JApplicationCms::isClient method.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 * @covers  JApplicationCms::isClient
	 */
	public function testIsClient()
	{
		$this->assertFalse($this->class->isClient('administrator'));
		$this->assertFalse($this->class->isClient('site'));
	}

	/**
	 * Tests the JApplicationCms::redirect method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::redirect
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
			array('HTTP/1.1 303 See other', true, 303),
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

		$this->assertRegexp('/Expires/', $this->class->headers[3][0]);

		$this->assertRegexp('/Last-Modified/', $this->class->headers[4][0]);

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
	 * Tests the JApplicationCms::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::redirect
	 */
	public function testRedirectWithHeadersSent()
	{
		$base = 'http://mydomain.com/';
		$url = 'index.php';

		// Emulate headers already sent.
		JApplicationCmsInspector::$headersSent = true;

		// Inject the internal configuration.
		$config = new Registry;
		$config->set('uri.base.full', $base);

		TestReflection::setValue($this->class, 'config', $config);

		// Capture the output for this test.
		ob_start();
		$this->class->redirect('index.php');
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEquals("<script>document.location.href='{$base}{$url}';</script>\n", $buffer);
	}

	/**
	 * Tests the JApplicationCms::redirect method with headers already sent.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::redirect
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
		$buffer = ob_get_contents();
		ob_end_clean();

		$this->assertEquals(
			'<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8" />'
			. "<script>document.location.href='{$url}';</script></head><body></body></html>",
			trim($buffer)
		);
	}

	/**
	 * Tests the JApplicationCms::redirect method with moved option.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::redirect
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
			array('HTTP/1.1 301 Moved Permanently', true, 301),
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

		$this->assertRegexp('/Expires/', $this->class->headers[3][0]);

		$this->assertRegexp('/Last-Modified/', $this->class->headers[4][0]);

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
	 * @covers  JApplicationCms::redirect
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
	 * Tests the JApplicationCms::render method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationCms::render
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
