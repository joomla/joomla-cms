<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/../stubs/ComContentRouter.php';
require_once __DIR__ . '/stubs/MockJComponentRouterRulesMenuMenuObject.php';

/**
 * Test class for JComponentRouterRulesStandard.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.7.0
 */
class JComponentRouterRulesStandardTest extends TestCaseDatabase
{
	/**
	 * Object under test
	 *
	 * @var    JComponentRouterRulesStandard
	 * @since  3.7.0
	 */
	protected $object;

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.7.0
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');
		$dataSet->addTable('jos_content', JPATH_TEST_DATABASE . '/jos_content.csv');
		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');
		$dataSet->addTable('jos_menu', JPATH_TEST_DATABASE . '/jos_menu.csv');

		return $dataSet;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function setUp()
	{
		parent::setUp();
		$noIds = true;

		// The menu object relies on a session so mock it.
		$this->saveFactoryState();
		JFactory::$session = $this->getMockSession();

		$app = $this->getMockCmsApp();
		JFactory::$application = $app;

		$router = new ContentRouterStandardRuleOnly($app, new JMenuSite(array('app' => $app, 'language' => self::getMockLanguage())), $noIds);
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

		$this->object = new JComponentRouterRulesStandard($router);
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
	 * Tests the __construct() method
	 *
	 * @return  void
	 *
	 * @covers  JComponentRouterRulesStandard::__construct
	 * @since   3.7.0
	 */
	public function testConstruct()
	{
		$this->assertInstanceOf('JComponentRouterRulesStandard', $this->object);
		$this->assertInstanceOf('JComponentRouterView', TestReflection::getValue($this->object, 'router'));
	}

	/**
	 * Provides the data to test the build method.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function dataTestBuild()
	{
		return array(
			array(
				array(
					'option' => 'com_content',
					'view' => 'article',
					'catid' => 19,
					'id' => 8,
					'Itemid' => 260
				),
				array(
					'option' => 'com_content',
					'Itemid' => 260
				),
				array(
					0 => 'beginners'
				),
				'Error building a URL for an article with a parent category menu item'
			),
			array(
				array(
					'option' => 'com_content',
					'view' => 'article',
					'catid' => 73,
					'id' => 11,
					'Itemid' => 272
				),
				array(
					'option' => 'com_content',
					'Itemid' => 272
				),
				array(
					0 => 'park-site',
					1 => 'photo-gallery',
					2 => 'scenery',
					3 => 'cradle-mountain'
				),
				'Error building a URL for an article with multiple levels to it\'s category menu item'
			),
			array(
				array(
					'option' => 'com_content',
					'view' => 'category',
					'id' => 19,
					'Itemid' => 260
				),
				array(
					'option' => 'com_content',
					'Itemid' => 260
				),
				array(
				),
				'Error building a URL for category that has a menu item'
			),
			array(
				array(
					'option' => 'com_content',
					'view' => 'form',
					'Itemid' => 263
				),
				// TODO: I think this might be a bug? I think view should be unset whatever the status of the layout
				array(
					'option' => 'com_content',
					'view' => 'form',
					'Itemid' => 263
				),
				array(
				),
				'Error building a URL for a menu item that doesn\'t have a key'
			),
			array(
				array(
					'option' => 'com_content',
					'id' => 19,
					'Itemid' => 260
				),
				array(
					'option' => 'com_content',
					'id' => 19,
					'Itemid' => 260
				),
				array(
				),
				'URL without a view specified cannot build'
			),
		);
	}

	/**
	 * Tests the build() method
	 *
	 * @return  void
	 *
	 * @covers        JComponentRouterRulesStandard::build
	 * @dataProvider  dataTestBuild
	 * @since         3.7.0
	 */
	public function testBuild($query, $expectedQuery, $expectedSegments, $error)
	{
		$actualSegments = array();
		$this->object->build($query, $actualSegments);
		$this->assertEquals($expectedSegments, $actualSegments, $error);
		$this->assertEquals($expectedQuery, $query, $error);
	}

	/**
	 * Provides the data to test the build method.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function dataTestParse()
	{
		return array(
			array(
				array(
					0 => 'beginners'
				),
				array(
					'option' => 'com_content',
					'view' => 'article',
					'catid' => 19,
					'id' => 8
				),
				260,
				'Error parsing a URL for an article with a parent category menu item'
			),
			array(
				array(
					0 => 'park-site',
					1 => 'photo-gallery',
					2 => 'scenery',
					3 => 'cradle-mountain'
				),
				array(
					'option' => 'com_content',
					'view' => 'article',
					'catid' => 73,
					'id' => 11
				),
				272,
				'Error parsing a URL for an article with a parent category menu item'
			),
		);
	}

	/**
	 * Tests the build() method
	 *
	 * @return  void
	 *
	 * @covers        JComponentRouterRulesStandard::parse
	 * @dataProvider  dataTestParse
	 * @since         3.7.0
	 */
	public function testParse($segments, $expectedVars, $activeMenu, $error)
	{
		$vars = array();

		// Set the router ID effectively mimicking JComponentRouterRulesMenu
		/** @var ContentRouterStandardRuleOnly $router */
		$router = TestReflection::getValue($this->object, 'router');
		$router->menu->setActive($activeMenu);
		TestReflection::setValue($this->object, 'router', $router);

		$this->object->parse($segments, $vars);
		$this->assertEquals($expectedVars, $vars, $error . ' invalid vars');
		$this->assertEquals(array(), $segments, $error . ' invalid segments');
	}
}
