<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JComponentRouterRulesMenuInspector.php';
require_once __DIR__ . '/stubs/MockJComponentRouterRulesMenuMenuObject.php';
require_once __DIR__ . '/../stubs/JComponentRouterViewInspector.php';

/**
 * Test class for JComponentRouterRulesMenu.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.5
 */
class JComponentRouterRulesMenuTest extends TestCaseDatabase {

	/**
	 * Object under test
	 *
	 * @var    JComponentRouterRulesMenu
	 * @since  3.5
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function setUp()
	{
		parent::setUp();

		// Getting categories relies on the user access which relies on the session.
		$this->saveFactoryState();
		JFactory::$session = $this->getMockSession();

		$app = $this->getMockCmsApp();
		JFactory::$application = $app;
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
		$router->menu = new MockJComponentRouterRulesMenuMenuObject();

		$this->object = new JComponentRouterRulesMenuInspector($router);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.7.0
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.5
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * Tests the __construct() method
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('JComponentRouterRulesMenu', $this->object);
		$this->assertInstanceOf('JComponentRouterView', $this->object->get('router'));
		$this->assertEquals(array(
			'*' => array(
				'featured' => '47',
				'categories' => array(14 => '48'),
				'category' => array (20 => '49'))
			), $this->object->get('lookup'));
	}

	/**
	 * Cases for testPreprocess
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function casesPreprocess()
	{
		$cases   = array();
		
		// Check direct link to a simple view
		$cases[] = array(array('option' => 'com_content', 'view' => 'featured'),
			array('option' => 'com_content', 'view' => 'featured', 'Itemid' => '47'));

		// Check direct link to a simple view with a language
		$cases[] = array(array('option' => 'com_content', 'view' => 'featured', 'lang' => 'en-GB'),
			array('option' => 'com_content', 'view' => 'featured', 'lang' => 'en-GB', 'Itemid' => '51'));

		// Check direct link to a view with a key
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '14'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '14', 'Itemid' => '48'));

		// Check direct link to a view with a key with a language
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '14', 'lang' => 'en-GB'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '14', 'lang' => 'en-GB', 'Itemid' => '50'));

		// Check indirect link to a nested view with a key
		$cases[] = array(array('option' => 'com_content', 'view' => 'category', 'id' => '22'),
			array('option' => 'com_content', 'view' => 'category', 'id' => '22', 'Itemid' => '49'));

		// Check indirect link to a nested view with a key and a language
		$cases[] = array(array('option' => 'com_content', 'view' => 'category', 'id' => '22', 'lang' => 'en-GB'),
			array('option' => 'com_content', 'view' => 'category', 'id' => '22', 'lang' => 'en-GB', 'Itemid' => '49'));

		// Check indirect link to a single view behind a nested view with a key
		$cases[] = array(array('option' => 'com_content', 'view' => 'article', 'id' => '42', 'catid' => '22'),
			array('option' => 'com_content', 'view' => 'article', 'id' => '42', 'catid' => '22', 'Itemid' => '49'));

		// Check indirect link to a single view behind a nested view with a key and language
		$cases[] = array(array('option' => 'com_content', 'view' => 'article', 'id' => '42', 'catid' => '22', 'lang' => 'en-GB'),
			array('option' => 'com_content', 'view' => 'article', 'id' => '42', 'catid' => '22', 'lang' => 'en-GB', 'Itemid' => '49'));

		// Check non-existing menu link
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '42'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '42', 'Itemid' => '49'));

		// Check indirect link to a single view behind a nested view with a key and language
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '42', 'lang' => 'en-GB'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '42', 'lang' => 'en-GB', 'Itemid' => '49'));

		// Check if a query with existing Itemid that is not the current active menu-item is not touched
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '42', 'Itemid' => '99'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '42', 'Itemid' => '99'));

		// Check if a query with existing Itemid that is the current active menu-item is correctly searched
		$cases[] = array(array('option' => 'com_content', 'view' => 'categories', 'id' => '14', 'Itemid' => '49'),
			array('option' => 'com_content', 'view' => 'categories', 'id' => '14', 'Itemid' => '48'));

		return $cases;
	}

	/**
	 * Tests the preprocess() method
	 *
	 * @return  void
	 *
	 * @dataProvider  casesPreprocess
	 * @since   3.5
	 */
	public function testPreprocess($input, $result)
	{
		$this->saveFactoryState();

		$this->object->preprocess($input);
		$this->assertEquals($result, $input);

		$this->restoreFactoryState();
	}

	/**
	 * Tests the preprocess() method
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function testPreprocessLanguage()
	{
		$this->saveFactoryState();

		// Test if the default Itemid is used if everything else fails
		$router = $this->object->get('router');
		$router->menu->active = null;
		$query = array();
		$this->object->preprocess($query);
		$this->assertEquals(array('Itemid' => '47'), $query);

		// If we inject an item id and we have no active menu item we should get the injected item id
		$query = array('Itemid' => '50');
		$this->object->preprocess($query);
		$this->assertEquals(array('Itemid' => '50'), $query);

		// Test if the correct default item is used based on the language
		$query = array('lang' => 'en-GB');
		$this->object->preprocess($query);
		$this->assertEquals(array('lang' => 'en-GB', 'Itemid' => '51'), $query);

		$this->restoreFactoryState();
	}

	/**
	 * Tests the buildLookup() method
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function testBuildLookup()
	{
		$this->assertEquals(array(
			'*' => array(
				'featured' => '47',
				'categories' => array(14 => '48'),
				'category' => array (20 => '49'))
			), $this->object->get('lookup'));
		
		$this->object->runBuildLookUp('en-GB');
		$this->assertEquals(array(
			'*' => array(
				'featured' => '47',
				'categories' => array(14 => '48'),
				'category' => array (20 => '49')),
			'en-GB' => array(
				'featured' => '51',
				'categories' => array(14 => '50'),
				'category' => array (20 => '49'))
			), $this->object->get('lookup'));
	}
}
