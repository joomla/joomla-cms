<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationWebRouterBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.3
 */
class JApplicationWebRouterBaseTest extends TestCase
{
	/**
	 * @var    JApplicationWebRouterBase  The object to be tested.
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * @var    JInput  The JInput object to be inspected for route variables.
	 * @since  12.3
	 */
	private $_input;

	/**
	 * Provides test data for route parsing.
	 *
	 * @return  array
	 *
	 * @since   12.3
	 */
	public static function getParseRouteData()
	{
		// Route, Exception, ControllerName, InputData, MapSet
		return array(
			array('', false, 'home', array(), 1),
			array('articles/4', true, 'home', array(), 1),
			array('', false, 'index', array(), 2),
			array('login', false, 'login', array('_rawRoute' => 'login'), 2),
			array('articles', false, 'articles', array('_rawRoute' => 'articles'), 2),
			array('articles/4', false, 'article', array('article_id' => 4, '_rawRoute' => 'articles/4'), 2),
			array('articles/4/crap', true, '', array(), 2),
			array('test', true, '', array(), 2),
			array('test/foo', true, '', array(), 2),
			array('test/foo/path', true, '', array(), 2),
			array('test/foo/path/bar', false, 'test', array('seg1' => 'foo', 'seg2' => 'bar', '_rawRoute' => 'test/foo/path/bar'), 2),
			array('content/article-1/*', false, 'content', array('_rawRoute' => 'content/article-1/*'), 2),
			array('content/cat-1/article-1', false,
				'article', array('category' => 'cat-1', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/article-1'), 2),
			array('content/cat-1/cat-2/article-1', false,
				'article', array('category' => 'cat-1/cat-2', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/cat-2/article-1'), 2),
			array('content/cat-1/cat-2/cat-3/article-1', false,
				'article', array('category' => 'cat-1/cat-2/cat-3', 'article' => 'article-1', '_rawRoute' => 'content/cat-1/cat-2/cat-3/article-1'), 2)
		);
	}

	/**
	 * Tests the addMap method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouterBase::addMap
	 * @since   12.3
	 */
	public function testAddMap()
	{
		$this->assertAttributeEmpty('maps', $this->_instance);
		$this->_instance->addMap('foo', 'MyApplicationFoo');
		$this->assertAttributeEquals(
			array(
				array(
					'regex' => chr(1) . '^foo$' . chr(1),
					'vars' => array(),
					'controller' => 'MyApplicationFoo'
				)
			),
			'maps',
			$this->_instance
		);
	}

	/**
	 * Tests the addMaps method.
	 *
	 * @return  void
	 *
	 * @covers  JApplicationWebRouterBase::addMaps
	 * @since   12.3
	 */
	public function testAddMaps()
	{
		$maps = array(
			'login' => 'login',
			'logout' => 'logout',
			'requests' => 'requests',
			'requests/:request_id' => 'request'
		);

		$rules = array(
			array(
				'regex' => chr(1) . '^login$' . chr(1),
				'vars' => array(),
				'controller' => 'login'
			),
			array(
				'regex' => chr(1) . '^logout$' . chr(1),
				'vars' => array(),
				'controller' => 'logout'
			),
			array(
				'regex' => chr(1) . '^requests$' . chr(1),
				'vars' => array(),
				'controller' => 'requests'
			),
			array(
				'regex' => chr(1) . '^requests/([^/]*)$' . chr(1),
				'vars' => array('request_id'),
				'controller' => 'request'
			)
		);

		$this->assertAttributeEmpty('maps', $this->_instance);
		$this->_instance->addMaps($maps);
		$this->assertAttributeEquals($rules, 'maps', $this->_instance);
	}

	/**
	 * Tests the JApplicationWebRouterBase::parseRoute method.
	 *
	 * @param   string   $r  The route to parse.
	 * @param   boolean  $e  True if an exception is expected.
	 * @param   string   $c  The expected controller name.
	 * @param   array    $i  The expected input object data.
	 * @param   integer  $m  The map set to use for setting up the router.
	 *
	 * @return  void
	 *
	 * @covers       JApplicationWebRouterBase::parseRoute
	 * @dataProvider getParseRouteData
	 * @since        12.3
	 */
	public function testParseRoute($r, $e, $c, $i, $m)
	{
		// Setup the router maps.
		$mapSetup = 'setMaps' . $m;
		$this->$mapSetup();

		// If we should expect an exception set that up.
		if ($e)
		{
			$this->setExpectedException('InvalidArgumentException');
		}

		// Execute the route parsing.
		$actual = TestReflection::invoke($this->_instance, 'parseRoute', $r);

		// Test the assertions.
		$this->assertEquals($c, $actual, 'Incorrect controller name found.');
		$this->assertAttributeEquals($i, 'data', $this->_input, 'The input data is incorrect.');
	}

	/**
	 * Setup the router maps to option 1.
	 *
	 * This has no routes but has a default controller for the home page.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setMaps1()
	{
		$this->_instance->addMaps(array());
		$this->_instance->setDefaultController('home');
	}

	/**
	 * Setup the router maps to option 2.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setMaps2()
	{
		$this->_instance->addMaps(
			array(
				'login' => 'login',
				'logout' => 'logout',
				'articles' => 'articles',
				'articles/:article_id' => 'article',
				'test/:seg1/path/:seg2' => 'test',
				'content/:/\*' => 'content',
				'content/*category/:article' => 'article'
			)
		);
		$this->_instance->setDefaultController('index');
	}

	/**
	 * Prepares the environment before running a test.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function setUp()
	{
		parent::setUp();

		// Construct the clean JInput object.
		$array = array();
		$this->_input = new JInput($array);

		$this->_instance = new JApplicationWebRouterBase($this->getMockWeb(), $this->_input);
	}

	/**
	 * Cleans up the environment after running a test.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	protected function tearDown()
	{
		$this->_instance = null;

		parent::tearDown();
	}
}
