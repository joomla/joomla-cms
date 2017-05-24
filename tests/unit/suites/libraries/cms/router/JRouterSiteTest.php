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

		parent::tearDown();
	}

	/**
	 * Tests the __construct() method
	 *
	 * @return  void
	 * @testdox JRouterSite is a JRouter
	 * @since   3.4
	 */
	public function testJRouterSiteIsAJRouter()
	{
		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$this->assertInstanceOf('JRouter', $object);
	}

	/**
	 * Cases for testParse
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesParse()
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
				'url'     => '',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server1,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => ''
			),
			array(
				'url'     => '/index.php?var1=value1',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server1,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'index.php?var1=value1'
			),
			array(
				'url'     => 'index.php?var1=value1',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server1,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'index.php?var1=value1'
			),
			array(
				'url'     => '/joomla/blog/test.json',
				'mode'    => JROUTER_MODE_SEF,
				'map'     => array(array('sef_suffix', null, '1')),
				'server'  => $server1,
				'expVars' => array('format' => 'json', 'option' => 'com_test3', 'Itemid' => '45'),
				'expUrl'  => 'joomla/blog/test.json'
			),
			array(
				'url'     => '/joomla/blog/test.json/',
				'mode'    => JROUTER_MODE_SEF,
				'map'     => array(array('sef_suffix', null, '1')),
				'server'  => $server1,
				'expVars' => array('option' => 'com_test3', 'Itemid' => '45'),
				'expUrl'  => 'joomla/blog/test.json'
			),
			array(
				'url'     => '/joomla/blog/test%202',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server1,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'joomla/blog/test 2'
			),
			array(
				'url'     => '/joomla/blog/test',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server2,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'blog/test'
			),
			array(
				'url'     => '/joomla/blog/te%20st',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server2,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'blog/te st'
			),
			array(
				'url'     => '/otherfolder/blog/test',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server2,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => 'older/blog/test'
			),
			array(
				'url'     => '/cli/deletefiles.php?var1=value1',
				'mode'    => JROUTER_MODE_RAW,
				'map'     => array(),
				'server'  => $server3,
				'expVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expUrl'  => '?var1=value1'
			),
		);
	}

	/**
	 * Tests the parse method
	 *
	 * @param   string  $url          An associative array with variables
	 * @param   integer $mode         JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array   $map          An associative array with app config vars
	 * @param   array   $server       An associative array with $_SERVER vars
	 * @param   array   $expectedVars Expected vars
	 * @param   string  $expectedUris Expected URI string
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParse
	 * @testdox       URLs are transformed into proper variables
	 * @since         3.4
	 */
	public function testParse($url, $mode, $map, $server, $expectedVars, $expectedUris)
	{
		$_SERVER = array_merge($_SERVER, $server);

		$app = $this->getMockCmsApp();
		$app->expects($this->any())
			->method('get')
			->will($this->returnValueMap($map));

		$object = new JRouterSite(
			array(),
			$app,
			TestMockMenu::create($this)
		);

		$object->setMode($mode);

		$uri  = new JUri($url);
		$vars = $object->parse($uri);

		$this->assertEquals($expectedVars, $vars);
		$this->assertEquals($expectedUris, (string) $uri);
	}

	/**
	 * Tests the parse methods redirect
	 *
	 * @return  void
	 * @testdox External URLs trigger a redirect
	 * @since   3.4
	 */
	public function testParseRedirect()
	{
		$uri = new JUri('http://www.example.com/index.php');

		$app = $this->getMockCmsApp();
		$app->expects($this->any())
			->method('get')
			->will($this->returnValue(2));
		$app->expects($this->once())
			->method('redirect');

		$object = new JRouterSite(
			array(),
			$app,
			TestMockMenu::create($this)
		);

		$object->parse($uri);
	}

	/**
	 * Cases for testBuild
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesBuild()
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

		return array(
			array(
				'url'      => '',
				'mode'     => JROUTER_MODE_RAW,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server1,
				'expected' => '/'
			),
			array(
				'url'      => 'blog/test',
				'mode'     => JROUTER_MODE_RAW,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server1,
				'expected' => '/blog/test'
			),
			array(
				'url'      => '',
				'mode'     => JROUTER_MODE_RAW,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server2,
				'expected' => '/joomla/'
			),
			array(
				'url'      => 'blog/test',
				'mode'     => JROUTER_MODE_RAW,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server2,
				'expected' => '/joomla/blog/test'
			),
			array(
				'url'      => '',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server1,
				'expected' => '/'
			),
			array(
				'url'      => 'blog/test',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server1,
				'expected' => '/blog/test'
			),
			array(
				'url'      => '',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server2,
				'expected' => '/joomla/'
			),
			array(
				'url'      => 'blog/test',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(),
				'server'   => $server2,
				'expected' => '/joomla/blog/test'
			),
			array(
				'url'      => 'index.php',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/'
			),
			array(
				'url'      => 'index.php/blog/test',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/blog/test'
			),
			array(
				'url'      => 'index.php',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1)),
				'server'   => $server1,
				'expected' => '/'
			),
			array(
				'url'      => 'index.php/blog/test',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1)),
				'server'   => $server1,
				'expected' => '/blog/test'
			),
			array(
				'url'      => 'index.php?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_suffix', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/index.php?format=json'
			),
			array(
				'url'      => 'index.php/blog/test?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_suffix', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/index.php/blog/test.json'
			),
			array(
				'url'      => 'index.php?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_suffix', null, 1)),
				'server'   => $server1,
				'expected' => '/index.php?format=json'
			),
			array(
				'url'      => 'index.php/blog/test?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_suffix', null, 1)),
				'server'   => $server1,
				'expected' => '/index.php/blog/test.json'
			),
			array(
				'url'      => 'index.php?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1), array('sef_suffix', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/?format=json'
			),
			array(
				'url'      => 'index.php/blog/test?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1), array('sef_suffix', null, 1)),
				'server'   => $server2,
				'expected' => '/joomla/blog/test.json'
			),
			array(
				'url'      => 'index.php?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1), array('sef_suffix', null, 1)),
				'server'   => $server1,
				'expected' => '/?format=json'
			),
			array(
				'url'      => 'index.php/blog/test?format=json',
				'mode'     => JROUTER_MODE_SEF,
				'vars'     => array(),
				'map'      => array(array('sef_rewrite', null, 1), array('sef_suffix', null, 1)),
				'server'   => $server1,
				'expected' => '/blog/test.json'
			),
		);
	}

	/**
	 * testBuild().
	 *
	 * @param   string  $url      The URL
	 * @param   integer $mode     JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array   $vars     An associative array with global variables
	 * @param   array   $map      Valuemap for JApplication::get() Mock
	 * @param   array   $server   Values for $_SERVER
	 * @param   array   $expected Expected value
	 *
	 * @dataProvider casesBuild
	 *
	 * @return void
	 * @testdox      Variables are transformed into proper URLs
	 * @since        3.4
	 */
	public function testBuild($url, $mode, $vars, $map, $server, $expected)
	{
		$_SERVER = array_merge($_SERVER, $server);

		$app = $this->getMockCmsApp();
		$app->expects($this->any())
			->method('get')
			->will($this->returnValueMap($map));

		$object = new JRouterSite(
			array(),
			$app,
			TestMockMenu::create($this)
		);

		$object->setMode($mode);

		// Check the expected values
		$this->assertEquals($expected, (string) ($object->build($url)));
	}

	/**
	 * Cases for testParseRawRoute
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesParseRawRoute()
	{
		return array(
			'no_url-sef'           => array(
				'url'          => '',
				'mode'         => JROUTER_MODE_SEF,
				'expParseVars' => array(),
				'expObjVars'   => array()
			),
			'no_url-raw-default'   => array(
				'url'          => '',
				'mode'         => JROUTER_MODE_RAW,
				'expParseVars' => array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'),
				'expObjVars'   => array()
			),
			'url-sef-query-itemid' => array(
				'url'          => 'index.php?option=com_test&Itemid=42&testvar=testvalue',
				'mode'         => JROUTER_MODE_SEF,
				'expParseVars' => array(),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => '42', 'testvar' => 'testvalue')
			),
			'url-sef-itemid'       => array(
				'url'          => 'index.php?Itemid=42',
				'mode'         => JROUTER_MODE_SEF,
				'expParseVars' => array('option' => 'com_test', 'view' => 'test'),
				'expObjVars'   => array('Itemid' => 42)
			),
		);
	}

	/**
	 * Tests the parse method
	 *
	 * @param   string  $url                An associative array with variables
	 * @param   integer $mode               JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array   $expectedParseVars  An associative array with $_SERVER vars
	 * @param   array   $expectedObjectVars An associative array with $_SERVER vars
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseRawRoute
	 * @since         3.4
	 */
	public function testParseRawRoute($url, $mode, $expectedParseVars, $expectedObjectVars)
	{
		$app = $this->getMockCmsApp();

		if (isset($expectedObjectVars['Itemid']))
		{
			$app->input->set('Itemid', $expectedObjectVars['Itemid']);
		}

		if ($mode == JROUTER_MODE_SEF)
		{
			$menu = TestMockMenu::create($this, false);
			$menu
				->expects($this->any())
				->method('getDefault')
				->will($this->returnValue(null));
		}
		else
		{
			$menu = TestMockMenu::create($this, true);
		}

		$object = new JRouterSite(
			array(),
			$app,
			$menu
		);

		$parseRawRouteMethod = new ReflectionMethod('JRouterSite', 'parseRawRoute');
		$parseRawRouteMethod->setAccessible(true);

		$uri  = new JUri($url);
		$vars = $parseRawRouteMethod->invokeArgs($object, array(&$uri));

		$this->assertEquals(
			$expectedParseVars,
			$vars,
			"JRouterSite::parseRawRoute() did not return the expected values."
		);
		$this->assertEquals(
			$expectedObjectVars,
			$object->getVars(),
			"JRouterSite did not have the expected values internally."
		);
	}

	/**
	 * Cases for testParseSefRoute
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesParseSefRoute()
	{
		return array(
			// Empty URLs without a default menu item return nothing
			'empty-sef'                     => array(
				'url'          => '',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array()
			),
			// Absolute URLs to the domain of the site
			'abs-sef-path-no_qs-no_sfx'     => array(
				'url'          => '/test/path',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array()
			),
			'abs-sef-path-qs-no_sfx'        => array(
				'url'          => '/test/path?testvar=testvalue',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue')
			),
			'abs-sef-no_path-qs-no_sfx'     => array(
				'url'          => '?testvar=testvalue',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array()
			),
			'abs-sef-path.ext-no_qs-no_sfx' => array(
				'url'          => '/test/path.json',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array()
			),
			'abs-sef-path.ext-qs-no_sfx'    => array(
				'url'          => '/test/path.json?testvar=testvalue',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue')
			),
			'abs-sef-path.ext-no_qs-sfx'    => array(
				'url'          => '/test/path.json',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(array('sef_suffix', null, '1')),
				'expParseVars' => array(),
				'expObjVars'   => array()
			),
			'abs-sef-path.ext-qs-sfx'       => array(
				'url'          => '/test/path.json?testvar=testvalue',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(array('sef_suffix', null, '1')),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue')
			),
			'empty-raw'                     => array(
				'url'          => '',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'),
				'expObjVars'   => array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3')
			),
			'abs-raw-path-no_qs-no_sfx'     => array(
				'url'          => '/test/path',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('Itemid' => '45', 'option' => 'com_test3')
			),
			'abs-raw-path-qs-no_sfx'        => array(
				'url'          => '/test/path?testvar=testvalue',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3')
			),
			'abs-raw-no_path-qs-no_sfx'     => array(
				'url'          => '?testvar=testvalue',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'),
				'expObjVars'   => array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3')
			),
			'abs-raw-path.ext-no_qs-no_sfx' => array(
				'url'          => '/test/path.json',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('Itemid' => '45', 'option' => 'com_test3')
			),
			'abs-raw-path.ext-qs-no_sfx'    => array(
				'url'          => '/test/path.json?testvar=testvalue',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3')
			),
			'abs-raw-path.ext-no_qs-sfx'    => array(
				'url'          => '/test/path.json',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(array('sef_suffix', null, '1')),
				'expParseVars' => array(),
				'expObjVars'   => array('Itemid' => '45', 'option' => 'com_test3')
			),
			'abs-raw-path.ext-qs-sfx'       => array(
				'url'          => '/test/path.json?testvar=testvalue',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(array('sef_suffix', null, '1')),
				'expParseVars' => array(),
				'expObjVars'   => array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3')
			),
			// Non-SEF URLs
			'raw-sef-no_id-no_opt'          => array(
				'url'          => '?option=com_test',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			'raw-sef-id-no_opt'             => array(
				'url'          => '?Itemid=42',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('Itemid' => null)
			),
			'raw-sef-id-opt'                => array(
				'url'          => '?Itemid=42&option=com_test',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			'raw-raw-no_id-opt'             => array(
				'url'          => '?option=com_test',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			// 20
			'20-raw-id-no_opt'              => array(
				'url'          => '?Itemid=42',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('Itemid' => null)
			),
			'20-raw-id-opt'                 => array(
				'url'          => '?Itemid=42&option=com_test',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array(),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			// URLs with /component/something
			'comp-sef-2lvl'                 => array(
				'url'          => 'component/test',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('option' => 'com_test', 'Itemid' => null),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			'comp-raw-2lvl'                 => array(
				'url'          => 'component/test',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('option' => 'com_test', 'Itemid' => null),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => null)
			),
			'comp-sef-3lvl'                 => array(
				'url'          => 'component/test2/something',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => null)
			),
			'comp-raw-3lvl'                 => array(
				'url'          => 'component/test2/something',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => null, 'testvar' => 'testvalue')
			),
			// Parse current menu items
			'curr-sef-no_lang-2lvl'         => array(
				'url'          => 'test2/sub-menu',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('option' => 'com_test2', 'Itemid' => 44),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-raw-no_lang-2lvl'         => array(
				'url'          => 'test2/sub-menu',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('option' => 'com_test2', 'Itemid' => 44),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-sef-no_lang-3lvl'         => array(
				'url'          => 'test2/sub-menu/something',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-raw-no_lang-3lvl'         => array(
				'url'          => 'test2/sub-menu/something',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array(),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44, 'testvar' => 'testvalue')
			),
			'curr-sef-lang-2lvl'            => array(
				'url'          => 'test2/sub-menu',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array('languagefilter' => true),
				'expParseVars' => array('option' => 'com_test2', 'Itemid' => 44),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-raw-lang-2lvl'            => array(
				'url'          => 'test2/sub-menu',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array('languagefilter' => true),
				'expParseVars' => array('option' => 'com_test2', 'Itemid' => 44),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-sef-lang-3lvl'            => array(
				'url'          => 'test2/sub-menu/something',
				'mode'         => JROUTER_MODE_SEF,
				'appConfig'    => array('languagefilter' => true),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => 44)
			),
			'curr-raw-lang-3lvl'            => array(
				'url'          => 'test2/sub-menu/something',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array('languagefilter' => true),
				'expParseVars' => array('testvar' => 'testvalue'),
				'expObjVars'   => array('option' => 'com_test2', 'Itemid' => 44, 'testvar' => 'testvalue')
			),
			'engl-raw-lang'                 => array(
				'url'          => 'english-test',
				'mode'         => JROUTER_MODE_RAW,
				'appConfig'    => array('languagefilter' => true),
				'expParseVars' => array('option' => 'com_test', 'view' => 'test2'),
				'expObjVars'   => array('option' => 'com_test', 'Itemid' => '47'),
				'itemid'       => 47
			),
		);
	}

	/**
	 * Tests the parseSefRoute method
	 *
	 * @param   string  $url                An associative array with variables
	 * @param   integer $mode               JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array   $appConfig          An associative array with app config vars
	 * @param   array   $expectedParseVars  An associative array with $_SERVER vars
	 * @param   array   $expectedObjectVars An associative array with $_SERVER vars
	 * @param   boolean $setActive          Flag if the item is the active menu
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseSefRoute
	 * @testdox       Parse SEF route
	 * @since         3.4
	 */
	public function testParseSefRoute($url, $mode, $appConfig, $expectedParseVars, $expectedObjectVars, $setActive = false)
	{
		$app = $this->getMockCmsApp();

		if (isset($expectedParseVars['Itemid']))
		{
			$app->input->set('Itemid', $expectedParseVars['Itemid']);
		}

		if (isset($appConfig['languagefilter']))
		{
			$app->expects($this->any())
				->method('getLanguageFilter')
				->will($this->returnValue(true));
			unset($appConfig['languagefilter']);
		}
		$app->expects($this->any())
			->method('get')
			->will($this->returnValueMap($appConfig));

		if ($mode == JROUTER_MODE_SEF)
		{
			$menu = TestMockMenu::create($this, false, $setActive);
			$menu
				->expects($this->any())
				->method('getDefault')
				->will($this->returnValue(null));
		}
		else
		{
			$menu = TestMockMenu::create($this, true, $setActive);
		}

		$object = new JRouterSite(
			array(),
			$app,
			$menu
		);

		$parseSefRouteMethod = new ReflectionMethod('JRouterSite', 'parseSefRoute');
		$parseSefRouteMethod->setAccessible(true);

		$uri  = new JUri($url);
		$vars = $parseSefRouteMethod->invokeArgs($object, array(&$uri));

		$this->assertEquals(
			$expectedParseVars,
			$vars,
			"JRouterSite::parseSefRoute() did not return the expected values."
		);
		$this->assertEquals(
			$expectedObjectVars,
			$object->getVars(),
			"JRouterSite did not have the expected values internally."
		);
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildRawRoute() does not change a URL without an option
	 * @since   3.4
	 */
	public function testBuildRawRoute()
	{
		$uri = new JUri('index.php');

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$buildRawRouteMethod = new ReflectionMethod('JRouterSite', 'buildRawRoute');
		$buildRawRouteMethod->setAccessible(true);

		$buildRawRouteMethod->invokeArgs($object, array(&$uri));
		$this->assertEquals('index.php', (string) $uri);
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildRawRoute() executes a component router's preprocess method
	 * @since   3.4
	 */
	public function testAComponentRoutersPreprocessMethodIsExecuted()
	{
		$uri = new JUri('index.php');

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$buildRawRouteMethod = new ReflectionMethod('JRouterSite', 'buildRawRoute');
		$buildRawRouteMethod->setAccessible(true);

		$uri->setVar('option', 'com_test');
		$buildRawRouteMethod->invokeArgs($object, array(&$uri));
		$this->assertEquals('index.php?option=com_test&testvar=testvalue', (string) $uri);
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildRawRoute() sanitizes broken options to get the right router
	 * @since   3.4
	 */
	public function testABrokenOptionIsProperlySanitisedToGetTheRightRouter()
	{
		$uri = new JUri('index.php');

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$buildRawRouteMethod = new ReflectionMethod('JRouterSite', 'buildRawRoute');
		$buildRawRouteMethod->setAccessible(true);

		$uri->setVar('option', 'com_ te?st');
		$uri->delVar('testvar');
		$buildRawRouteMethod->invokeArgs($object, array(&$uri));
		$this->assertEquals('index.php?option=com_ te?st&testvar=testvalue', (string) $uri);
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 * @testdox JRouterSite::buildRawRoute() executes a legacy component router's preprocess method
	 * @since   3.4
	 */
	public function testALegacyComponentRoutersPreprocessMethodIsExecuted()
	{
		$uri = new JUri('index.php');

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$buildRawRouteMethod = new ReflectionMethod('JRouterSite', 'buildRawRoute');
		$buildRawRouteMethod->setAccessible(true);

		$uri->setVar('option', 'com_test3');
		$uri->delVar('testvar');
		$buildRawRouteMethod->invokeArgs($object, array(&$uri));
		$this->assertEquals('index.php?option=com_test3', (string) $uri);
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
				'expected' => 'index.php/component/test/?var1=value1&Itemid=41'
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
				'expected' => 'index.php/component/test3/?Itemid=45'
			),
			'A home menu item is treated properly (with vars)'                        => array(
				'url'      => 'index.php?Itemid=45&option=com_test3&testvar=testvalue',
				'expected' => 'index.php/component/test3/?testvar=testvalue&Itemid=45'
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

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$buildSefRouteMethod = new ReflectionMethod('JRouterSite', 'buildSefRoute');
		$buildSefRouteMethod->setAccessible(true);
		$buildSefRouteMethod->invokeArgs($object, array(&$uri));

		$this->assertEquals($expected, (string) $uri);
	}

	/**
	 * Tests the processParseRules() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testProcessParseRules()
	{
		$uri = new JUri('index.php?start=42');

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);
		$object->setMode(JROUTER_MODE_SEF);

		$processParseRulesMethod = new ReflectionMethod('JRouterSite', 'processParseRules');
		$processParseRulesMethod->setAccessible(true);

		$vars = $processParseRulesMethod->invokeArgs($object, array(&$uri));

		$this->assertEquals('index.php', $uri->toString());
		$this->assertEquals(array('limitstart' => '42'), $vars);
	}

	/**
	 * Cases for testProcessBuildRules
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesProcessBuildRules()
	{
		return array(
			// Check if an empty URL is returned as an empty URL
			'empty'      => array(
				'url'      => '',
				'mode'     => JROUTER_MODE_RAW,
				'expected' => ''
			),
			/**
			 * Check if a URL with an Itemid and another query parameter
			 * is replaced with the query of the menu item plus the Itemid
			 * when mode is not SEF
			 */
			'raw'        => array(
				'url'      => 'index.php?Itemid=42&test=true',
				'mode'     => JROUTER_MODE_RAW,
				'expected' => 'index.php?option=com_test&view=test&Itemid=42'
			),
			/**
			 * Check if a URL with an Itemid and another query parameter
			 * is returned identical when mode is SEF
			 */
			'sef'        => array(
				'url'      => 'index.php?Itemid=42&test=true',
				'mode'     => JROUTER_MODE_SEF,
				'expected' => 'index.php?Itemid=42&test=true'
			),
			/**
			 * Check if a URL with a path and limitstart gets the limitstart
			 * parameter converted to start when mode is SEF
			 */
			'limitstart' => array(
				'url'      => 'test?limitstart=42',
				'mode'     => JROUTER_MODE_SEF,
				'expected' => 'test?start=42'
			),
		);
	}

	/**
	 * testProcessBuildRules().
	 *
	 * @param   string $url      Input URL
	 * @param   int    $mode
	 * @param   string $expected Expected return value
	 *
	 * @dataProvider casesProcessBuildRules
	 *
	 * @since        3.4
	 */
	public function testProcessBuildRules($url, $mode, $expected)
	{
		$uri = new JUri($url);

		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);
		$object->setMode($mode);

		$processBuildRulesMethod = new ReflectionMethod('JRouterSite', 'processBuildRules');
		$processBuildRulesMethod->setAccessible(true);

		$processBuildRulesMethod->invokeArgs($object, array(&$uri));

		$this->assertEquals($expected, (string) $uri);
	}

	/**
	 * Cases for testCreateURI
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesCreateUri()
	{
		return array(
			// Check if a rather non-URL is returned identical
			array(
				'url'      => 'index.php?var1=value&var2=value2',
				'preset'   => array(),
				'expected' => 'index.php?var1=value&var2=value2'
			),
			// Check if a URL with Itemid and option is returned identically
			array(
				'url'      => 'index.php?option=com_test&Itemid=42&var1=value1',
				'preset'   => array(),
				'expected' => 'index.php?option=com_test&Itemid=42&var1=value1'
			),
			// Check if a URL with existing Itemid and no option is added the right option
			array(
				'url'      => 'index.php?Itemid=42&var1=value1',
				'preset'   => array(),
				'expected' => 'index.php?Itemid=42&var1=value1&option=com_test'
			),
			// Check if a URL with non-existing Itemid and no option is returned identically
			array(
				'url'      => 'index.php?Itemid=41&var1=value1',
				'preset'   => array(),
				'expected' => 'index.php?Itemid=41&var1=value1'
			),
			// Check if a URL with no Itemid and no option, but globally set option is added the option
			array(
				'url'      => 'index.php?var1=value1',
				'preset'   => array('option' => 'com_test'),
				'expected' => 'index.php?var1=value1&option=com_test'
			),
			// Check if a URL with no Itemid and no option, but globally set Itemid is added the Itemid
			array(
				'url'      => 'index.php?var1=value1',
				'preset'   => array('Itemid' => '42'),
				'expected' => 'index.php?var1=value1&Itemid=42'
			),
			// Check if a URL without an Itemid, but with an option set and a global Itemid available, which fits the option of the menu item gets the Itemid appended
			array(
				'url'      => 'index.php?var1=value&option=com_test',
				'preset'   => array('Itemid' => '42'),
				'expected' => 'index.php?var1=value&option=com_test&Itemid=42'
			),
			// Check if a URL without an Itemid, but with an option set and a global Itemid available, which does not fit the option of the menu item gets returned identically
			array(
				'url'      => 'index.php?var1=value&option=com_test3',
				'preset'   => array('Itemid' => '42'),
				'expected' => 'index.php?var1=value&option=com_test3'
			),
		);
	}

	/**
	 * Tests createUri() method
	 *
	 * @param   array  $url      valid inputs to the createUri() method
	 * @param   array  $preset   global Vars that should be merged into the URL
	 * @param   string $expected expected URI string
	 *
	 * @dataProvider casesCreateURI
	 *
	 * @return void
	 * @testdox      Create URI
	 * @since        3.4
	 */
	public function testCreateUri($url, $preset, $expected)
	{
		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);
		$object->setVars($preset);

		$createUriMethod = new ReflectionMethod('JRouterSite', 'createUri');
		$createUriMethod->setAccessible(true);

		$uri = $createUriMethod->invoke($object, $url);

		$this->assertInstanceOf('JUri', $uri);
		$this->assertEquals($expected, (string) $uri);
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
		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		/**
		 * Get the TestRouter and check if you get the
		 * same object instance the second time
		 */
		$router = $object->getComponentRouter('com_test');

		$this->assertInstanceOf('TestRouter', $router);
		$this->assertSame($router, $object->getComponentRouter('com_test'));

		/**
		 * Check if a proper router is automatically loaded
		 * by loading the router of com_search
		 */
		$this->assertInstanceOf('SearchRouter', $object->getComponentRouter('com_search'));

		/**
		 * Check if an instance of JComponentRouterLegacy
		 * is returned for non-existing routers
		 */
		$this->assertInstanceOf('JComponentRouterLegacy', $object->getComponentRouter('com_legacy'));
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
		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$router = new TestRouter;

		$this->assertTrue($object->setComponentRouter('com_test', $router));
		$this->assertSame($router, $object->getComponentRouter('com_test'));
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
		$object = new JRouterSite(
			array(),
			$this->getMockCmsApp(),
			TestMockMenu::create($this)
		);

		$this->assertFalse($object->setComponentRouter('com_test3', new stdClass));
	}
}
