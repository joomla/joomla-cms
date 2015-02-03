<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JRouterSiteInspector.php';

/**
 * Test class for JRouterSite.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.0
 */
class JRouterSiteTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JRouter
	 * @since  3.4
	 */
	protected $object;

	/**
	 * Backup of the $_SERVER variable
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $server;

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

		$this->server = $_SERVER;

		$_SERVER['HTTP_HOST']       = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';
		$_SERVER['REQUEST_URI']     = '/index.php';
		$_SERVER['SCRIPT_NAME']     = '/index.php';

		JUri::reset();

		$options      = array();
		$app          = $this->getMockCmsApp();
		$menu         = TestMockMenu::create($this);
		$this->object = new JRouterSiteInspector($options, $app, $menu);
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

		parent::tearDown();
	}

	/**
	 * Tests the __construct() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testConstruct()
	{
		$options = array();
		$app     = $this->getMockCmsApp();
		$menu    = TestMockMenu::create($this);
		$object  = new JRouterSiteInspector($options, $app, $menu);
		$this->assertInstanceOf('JRouterSite', $object);

		$options = array();
		$app     = $this->getMockCmsApp();
		$object  = new JRouterSiteInspector($options, $app);
		$this->assertInstanceOf('JRouterSite', $object);
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
			'HTTP_HOST'   => '',
			'SCRIPT_NAME' => '',
			'SCRIPT_FILENAME' => JPATH_SITE . '/cli/deletefiles.php',
			'PHP_SELF'    => '',
			'REQUEST_URI' => ''
		);

		$cases   = array();
		$cases[] = array('', JROUTER_MODE_RAW, array(), $server1, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), '');

		$cases[] = array('/index.php?var1=value1', JROUTER_MODE_RAW, array(), $server1, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'index.php?var1=value1');
		$cases[] = array('index.php?var1=value1', JROUTER_MODE_RAW, array(), $server1, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'index.php?var1=value1');

		$cases[] = array('/joomla/blog/test.json', JROUTER_MODE_SEF, array(array('sef_suffix', null, '1')), $server1, array('format' => 'json', 'option' => 'com_test3', 'Itemid' => '45'), 'joomla/blog/test.json');
		$cases[] = array('/joomla/blog/test.json/', JROUTER_MODE_SEF, array(array('sef_suffix', null, '1')), $server1, array('option' => 'com_test3', 'Itemid' => '45'), 'joomla/blog/test.json');

		$cases[] = array('/joomla/blog/test%202', JROUTER_MODE_RAW, array(), $server1, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'joomla/blog/test 2');
		$cases[] = array('/joomla/blog/test', JROUTER_MODE_RAW, array(), $server2, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'blog/test');
		$cases[] = array('/joomla/blog/te%20st', JROUTER_MODE_RAW, array(), $server2, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'blog/te st');
		$cases[] = array('/otherfolder/blog/test', JROUTER_MODE_RAW, array(), $server2, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), 'older/blog/test');

		$cases[] = array('/cli/deletefiles.php?var1=value1', JROUTER_MODE_RAW, array(), $server3, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), '?var1=value1');

		return $cases;
	}

	/**
	 * Tests the parse method
	 *
	 * @param   string   $url        An associative array with variables
	 * @param   integer  $mode       JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array    $map        An associative array with app config vars
	 * @param   array    $server     An associative array with $_SERVER vars
	 * @param   array    $expected   Expected vars
	 * @param   string   $expected2  Expected URI string
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParse
	 * @since         3.4
	 */
	public function testParse($url, $mode, $map, $server, $expected, $expected2)
	{
		$_SERVER = array_merge($_SERVER, $server);
		$app     = $this->object->getApp();
		$app->expects($this->any())->method('get')->will($this->returnValueMap($map));
		$this->object->setApp($app);
		$this->object->setMode($mode);

		$uri  = new JUri($url);
		$vars = $this->object->parse($uri);

		$this->assertEquals($expected, $vars);
		$this->assertEquals($expected2, $uri->toString());
	}

	/**
	 * Tests the parse methods redirect
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testParseRedirect()
	{
		$uri = new JUri('http://www.example.com/index.php');
		$app = $this->object->getApp();
		$app->expects($this->any())->method('get')->will($this->returnValue(2));
		$app->expects($this->once())->method('redirect');
		$this->object->setApp($app);

		$this->object->parse($uri);
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

		$cases = array();

		$cases[] = array('', JROUTER_MODE_RAW, array(), array(), $server1, '/');

		$cases[] = array('blog/test', JROUTER_MODE_RAW, array(), array(), $server1, '/blog/test');

		$cases[] = array('', JROUTER_MODE_RAW, array(), array(), $server2, '/joomla/');

		$cases[] = array('blog/test', JROUTER_MODE_RAW, array(), array(), $server2, '/joomla/blog/test');

		$cases[] = array('', JROUTER_MODE_SEF, array(), array(), $server1, '/');

		$cases[] = array('blog/test', JROUTER_MODE_SEF, array(), array(), $server1, '/blog/test');

		$cases[] = array('', JROUTER_MODE_SEF, array(), array(), $server2, '/joomla/');

		$cases[] = array('blog/test', JROUTER_MODE_SEF, array(), array(), $server2, '/joomla/blog/test');

		$cases[] = array('index.php', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1)
			),
			$server2, '/joomla/');

		$cases[] = array('index.php/blog/test', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1)
			),
			$server2, '/joomla/blog/test');

		$cases[] = array('index.php', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1)
			),
			$server1, '/');

		$cases[] = array('index.php/blog/test', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1)
			),
			$server1, '/blog/test');

		$cases[] = array('index.php?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_suffix', null, 1)
			),
			$server2, '/joomla/index.php?format=json');

		$cases[] = array('index.php/blog/test?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_suffix', null, 1)
			),
			$server2, '/joomla/index.php/blog/test.json');

		$cases[] = array('index.php?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_suffix', null, 1)
			),
			$server1, '/index.php?format=json');

		$cases[] = array('index.php/blog/test?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_suffix', null, 1)
			),
			$server1, '/index.php/blog/test.json');

		$cases[] = array('index.php?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1),
				array('sef_suffix', null, 1)
			),
			$server2, '/joomla/?format=json');

		$cases[] = array('index.php/blog/test?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1),
				array('sef_suffix', null, 1)
			),
			$server2, '/joomla/blog/test.json');

		$cases[] = array('index.php?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1),
				array('sef_suffix', null, 1)
			),
			$server1, '/?format=json');

		$cases[] = array('index.php/blog/test?format=json', JROUTER_MODE_SEF, array(),
			array(
				array('sef_rewrite', null, 1),
				array('sef_suffix', null, 1)
			),
			$server1, '/blog/test.json');

		return $cases;
	}

	/**
	 * testBuild().
	 *
	 * @param   string   $url       The URL
	 * @param   integer  $mode      JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array    $vars      An associative array with global variables
	 * @param   array    $map       Valuemap for JApplication::get() Mock
	 * @param   array    $server    Values for $_SERVER
	 * @param   array    $expected  Expected value
	 *
	 * @dataProvider casesBuild
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testBuild($url, $mode, $vars, $map, $server, $expected)
	{
		//Set $_SERVER variable
		$_SERVER = array_merge($_SERVER, $server);

		$this->object->setMode($mode);
		$app = $this->object->getApp();
		$app->expects($this->any())->method('get')->will($this->returnValueMap($map));
		$this->object->setApp($app);

		$uri = $this->object->build($url);

		// Check the expected values
		$this->assertEquals($expected, $uri->toString());

		// Check that caching works
		$juri = $this->object->build($url);
		$this->assertEquals($uri, $juri);
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
		$cases = array();
		$cases[] = array('', true, array(), array());
		$cases[] = array('', false, array('option' => 'com_test3', 'view' => 'test3', 'Itemid' => '45'), array());

		$cases[] = array('index.php?option=com_test&Itemid=42&testvar=testvalue', true, array(), array('option' => 'com_test', 'Itemid' => '42', 'testvar' => 'testvalue'));
		$cases[] = array('index.php?Itemid=42', true, array('option' => 'com_test', 'view' => 'test'), array('Itemid' => 42));

		return $cases;
	}

	/**
	 * Tests the parse method
	 *
	 * @param   string   $url                 An associative array with variables
	 * @param   integer  $menubool            JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array    $expectedVars        An associative array with $_SERVER vars
	 * @param   array    $expectedGlobalVars  An associative array with $_SERVER vars
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseRawRoute
	 * @since         3.4
	 */
	public function testParseRawRoute($url, $menubool, $expectedVars, $expectedGlobalVars)
	{
		$uri = new JUri($url);

		if (isset($expectedGlobalVars['Itemid']))
		{
			$app = $this->object->getApp();
			$app->input->set('Itemid', $expectedGlobalVars['Itemid']);
			$this->object->setApp($app);
		}

		if ($menubool)
		{
			$menu = TestMockMenu::create($this, false);
			$menu->expects($this->any())->method('getDefault')->will($this->returnValue(null));
			$this->object->setMenu($menu);
		}

		$vars = $this->object->runParseRawRoute($uri);

		$this->assertEquals($expectedVars, $vars);

		$this->assertEquals($expectedGlobalVars, $this->object->getVars());
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
		$cases = array();

		// Empty URLs without a default menu item return nothing
		$cases[] = array('', true, array(), array(), array());

		// Absolute URLs to the domain of the site
		$cases[] = array('/test/path', true, array(), array(), array());
		$cases[] = array('/test/path?testvar=testvalue', true, array(), array('testvar' => 'testvalue'), array('testvar' => 'testvalue'));
		$cases[] = array('?testvar=testvalue', true, array(), array('testvar' => 'testvalue'), array());

		$cases[] = array('/test/path.json', true, array(), array(), array());
		$cases[] = array('/test/path.json?testvar=testvalue', true, array(), array('testvar' => 'testvalue'), array('testvar' => 'testvalue'));

		$cases[] = array('/test/path.json', true, array(array('sef_suffix', null, '1')), array(), array());
		$cases[] = array('/test/path.json?testvar=testvalue', true, array(array('sef_suffix', null, '1')), array('testvar' => 'testvalue'), array('testvar' => 'testvalue'));

		$cases[] = array('', false, array(), array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'), array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'));
		$cases[] = array('/test/path', false, array(), array(), array('Itemid' => '45', 'option' => 'com_test3'));
		$cases[] = array('/test/path?testvar=testvalue', false, array(), array(), array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3'));
		$cases[] = array('?testvar=testvalue', false, array(), array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'), array('Itemid' => '45', 'option' => 'com_test3', 'view' => 'test3'));

		$cases[] = array('/test/path.json', false, array(), array(), array('Itemid' => '45', 'option' => 'com_test3'));
		$cases[] = array('/test/path.json?testvar=testvalue', false, array(), array(), array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3'));

		$cases[] = array('/test/path.json', false, array(array('sef_suffix', null, '1')), array(), array('Itemid' => '45', 'option' => 'com_test3'));
		$cases[] = array('/test/path.json?testvar=testvalue', false, array(array('sef_suffix', null, '1')), array(), array('testvar' => 'testvalue', 'Itemid' => '45', 'option' => 'com_test3'));

		// Non-SEF URLs
		$cases[] = array('?option=com_test', true, array(), array(), array('option' => 'com_test', 'Itemid' => NULL));
		$cases[] = array('?Itemid=42', true, array(), array(), array('Itemid' => NULL));
		$cases[] = array('?Itemid=42&option=com_test', true, array(), array(), array('option' => 'com_test', 'Itemid' => NULL));
		$cases[] = array('?option=com_test', false, array(), array(), array('option' => 'com_test', 'Itemid' => NULL));

		// 20
		$cases[] = array('?Itemid=42', false, array(), array(), array('Itemid' => NULL));
		$cases[] = array('?Itemid=42&option=com_test', false, array(), array(), array('option' => 'com_test', 'Itemid' => NULL));

		// URLs with /component/something
		$cases[] = array('component/test', true, array(), array('option' => 'com_test', 'Itemid' => NULL), array('option' => 'com_test', 'Itemid' => NULL));
		$cases[] = array('component/test', false, array(), array('option' => 'com_test', 'Itemid' => NULL), array('option' => 'com_test', 'Itemid' => NULL));
		$cases[] = array('component/test2/something', true, array(), array('testvar' => 'testvalue'), array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => NULL));
		$cases[] = array('component/test2/something', false, array(), array('testvar' => 'testvalue'), array('option' => 'com_test2', 'Itemid' => NULL, 'testvar' => 'testvalue'));

		// Parse actual menu items
		$cases[] = array('test2/sub-menu', true, array(), array('option' => 'com_test2', 'Itemid' => 44), array('option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu', false, array(), array('option' => 'com_test2', 'Itemid' => 44), array('option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu/something', true, array(), array('testvar' => 'testvalue'), array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu/something', false, array(), array('testvar' => 'testvalue'), array('option' => 'com_test2', 'Itemid' => 44, 'testvar' => 'testvalue'));

		$cases[] = array('test2/sub-menu', true, array('languagefilter' => true), array('option' => 'com_test2', 'Itemid' => 44), array('option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu', false, array('languagefilter' => true), array('option' => 'com_test2', 'Itemid' => 44), array('option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu/something', true, array('languagefilter' => true), array('testvar' => 'testvalue'), array('testvar' => 'testvalue', 'option' => 'com_test2', 'Itemid' => 44));
		$cases[] = array('test2/sub-menu/something', false, array('languagefilter' => true), array('testvar' => 'testvalue'), array('option' => 'com_test2', 'Itemid' => 44, 'testvar' => 'testvalue'));

		$cases[] = array('english-test', false, array('languagefilter' => true), array('option' => 'com_test', 'view' => 'test2'), array('option' => 'com_test', 'Itemid' => '47'), 47);

		return $cases;
	}

	/**
	 * Tests the parseSefRoute method
	 *
	 * @param   string   $url              An associative array with variables
	 * @param   integer  $menubool         JROUTER_MODE_RAW or JROUTER_MODE_SEF
	 * @param   array    $appConfig        An associative array with app config vars
	 * @param   array    $expected         An associative array with $_SERVER vars
	 * @param   array    $expectedGlobals  An associative array with $_SERVER vars
	 * @param   boolean  $activeMenu       Flag if the item is the active menu
	 *
	 * @return  void
	 *
	 * @dataProvider  casesParseSefRoute
	 * @since         3.4
	 */
	public function testParseSefRoute($url, $menubool, $appConfig, $expected, $expectedGlobals, $activeMenu = false)
	{
		$uri = new JUri($url);
		$app = $this->object->getApp();

		if (isset($expected['Itemid']))
		{
			$app->input->set('Itemid', $expected['Itemid']);
		}

		if (isset($appConfig['languagefilter']))
		{
			$app->expects($this->any())->method('getLanguageFilter')->will($this->returnValue(true));
			unset($appConfig['languagefilter']);
		}
		$app->expects($this->any())->method('get')->will($this->returnValueMap($appConfig));
		$this->object->setApp($app);

		if ($menubool)
		{
			$menu = TestMockMenu::create($this, false, $activeMenu);
			$menu->expects($this->any())->method('getDefault')->will($this->returnValue(null));
			$this->object->setMenu($menu);
		}
		else
		{
			$menu = TestMockMenu::create($this, true, $activeMenu);
			$this->object->setMenu($menu);
		}

		// The method should return an array of variables
		$vars = $this->object->runParseSefRoute($uri);
		$this->assertEquals($expected, $vars);

		$this->assertEquals($expectedGlobals, $this->object->getVars(), 'global vars');
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testBuildRawRoute()
	{
		$uri = new JUri('index.php');

		// Test if a URL without an option is returned identical
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php', $uri->toString());

		// Test if a component routers preprocess method is executed
		$uri->setVar('option', 'com_test');
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php?option=com_test&testvar=testvalue', $uri->toString());

		// Test if a broken option is properly sanitised to get the right router
		$uri->setVar('option', 'com_ te?st');
		$uri->delVar('testvar');
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php?option=com_ te?st&testvar=testvalue', $uri->toString());

		// Test if a legacy component routers preprocess method is executed
		$uri->setVar('option', 'com_test3');
		$uri->delVar('testvar');
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php?option=com_test3', $uri->toString());
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
		$cases = array();

		// Check empty URLs are returned identically
		$cases[] = array('', '');

		// Check if URLs without an option are returned identically
		$cases[] = array('index.php?var1=value1', 'index.php?var1=value1');

		// Check if the menu item is properly prepended
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=42', 'index.php/test?var1=value1');

		// Check if a non existing menu item is correctly ignored
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=41', 'index.php/component/test/?var1=value1&Itemid=41');

		// Check if a menu item with a parent is properly prepended
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=46', 'index.php/test/sub-menu?var1=value1');

		// Component router build: Check if the menu item is properly prepended
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=43', 'index.php/test2/router-test/another-segment?var1=value1');

		// Component router build: Check if a non existing menu item is correctly ignored
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=41', 'index.php/component/test2/router-test/another-segment?var1=value1&Itemid=41');

		// Component router build: Check if a menu item with a parent is properly prepended
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=44', 'index.php/test2/sub-menu/router-test/another-segment?var1=value1');

		// Check if a home menu item is treated properly
		$cases[] = array('index.php?Itemid=45&option=com_test3', 'index.php/component/test3/?Itemid=45');

		// Check if a home menu item is treated properly
		$cases[] = array('index.php?Itemid=45&option=com_test3&testvar=testvalue', 'index.php/component/test3/?testvar=testvalue&Itemid=45');

		return $cases;
	}

	/**
	 * testBuildSefRoute().
	 *
	 * @param   string  $url        Input URL
	 * @param   string  $expected   Expected return value
	 *
	 * @dataProvider casesBuildSefRoute
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testBuildSefRoute($url, $expected)
	{
		$uri = new JUri($url);
		$this->object->runBuildSefRoute($uri);
		$this->assertEquals($expected, $uri->toString());
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
		$url      = 'index.php?start=42';
		$expected = 'index.php';
		$uri      = new JUri($url);
		$this->object->setMode(JROUTER_MODE_SEF);
		$vars = $this->object->runProcessParseRules($uri);
		$this->assertEquals($uri->toString(), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
		$this->assertEquals($vars, array('limitstart' => '42'), __METHOD__ . ':' . __LINE__ . ': value is not expected');
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
		$cases = array();

		// Check if an empty URL is returned as an empty URL
		$cases[] = array('', JROUTER_MODE_RAW, '');

		/**
		 * Check if a URL with an Itemid and another query parameter
		 * is replaced with the query of the menu item plus the Itemid
		 * when mode is not SEF
		 */
		$cases[] = array('index.php?Itemid=42&test=true', JROUTER_MODE_RAW, 'index.php?option=com_test&view=test&Itemid=42');

		/**
		 * Check if a URL with an Itemid and another query parameter
		 * is returned identical when mode is SEF
		 */
		$cases[] = array('index.php?Itemid=42&test=true', JROUTER_MODE_SEF, 'index.php?Itemid=42&test=true');

		/**
		 * Check if a URL with a path and limitstart gets the limitstart
		 * parameter converted to start when mode is SEF
		 */
		$cases[] = array('test?limitstart=42', JROUTER_MODE_SEF, 'test?start=42');

		return $cases;
	}

	/**
	 * testProcessBuildRules().
	 *
	 * @param   string  $url        Input URL
	 * @param   array   $functions  Callback to execute
	 * @param   string  $expected   Expected return value
	 *
	 * @dataProvider casesProcessBuildRules
	 *
	 * @return void
	 *
	 * @since 3.4
	 */
	public function testProcessBuildRules($url, $mode, $expected)
	{
		$uri = new JUri($url);
		$this->object->setMode($mode);
		$this->object->runProcessBuildRules($uri);
		$this->assertEquals($uri->toString(), $expected, __METHOD__ . ':' . __LINE__ . ': value is not expected');
	}

	/**
	 * Cases for testCreateURI
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function casesCreateURI()
	{
		$cases = array();

		// Check if a rather non-URL is returned identical
		$cases[] = array('index.php?var1=value&var2=value2', array(), 'index.php?var1=value&var2=value2');

		// Check if a URL with Itemid and option is returned identically
		$cases[] = array('index.php?option=com_test&Itemid=42&var1=value1', array(), 'index.php?option=com_test&Itemid=42&var1=value1');

		// Check if a URL with existing Itemid and no option is added the right option
		$cases[] = array('index.php?Itemid=42&var1=value1', array(), 'index.php?Itemid=42&var1=value1&option=com_test');

		// Check if a URL with non-existing Itemid and no option is returned identically
		$cases[] = array('index.php?Itemid=41&var1=value1', array(), 'index.php?Itemid=41&var1=value1');

		/**
		 * Check if a URL with no Itemid and no option,
		 * but globally set option is added the option
		 */
		$cases[] = array('index.php?var1=value1', array('option' => 'com_test'), 'index.php?var1=value1&option=com_test');

		/**
		 * Check if a URL with no Itemid and no option,
		 * but globally set Itemid is added the Itemid
		 */
		$cases[] = array('index.php?var1=value1', array('Itemid' => '42'), 'index.php?var1=value1&Itemid=42');

		/**
		 * Check if a URL without an Itemid, but with an option set
		 * and a global Itemid available, which fits the option of
		 * the menu item gets the Itemid appended
		 */
		$cases[] = array('index.php?var1=value&option=com_test', array('Itemid' => '42'), 'index.php?var1=value&option=com_test&Itemid=42');

		/**
		 * Check if a URL without an Itemid, but with an option set
		 * and a global Itemid available, which does not fit the
		 * option of the menu item gets returned identically
		 */
		$cases[] = array('index.php?var1=value&option=com_test3', array('Itemid' => '42'), 'index.php?var1=value&option=com_test3');

		return $cases;
	}

	/**
	 * Tests createURI() method
	 *
	 * @param   array   $url         valid inputs to the createURI() method
	 * @param   array   $globalVars  global Vars that should be merged into the URL
	 * @param   string  $expected    expected URI string
	 *
	 * @dataProvider casesCreateURI
	 *
	 * @return void
	 *
	 * @since  3.4
	 */
	public function testCreateURI($url, $globalVars, $expected)
	{
		$this->object->setVars($globalVars, false);
		$juri = $this->object->runCreateURI($url);

		$this->assertTrue(is_a($juri, 'JUri'));
		$this->assertEquals($juri->toString(), $expected);
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
	public function testSetComponentRouter()
	{
		// Check if a router that implements JComponentRouterInterface gets accepted
		$router = new TestRouter;
		$this->assertEquals($this->object->setComponentRouter('com_test', $router), true);
		$this->assertSame($this->object->getComponentRouter('com_test'), $router);

		// Check if a false router is correctly rejected
		$this->assertFalse($this->object->setComponentRouter('com_test3', new stdClass));
	}
}
