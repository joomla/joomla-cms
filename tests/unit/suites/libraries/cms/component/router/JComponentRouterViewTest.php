<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JComponentRouterViewInspector.php';
require_once __DIR__ . '/stubs/componentrouterrule.php';
require_once __DIR__ . '/stubs/JCategoriesMock.php';

/**
 * Test class for JComponentRouterAdvanced.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Component
 * @since       3.4
 */
class JComponentRouterViewTest extends TestCaseDatabase
{
	/**
	 * Object under test
	 *
	 * @var    JComponentRouterView
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
		$this->object = new JComponentRouterViewInspector($app, $app->getMenu());
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

		$dataSet->addTable('jos_categories', JPATH_TEST_DATABASE . '/jos_categories.csv');

		return $dataSet;
	}

	/**
	 * Tests the registerView() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::registerView
	 */
	public function testRegisterView()
	{
		$views = $this->getComContentViews();

		foreach ($views as $view)
		{
			$this->object->registerView($view);
		}

		$this->assertEquals($views, $this->object->get('views'));
	}

	/**
	 * Tests the getViews() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::getViews
	 */
	public function testGetViews()
	{
		$views = $this->getComContentViews();

		foreach ($views as $view)
		{
			$this->object->registerView($view);
		}

		$this->assertEquals($views, $this->object->getViews());
	}

	/**
	 * Tests the getPath() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::getPath
	 */
	public function testGetPath()
	{
		// This test requires an application registered to JFactory
		$this->saveFactoryState();
		JFactory::$application = $this->object->app;

		$views = $this->getComContentViews();
		$this->object->set('name', 'unittest');

		foreach ($views as $view)
		{
			$this->object->registerView($view);
		}

		// No view, so we don't have a path to return.
		$query = array('task' => 'edit');
		$this->assertEquals(array(), $this->object->getPath($query));

		// View without any parents and children
		$query = array('view' => 'form');
		$this->assertEquals(array('form' => true), $this->object->getPath($query));

		// View without any parents, but with children
		$query = array('view' => 'categories');
		$this->assertEquals(array('categories' => true), $this->object->getPath($query));

		// View with parent and children
		$query = array('view' => 'category', 'id' => '9');
		$this->assertEquals(array('category' => array('9:uncategorised'), 'categories' => true), $this->object->getPath($query));

		//View with parent, no children
		$query = array('view' => 'article', 'id' => '42:question-for-everything', 'catid' => '9');
		$this->assertEquals(array(
			'article' => array('42:question-for-everything'),
			'category' => array('9:uncategorised'),
			'categories' => true),
			$this->object->getPath($query));

		//View with parent, no children and nested view
		$query = array('view' => 'article', 'id' => '42:question-for-everything', 'catid' => '20');
		$this->assertEquals(array(
			'article' => array('42:question-for-everything'),
			'category' => array('20:extensions',
				'19:joomla',
				'14:sample-data-articles'
			),
			'categories' => true),
			$this->object->getPath($query)
		);

		$this->restoreFactoryState();
	}

	/**
	 * Tests the getRules() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::getRules
	 */
	public function testGetRules()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->object->attachRule($rule);
		$this->assertEquals(array($rule), $this->object->getRules());
	}

	/**
	 * Tests the attachRules() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::attachRules
	 */
	public function testAttachRules()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->assertEquals(array(), $this->object->getRules());
		$this->object->attachRules(array($rule));
		$this->assertEquals(array($rule), $this->object->getRules());
	}

	/**
	 * Tests the attachRule() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::attachRule
	 */
	public function testAttachRule()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->assertEquals(array(), $this->object->get('rules'));
		$this->object->attachRule($rule);
		$this->assertEquals(array($rule), $this->object->get('rules'));
		$this->object->attachRule($rule);
		$this->assertEquals(array($rule, $rule), $this->object->get('rules'));
	}

	/**
	 * Tests the detachRule() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::detachRule
	 */
	public function testDetachRule()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->object->attachRule($rule);
		$this->assertEquals(array($rule), $this->object->get('rules'));
		$this->assertTrue($this->object->detachRule($rule));
		$this->assertEquals(array(), $this->object->get('rules'));
		$this->assertFalse($this->object->detachRule($rule));
	}

	/**
	 * Tests the preprocess() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::preprocess
	 */
	public function testPreprocess()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->object->attachRule($rule);
		$this->assertEquals(array('key' => 'value', 'testrule' => 'yes'), $this->object->preprocess(array('key' => 'value')));
	}

	/**
	 * Tests the build() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::build
	 */
	public function testBuild()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->object->attachRule($rule);
		$query = array('key' => 'value', 'test' => 'true');
		$this->assertEquals(array('testrule-run'), $this->object->build($query));
		$this->assertEquals(array('key' => 'value'), $query);
	}

	/**
	 * Tests the parse() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::parse
	 */
	public function testParse()
	{
		$rule = new TestComponentRouterRule($this->object);
		$this->object->attachRule($rule);
		$segments = array('testrun', 'getsdropped');
		$this->assertEquals(array('testparse' => 'run'), $this->object->parse($segments));
		$this->assertEquals(array('testrun'), $segments);
	}

	/**
	 * Tests the getName() method
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @covers  JComponentRouterView::getName
	 */
	public function testGetName()
	{
		$this->object->set('name', 'test');
		$this->assertEquals('test', $this->object->getName());
		$this->object->set('name', null);
		$this->assertEquals('jcomponent', $this->object->getName());
	}

	/**
	 * Tests the getName() method and if it throws the right Exception
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @expectedException Exception
	 * @covers  JComponentRouterView::getName
	 */
	public function testGetNameException()
	{
		$object = new FakeComponentURLCreator($this->object->app, $this->object->menu);
		$object->getName();
	}

	/**
	 * As view testdata, use the view configuration of com_content
	 *
	 * @return array|JComponentRouterViewconfiguration
	 */
	protected function getComContentViews()
	{
		$categories = new JComponentRouterViewconfiguration('categories');
		$category = new JComponentRouterViewconfiguration('category');
		$category->setKey('id')->setParent($categories)->setNestable()->addLayout('blog');
		$article = new JComponentRouterViewconfiguration('article');
		$article->setKey('id')->setParent($category, 'catid');
		$archive = new JComponentRouterViewconfiguration('archive');
		$featured = new JComponentRouterViewconfiguration('featured');
		$form = new JComponentRouterViewconfiguration('form');

		return array(
			'categories' => $categories,
			'category' => $category,
			'article' => $article,
			'archive' => $archive,
			'featured' => $featured,
			'form' => $form
		);
	}
}
