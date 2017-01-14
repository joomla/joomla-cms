<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JComponentRouterRulesNomenuInspector.php';
require_once __DIR__ . '/../stubs/JComponentRouterViewInspector.php';

/**
 * Test class for JComponentRouterRulesMenu.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterRulesNomenuTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JComponentRouterRulesMenu
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

		$app = $this->getMockCmsApp();
		$router = new JComponentRouterViewInspector($app, $app->getMenu());
		$router->set('name', 'content');
		$categories = new JComponentRouterViewconfiguration('categories');
		$categories->setKey('id');
		$router->registerView($categories);
		$category = new JComponentRouterViewconfiguration('category');
		$category->setKey('id')->setParent($categories)->setNestable()->addLayout('blog');
		$router->registerView($category);
		$article = new JComponentRouterViewconfiguration('article');
		$article->setKey('id')->setParent($category, 'catid');
		$router->registerView($article);
		$archive = new JComponentRouterViewconfiguration('archive');
		$router->registerView($archive);
		$featured = new JComponentRouterViewconfiguration('featured');
		$router->registerView($featured);
		$form = new JComponentRouterViewconfiguration('form');
		$router->registerView($form);

		$this->object = new JComponentRouterRulesNomenuInspector($router);
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
		$this->assertInstanceOf('JComponentRouterRulesNomenu', $this->object);
		$this->assertInstanceOf('JComponentRouterView', $this->object->get('router'));
	}

	/**
	 * Tests the parse() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testParse()
	{
		// Check if a false view is properly rejected
		$segments = array('falseview');
		$vars = array('option' => 'com_content');
		$this->object->parse($segments, $vars);
		$this->assertEquals(array('falseview'), $segments);
		$this->assertEquals(array('option' => 'com_content'), $vars);

		// Check if a single view is properly parsed
		$segments = array('featured');
		$vars = array('option' => 'com_content');
		$this->object->parse($segments, $vars);
		$this->assertEquals(array(), $segments);
		$this->assertEquals(array('option' => 'com_content', 'view' => 'featured'), $vars);

		// Check if a view with ID is properly parsed
		$segments = array('category', '23-the-question');
		$vars = array('option' => 'com_content');
		$this->object->parse($segments, $vars);
		$this->assertEquals(array(), $segments);
		$this->assertEquals(array('option' => 'com_content', 'view' => 'category', 'id' => '23:the-question'), $vars);

		// Check if a view that normally has an ID but which is missing is properly parsed
		$segments = array('category');
		$vars = array('option' => 'com_content');
		$this->object->parse($segments, $vars);
		$this->assertEquals(array(), $segments);
		$this->assertEquals(array('option' => 'com_content', 'view' => 'category'), $vars);

		// Test if the rule is properly skipped when a menu item is set
		$router = $this->object->get('router');
		$router->menu->expects($this->any())
			->method('getActive')
			->will($this->returnValue(new stdClass));
		$segments = array('article', '42:the-answer');
		$vars = array('option' => 'com_content');
		$this->object->parse($segments, $vars);
		$this->assertEquals(array('article', '42:the-answer'), $segments);
		$this->assertEquals(array('option' => 'com_content'), $vars);
	}

	/**
	 * Tests the build() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function testBuild()
	{
		// Test if the rule is properly skipped if an Itemid is set
		$query = array('option' => 'com_test', 'view' => 'article', 'id' => '42:the-answer', 'Itemid' => '42');
		$segments = array();
		$this->object->build($query, $segments);
		$this->assertEquals(array('option' => 'com_test', 'view' => 'article', 'id' => '42:the-answer', 'Itemid' => '42'), $query);
		$this->assertEquals(array(), $segments);

		// Test if a false view is properly not treated
		$query = array('option' => 'com_content', 'view' => 'falseview', 'id' => '42:the-answer');
		$segments = array();
		$this->object->build($query, $segments);
		$this->assertEquals(array('option' => 'com_content', 'view' => 'falseview', 'id' => '42:the-answer'), $query);
		$this->assertEquals(array(), $segments);

		// Test if a single view without identifier is properly build
		$query = array('option' => 'com_content', 'view' => 'featured');
		$segments = array();
		$this->object->build($query, $segments);
		$this->assertEquals(array('option' => 'com_content'), $query);
		$this->assertEquals(array('featured'), $segments);

		// Test if a single view with identifier is properly build
		$query = array('option' => 'com_content', 'view' => 'article', 'id' => '42:the-answer');
		$segments = array();
		$this->object->build($query, $segments);
		$this->assertEquals(array('option' => 'com_content'), $query);
		$this->assertEquals(array('article', '42-the-answer'), $segments);
	}
}
