<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/data/TestRouter.php';
jimport('cms.router.router');

/**
 * Test class for JRouterSite.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @group       Router
 * @since       3.0
 */
class JRouterSiteTest extends TestCaseDatabase
{
	/**
	 * Backup of the $_SERVER variable
	 *
	 * @var    array
	 * @since  3.4
	 */
	private $server;

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

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function setUp()
	{
		parent::setUp();

		JUri::reset();

		$this->server = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'mydomain.com';

		$this->object = new JRouterSite(
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);
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
		$_SERVER = $this->server;
		unset($this->server);
		unset($this->object);

		parent::tearDown();
	}

	/**
	 * Tests the __construct() method
	 *
	 * @return  void
	 * @testdox JRouterSite is a JRouter
	 * @since   4.0
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('JRouter', $this->object);

		$rules = $this->object->getRules();
		$this->assertTrue(count($rules['parse' . JRouter::PROCESS_BEFORE]) > 0);
		$this->assertTrue(count($rules['parse']) > 0);
		$this->assertTrue(count($rules['parse' . JRouter::PROCESS_AFTER]) == 0);
		$this->assertTrue(count($rules['build' . JRouter::PROCESS_BEFORE]) > 0);
		$this->assertTrue(count($rules['build']) == 0);
		$this->assertTrue(count($rules['build' . JRouter::PROCESS_AFTER]) > 0);

		$config = array(
			array('sef', null, 1),
			array('force_ssl', null, 2),
			array('sef_suffix', null, 1),
			array('sef_rewrite', null, 1)
		);
		$app = $this->getMockCmsApp();
		$app->method('get')->will($this->returnValueMap($config));
		$object = new JRouterSite($app, $app->getMenu());
		$rules = $object->getRules();
		$this->assertTrue(count($rules['parse' . JRouter::PROCESS_BEFORE]) == 3);
		$this->assertTrue(count($rules['parse']) == 2);
		$this->assertTrue(count($rules['parse' . JRouter::PROCESS_AFTER]) == 1);
		$this->assertTrue(count($rules['build' . JRouter::PROCESS_BEFORE]) == 2);
		$this->assertTrue(count($rules['build']) == 1);
		$this->assertTrue(count($rules['build' . JRouter::PROCESS_AFTER]) == 4);
	}

	/**
	 * Tests the parseCheckSSL method
	 *
	 * @return  void
	 *
	 * @since         4.0
	 */
	public function testParseCheckSSL()
	{
		$app = $this->getMockCmsApp();
		$app->expects($this->never())
			->method('redirect');
		$object = new JRouterSite(
				$app,
				$app->getMenu()
			);

		$uri = new JUri('https://www.example.test');

		// No redirect and no error with a https URL
		$object->parseCheckSSL($object, $uri);

		$app = $this->getMockCmsApp();
		$app->expects($this->once())
			->method('redirect');
		$object = new JRouterSite(
				$app,
				$app->getMenu()
			);

		$uri = new JUri('http://www.example.test');

		$object->parseCheckSSL($object, $uri);

		$this->assertEquals('https', $uri->getScheme());
	}

	/**
	 * Cases for testParseInit
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public function casesParseInit()
	{
		$server1 = array(
			'HTTP_HOST'   => '',
			'SCRIPT_NAME' => '',
			'PHP_SELF'    => '',
			'REQUEST_URI' => ''
		);

		$server2 = array(
			'HTTP_HOST'   => 'www.example.com:80',
			'SCRIPT_NAME' => '/joomla/index.php',
			'PHP_SELF'    => '/joomla/index.php',
			'REQUEST_URI' => '/joomla/index.php?var=value 10'
		);
		$server3 = array(
			'HTTP_HOST'       => '',
			'SCRIPT_NAME'     => '',
			'SCRIPT_FILENAME' => JPATH_SITE . '/cli/deletefiles.php',
			'PHP_SELF'        => '',
			'REQUEST_URI'     => ''
		);
		return array(
			array(
				'url'     => '/joomla/blog/test%202',
				'server'  => $server1,
				'expUrl'  => 'joomla/blog/test 2'
			),
			array(
				'url'     => '/joomla/blog/te%20st',
				'server'  => $server2,
				'expUrl'  => 'blog/te st'
			),
			array(
				'url'     => '/cli/deletefiles.php?var1=value1',
				'server'  => $server3,
				'expUrl'  => '?var1=value1'
			)
		);
	}

	/**
	 * Tests the parseInit method
	 *
	 * @param   string  $url          An associative array with variables
	 * @param   array   $server       An associative array with $_SERVER vars
	 * @param   string  $expectedUris Expected URI string
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseInit
	 * @since         4.0
	 */
	public function testParseInit($url, $server, $expectedUris)
	{
		$_SERVER = array_merge($_SERVER, $server);

		$uri  = new JUri($url);
		$this->object->parseInit($this->object, $uri);

		$this->assertEquals($expectedUris, (string) $uri);
	}

	/**
	 * Tests the parseFormat method
	 *
	 * @return  void
	 *
	 * @testdox       Parse formats
	 * @since         4.0
	 */
	public function testParseFormat()
	{
		$uri = new JUri('index.php');
		$this->object->parseFormat($this->object, $uri);

		$this->assertEquals('index.php', $uri->getPath());
		$this->assertEquals(array(), $uri->getQuery(true));

		$uri2 = new JUri('/test/');
		$this->object->parseFormat($this->object, $uri2);
		$this->assertEquals('/test/', $uri2->getPath());
		$this->assertEquals(array(), $uri2->getQuery(true));

		$uri3 = new JUri('/test.html');
		$this->object->parseFormat($this->object, $uri3);
		$this->assertEquals('/test', $uri3->getPath());
		$this->assertEquals(array('format' => 'html'), $uri3->getQuery(true));

		$uri4 = new JUri('/test.json');
		$this->object->parseFormat($this->object, $uri4);
		$this->assertEquals('/test', $uri4->getPath());
		$this->assertEquals(array('format' => 'json'), $uri4->getQuery(true));

		$uri5 = new JUri('/index.php/test.html');
		$this->object->parseFormat($this->object, $uri5);
		$this->assertEquals('/index.php/test', $uri5->getPath());
		$this->assertEquals(array('format' => 'html'), $uri5->getQuery(true));
	}

	/**
	 * Cases for testParseSefRoute
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public function casesParseSefRoute()
	{
		return array(
			// Empty URLs without a default menu item return nothing
			'empty-sef'                     => array(
				'',
				''
			),
			// Absolute URLs to the domain of the site
			'matching-menu'     => array(
				'test',
				'?Itemid=42&option=com_test'
			),
			'abs-sef-path-no_qs-no_sfx'     => array(
				'test/path',
				'path?Itemid=42&option=com_test'
			),
			'abs-sef-path-no_qs-no'     => array(
				'path',
				'path?Itemid=45&option=com_test3'
			),
			'abs-sef-path-qs-no_sfx'        => array(
				'test/path?testvar=testvalue',
				'path?testvar=testvalue&Itemid=42&option=com_test'
			),
			'abs-sef-no_path-qs-no_sfx'     => array(
				'?testvar=testvalue',
				'?testvar=testvalue'
			),
			// URLs with /component/something
			'comp-sef-2lvl'                 => array(
				'component/test',
				'?option=com_test'
			),
			'comp-sef-3lvl'                 => array(
				'component/test2/something',
				'something?option=com_test2&testvar=testvalue'
			)
		);
	}

	/**
	 * Tests the parseSefRoute method
	 *
	 * @param   string  $url       A URL string
	 * @param   array   $expected  An expected URL string
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseSefRoute
	 * @testdox       Parse SEF route
	 * @since         4.0
	 */
	public function testParseSefRoute($url, $expected)
	{
		$uri  = new JUri($url);
		$this->object->parseSefRoute($this->object, $uri);

		$this->assertEquals($expected, (string) $uri);
	}

	/**
	 * Tests the parseRawRoute method
	 *
	 * @return  void
	 *
	 * @testdox       Build formats
	 * @since         4.0
	 */
	public function testParseRawRoute()
	{
		$uri = new JUri('index.php');
		$this->object->parseRawRoute($this->object, $uri);
		$this->assertEquals('index.php?option=com_test3&view=test3&Itemid=45', (string) $uri);

		$uri = new JUri('index.php?Itemid=43');
		$this->object->parseRawRoute($this->object, $uri);
		$this->assertEquals('index.php?option=com_test2&view=test&Itemid=43', (string) $uri);
	}

	/**
	 * Tests the parsePaginationData method
	 *
	 * @return  void
	 *
	 * @testdox       Build formats
	 * @since         4.0
	 */
	public function testParsePaginationData()
	{
		$uri = new JUri('index.php');
		$this->object->parsePaginationData($this->object, $uri);
		$this->assertEquals(array(), $uri->getQuery(true));

		$uri = new JUri('/test?start=23wrong');
		$this->object->parsePaginationData($this->object, $uri);
		$this->assertEquals(array('limitstart' => '23wrong'), $uri->getQuery(true));
	}

	/**
	 * Tests the buildInit() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildInit() executes a component router's preprocess method
	 * @since   4.0
	 */
	public function testBuildInit()
	{
		// Assert a URL with option and Itemid is not touched
		$uri = new JUri('index.php?option=com_test&Itemid=42');
		$this->object->buildInit($this->object, $uri);
		$this->assertEquals('index.php?option=com_test&Itemid=42', (string)$uri);

		// Assert a URL with only an Itemid set gets the right option set in the request
		$uri = new JUri('index.php?Itemid=42');
		$this->object->buildInit($this->object, $uri);
		$this->assertEquals('index.php?Itemid=42&option=com_test', (string)$uri);

		// Assert current vars are merged into request if no option and Itemid set
		$uri = new JUri('index.php?lang=en-GB');
		$this->object->setVar('current', 'var');
		$this->object->buildInit($this->object, $uri);
		$this->assertEquals('index.php?current=var&lang=en-GB', (string)$uri);

		// Assert current vars don't overwrite query params of the request
		$uri = new JUri('index.php?view=test42&data=42');
		$this->object->setVar('view', 'test');
		$this->object->buildInit($this->object, $uri);
		$this->assertEquals('index.php?current=var&view=test42&data=42', (string)$uri);
	}

	/**
	 * Tests the buildComponentPreprocess() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildComponentPreprocess() executes a component router's preprocess method
	 * @since   4.0
	 */
	public function testBuildComponentPreprocess()
	{
		// Assert preprocess exits without option
		$uri = new JUri('index.php?test=true');
		$this->object->buildComponentPreprocess($this->object, $uri);
		$this->assertEquals('index.php?test=true', (string)$uri);

		// Assert preprocess of Component router is run
		$uri = new JUri('index.php?option=com_test');
		$this->object->buildComponentPreprocess($this->object, $uri);
		$this->assertEquals('index.php?option=com_test&testvar=testvalue', (string)$uri);

		// Assert menu query is merged into request
		$uri = new JUri('index.php?option=com_test42&Itemid=42');
		$this->object->buildComponentPreprocess($this->object, $uri);
		$this->assertEquals('index.php?option=com_test42&view=test&Itemid=42', (string)$uri);

		// Assert menu query is merged into request with language
		$uri = new JUri('index.php?option=com_test42&Itemid=42&lang=en-GB');
		$this->object->buildComponentPreprocess($this->object, $uri);
		$this->assertEquals('index.php?option=com_test42&view=test&Itemid=42&lang=en-GB', (string)$uri);
	}

	/**
	 * Cases for testBuildSefRoute
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesBuildSefRoute()
	{
		return array(
			'Empty URLs are returned identically'                                     => array(
				'url'      => '',
				'expected' => ''
			),
			'URLs without an option are returned identically'                         => array(
				'url'      => 'index.php?var1=value1',
				'expected' => 'index.php?var1=value1'
			),
			'The menu item is properly prepended'                                     => array(
				'url'      => 'index.php?option=com_test&var1=value1&Itemid=42',
				'expected' => 'index.php/test?var1=value1'
			),
			'A non existing menu item is correctly ignored'                           => array(
				'url'      => 'index.php?option=com_test&var1=value1&Itemid=41',
				'expected' => 'index.php/component/test?var1=value1&Itemid=41'
			),
			'A menu item with a parent is properly prepended'                         => array(
				'url'      => 'index.php?option=com_test&var1=value1&Itemid=46',
				'expected' => 'index.php/test/sub-menu?var1=value1'
			),
			'Component router build: The menu item is properly prepended'             => array(
				'url'      => 'index.php?option=com_test2&var1=value1&Itemid=43',
				'expected' => 'index.php/test2/router-test/another-segment?var1=value1'
			),
			'Component router build: A non existing menu item is correctly ignored'   => array(
				'url'      => 'index.php?option=com_test2&var1=value1&Itemid=41',
				'expected' => 'index.php/component/test2/router-test/another-segment?var1=value1&Itemid=41'
			),
			'Component router build: A menu item with a parent is properly prepended' => array(
				'url'      => 'index.php?option=com_test2&var1=value1&Itemid=44',
				'expected' => 'index.php/test2/sub-menu/router-test/another-segment?var1=value1'
			),
			'A home menu item is treated properly (without vars)'                     => array(
				'url'      => 'index.php?Itemid=45&option=com_test3',
				'expected' => 'index.php'
			),
			'A home menu item is treated properly (with vars)'                        => array(
				'url'      => 'index.php?Itemid=45&option=com_test3&testvar=testvalue',
				'expected' => 'index.php?testvar=testvalue'
			),
		);
	}

	/**
	 * testBuildSefRoute().
	 *
	 * @param   string $url      Input URL
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider casesBuildSefRoute
	 *
	 * @return void
	 * @testdox      Build SEF route
	 * @since        3.4
	 */
	public function testBuildSefRoute($url, $expected)
	{
		$uri = new JUri($url);

		$this->object->buildSefRoute($this->object, $uri);

		$this->assertEquals($expected, (string) $uri);
	}

	/**
	 * Tests the buildPaginationData method
	 *
	 * @return  void
	 *
	 * @testdox       Build formats
	 * @since         4.0
	 */
	public function testBuildPaginationData()
	{
		$uri = new JUri('index.php');
		$this->object->buildPaginationData($this->object, $uri);
		$this->assertEquals(array(), $uri->getQuery(true));

		$uri2 = new JUri('/test?limitstart=23wrong');
		$this->object->buildPaginationData($this->object, $uri2);
		$this->assertEquals(array('start' => 23), $uri2->getQuery(true));
	}

	/**
	 * Tests the buildFormat method
	 *
	 * @return  void
	 *
	 * @testdox       Build formats
	 * @since         4.0
	 */
	public function testBuildFormat()
	{
		$uri = new JUri('index.php');
		$this->object->buildFormat($this->object, $uri);
		$this->assertEquals('index.php', $uri->getPath());
		$this->assertEquals(array(), $uri->getQuery(true));

		$uri2 = new JUri('/test/');
		$this->object->buildFormat($this->object, $uri2);
		$this->assertEquals('/test/', $uri2->getPath());
		$this->assertEquals(array(), $uri2->getQuery(true));

		$uri3 = new JUri('/test');
		$this->object->buildFormat($this->object, $uri3);
		$this->assertEquals('/test.html', $uri3->getPath());
		$this->assertEquals(array(), $uri3->getQuery(true));

		$uri4 = new JUri('/test?format=json');
		$this->object->buildFormat($this->object, $uri4);
		$this->assertEquals('/test.json', $uri4->getPath());
		$this->assertEquals(array(), $uri4->getQuery(true));

		$uri5 = new JUri('/index.php/test?format=html&test=true');
		$this->object->buildFormat($this->object, $uri5);
		$this->assertEquals('/index.php/test.html', $uri5->getPath());
		$this->assertEquals(array('test' => 'true'), $uri5->getQuery(true));
	}

	/**
	 * Tests the buildRewrite() method
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function testBuildRewrite()
	{
		$uri = new JUri('');
		$this->object->buildRewrite($this->object, $uri);
		$this->assertEquals('', $uri->getPath());

		$uri = new JUri('index.php');
		$this->object->buildRewrite($this->object, $uri);
		$this->assertEquals('', $uri->getPath());

		$uri = new JUri('index.php/test/path/');
		$this->object->buildRewrite($this->object, $uri);
		$this->assertEquals('test/path/', $uri->getPath());
	}

	/**
	 * Tests the buildBase() method
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function testBuildBase()
	{
		$server = array(
			'HTTP_HOST'   => 'www.example.com:80',
			'SCRIPT_NAME' => '/joomla/index.php',
			'PHP_SELF'    => '/joomla/index.php',
			'REQUEST_URI' => '/joomla/index.php?var=value 10'
		);

		$_SERVER = array_merge($_SERVER, $server);

		$uri = new JUri('index.php');
		$this->assertEquals('index.php', $uri->getPath());
		$this->object->buildBase($this->object, $uri);
		$this->assertEquals(JUri::base(true) . '/' . 'index.php', $uri->getPath());
	}

	/**
	 * Tests the getComponentRouter() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testGetComponentRouter()
	{
		/**
		 * Get the TestRouter and check if you get the
		 * same object instance the second time
		 */
		$router = $this->object->getComponentRouter('com_test');

		$this->assertInstanceOf('TestRouter', $router);
		$this->assertSame($router, $this->object->getComponentRouter('com_test'));

		/**
		 * Check if a proper router is automatically loaded
		 * by loading the router of com_content
		 */
		$this->assertInstanceOf('ContentRouter', $this->object->getComponentRouter('com_content'));

		/**
		 * Check if an instance of JComponentRouterLegacy
		 * is returned for non-existing routers
		 */
		$this->assertInstanceOf('JComponentRouterLegacy', $this->object->getComponentRouter('com_legacy'));
	}

	/**
	 * Tests the setComponentRouter() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testValidRouterGetsAccepted()
	{
		$router = new TestRouter;

		$this->assertTrue($this->object->setComponentRouter('com_test', $router));
		$this->assertSame($router, $this->object->getComponentRouter('com_test'));
	}

	/**
	 * Tests the setComponentRouter() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testInvalidRouterIsRejected()
	{
		$this->assertFalse($this->object->setComponentRouter('com_test3', new stdClass));
	}
}
