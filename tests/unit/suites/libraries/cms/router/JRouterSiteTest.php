<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JRouterSiteInspector.php';

/**
 * Test class for JRouterSite.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Router
 * @since       3.0
 */
class JRouterSiteTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JRouter
	 * @since  3.4
	 */
	protected $object;

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

		$options = array();
		$app = TestMockApplicationCms::create($this);
		$menu = TestMockMenu::create($this);
		$this->object = new JRouterSiteInspector($options, $app, $menu);
	}
	
	/**
	 * Tests the __construct() method
	 *
	 * @return  void
	 *
	 * @since         3.4
	 */
	public function testConstruct()
	{
		$options = array();
		$app = TestMockApplication::create($this);
		$menu = TestMockMenu::create($this);
		$object = new JRouterSiteInspector($options, $app, $menu);
		$this->assertInstanceOf('JRouterSite', $object);
		
		$options = array();
		$app = TestMockApplication::create($this);
		$object = new JRouterSiteInspector($options, $app);
		$this->assertInstanceOf('JRouterSite', $object);
		
		/**
		 * This test is commented until the PR 3758 is accepted to fix JApplication 
		 
		$clear = false;
		if(!isset($_SERVER['HTTP_HOST'])) {
			$_SERVER['HTTP_HOST'] = 'http://localhost';
			$clear = true;
		}
		JApplicationCms::getInstance('site', new JRegistry(array('session' => false)));
		$options = array();
		$menu = TestMockMenu::create($this);
		$object = new JRouterSiteInspector($options, null, $menu);
		$this->assertInstanceOf('JRouterSite', $object);
		
		if($clear) {
			unset($_SERVER['HTTP_HOST']);
		}
		 */
	}
	
	/**
	 * @todo   Implement testParse().
	 */
	public function testParse()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testBuild().
	 */
	public function testBuild()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.'
		);
	}

	public function testParseRawRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	public function testParseSefRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Tests the buildRawRoute() method
	 *
	 * @return  void
	 *
	 * @since         3.4
	 */
	public function testBuildRawRoute()
	{
		$uri = new JUri('index.php');
		
		/**
		 * Test if a URL without an option is returned identical
		 */
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php', $uri->toString());
		
		/**
		 * Test if a component routers preprocess method is executed
		 */
		$uri->setVar('option', 'com_test');
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php?option=com_test&testvar=testvalue', $uri->toString());
		
		/**
		 * Test if a broken option is properly sanitised to get the right router
		 */
		$uri->setVar('option', 'com_ te?st');
		$uri->delVar('testvar');
		$this->object->runBuildRawRoute($uri);
		$this->assertEquals('index.php?option=com_ te?st&testvar=testvalue', $uri->toString());
		
		/**
		 * Test if a legacy component routers preprocess method is executed
		 */
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
		
		//Check empty URLs are returned identically
		$cases[] = array('', '');
		
		//Check if URLs without an option are returned identically
		$cases[] = array('index.php?var1=value1', 'index.php?var1=value1');
		
		//Check if URLs with an option are processed by the pre-process method
		$cases[] = array('index.php?option=com_test&var1=value1', 'index.php/component/test/?var1=value1&testvar=testvalue');
				
		//Check if URLs with a mangled option are processed by the pre-process method
		$cases[] = array('index.php?option=com_ Tes§t&var1=value1', 'index.php/component/ Tes§t/?var1=value1&testvar=testvalue');
		
		//Check if URLs with an option and some path are processed by the pre-process method and returned with the original path
		$cases[] = array('test-folder?option=com_test&var1=value1', 'test-folder/component/test/?var1=value1&testvar=testvalue');

		//Check if the menu item is properly prepended
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=42', 'index.php/test?var1=value1&testvar=testvalue');
		
		//Check if a non existing menu item is correctly ignored
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=41', 'index.php/component/test/?var1=value1&Itemid=41&testvar=testvalue');
		
		//Check if a menu item with a parent is properly prepended
		$cases[] = array('index.php?option=com_test&var1=value1&Itemid=46', 'index.php/test/sub-menu?var1=value1&testvar=testvalue');
		
		//Component router build: Check if URLs with an option and some path are processed by the pre-process method and returned with the original path
		$cases[] = array('test-folder?option=com_test2&var1=value1', 'test-folder/component/test2/router-test/another-segment?var1=value1');

		//Component router build: Check if the menu item is properly prepended
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=43', 'index.php/test2/router-test/another-segment?var1=value1');
		
		//Component router build: Check if a non existing menu item is correctly ignored
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=41', 'index.php/component/test2/router-test/another-segment?var1=value1&Itemid=41');
		
		//Component router build: Check if a menu item with a parent is properly prepended
		$cases[] = array('index.php?option=com_test2&var1=value1&Itemid=44', 'index.php/test2/sub-menu/router-test/another-segment?var1=value1');
		
		//Check if a home menu item is treated properly
		$cases[] = array('index.php?Itemid=45&option=com_test3', 'index.php/component/test3/?Itemid=45');
		
		//Check if a home menu item is treated properly
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
	 * @since         3.4
	 */
	public function testProcessParseRules()
	{
		$url = 'index.php?start=42';
		$expected = 'index.php';
		$uri = new JUri($url);
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
		
		/**
		 * Check if an empty URL is returned as an empty URL
		 */
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

		/**
		 * Check if a rather non-URL is returned identical
		 */
		$cases[] = array('index.php?var1=value&var2=value2', array(), 'index.php?var1=value&var2=value2');
		
		/**
		 * Check if a URL with Itemid and option is returned identically
		 */
		$cases[] = array('index.php?option=com_test&Itemid=42&var1=value1', array(), 'index.php?option=com_test&Itemid=42&var1=value1');
		
		/**
		 * Check if a URL with existing Itemid and no option is added the right option
		 */
		$cases[] = array('index.php?Itemid=42&var1=value1', array(), 'index.php?Itemid=42&var1=value1&option=com_test');
		
		/**
		 * Check if a URL with non-existing Itemid and no option is returned identically
		 */
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
	 * @since         3.4
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
	 * @since         3.4
	 */
	public function testSetComponentRouter()
	{
		/**
		 * Check if a router that implements JComponentRouterInterface 
		 * gets accepted
		 */
		$router = new TestRouter;
		$this->assertEquals($this->object->setComponentRouter('com_test', $router), true);
		$this->assertSame($this->object->getComponentRouter('com_test'), $router);
		
		/**
		 * Check if a false router is correctly rejected
		 */
		$this->assertFalse($this->object->setComponentRouter('com_test3', new stdClass));
	}
}
