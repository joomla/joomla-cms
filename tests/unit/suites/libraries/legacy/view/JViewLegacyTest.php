<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::register('ContentViewArticle', __DIR__ . '/stubs/ContentViewArticle.php');
JLoader::register('ContentViewHtml', __DIR__ . '/stubs/ContentViewHtml.php');
JLoader::register('ExampleViewSEOHtml', __DIR__ . '/stubs/ExampleViewSEOHtml.php');
JLoader::register('MediaViewMediaList', __DIR__ . '/stubs/MediaViewMediaList.php');
JLoader::register('MediaViewMediaListItemsHtml', __DIR__ . '/stubs/MediaViewMediaListItemsHtml.php');

/**
 * Mockup object to test Model handling in JView
 *
 * @package     Joomla.UnitTest
 * @subpackage  View
 * @since       11.3
 */
class ModelMockupJView
{
	public $name = 'model';

	/**
	 * Stub method
	 *
	 * @return  string  Name of Model
	 */
	public function getName()
	{
		return $this->name;
	}
}

/**
 * Test class for JViewLegacy.
 *
 * @package     Joomla.UnitTest
 * @subpackage  View
 *
 * @since       12.3
 */
class JViewLegacyTest extends TestCase
{
	/**
	 * An instance of the test object.
	 *
	 * @var     JViewLegacy
	 * @since   12.1
	 */
	protected $class;

	/**
	 * $_SERVER variable
	 * 
	 * @var   array
	 */
	protected $server;

	/**
	 * Test JViewLegacy::get()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGet()
	{
		$this->class->test = 'pass';

		$test2 = new ModelMockupJView;
		$test2->name = 'test2';

		TestReflection::setValue($this->class, '_models', array('test1' => new ModelMockupJView, 'test2' => $test2));
		TestReflection::setValue($this->class, '_defaultModel', 'test1');

		$this->assertEquals('model', $this->class->get('Name'), 'Checks getName from default model.');
		$this->assertEquals('pass', $this->class->get('test'), 'Checks property from view.');
		$this->assertEquals('test2', $this->class->get('Name', 'test2'), 'Checks getName from model 2.');
	}

	/**
	 * Test JViewLegacy::getLayout()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetLayout()
	{
		$this->assertEquals('default', $this->class->getLayout());

		TestReflection::setValue($this->class, '_layout', 'test1');

		$this->assertEquals('test1', $this->class->getLayout());
	}

	/**
	 * Test JViewLegacy::getModel()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetModel()
	{
		// Prepare variable to compare against and a bunch of models
		$models = array();
		$model1 = new ModelMockupJView;
		$models['model'] = $model1;
		$model2 = new ModelMockupJView;
		$model2->name = 'test';
		$models['test'] = $model2;
		$model3 = new ModelMockupJView;
		$model3->name = 'defaulttest';
		$models['defaulttest'] = $model3;

		// Prepare JView object
		TestReflection::setValue($this->class, '_models', $models);
		TestReflection::setValue($this->class, '_defaultModel', 'defaulttest');

		// Assert that the function returns the model with the specific key
		$this->assertThat($this->class->getModel('test'), $this->equalTo($model2));

		// Assert that the function returns the model with an unspecific key
		$this->assertThat($this->class->getModel('Model'), $this->equalTo($model1));

		// Assert that the function returns the default model
		$this->assertThat($this->class->getModel(), $this->equalTo($model3));
	}

	/**
	 * Test JViewLegacy::getLayoutTemplate()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetLayoutTemplate()
	{
		$this->assertEquals('_', $this->class->getLayoutTemplate());

		TestReflection::setValue($this->class, '_layoutTemplate', '-');

		$this->assertEquals('-', $this->class->getLayoutTemplate());
	}

	/**
	 * Test JViewLegacy::getName()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetName()
	{
		$class = new ContentViewArticle;

		$this->assertEquals('article', $class->getName());
	}

	/**
	 * Test JViewLegacy::getName()
	 *
	 * @since   3.1.5
	 *
	 * @return  void
	 */
	public function testGetNameCamelCase()
	{
		$class = new MediaViewMediaList;

		$this->assertEquals('medialist', $class->getName());
	}

	/**
	 * Test JViewLegacy::getName()
	 *
	 * @since   3.1.5
	 *
	 * @return  void
	 */
	public function testGetNameMultipleUppercase()
	{
		$class = new ExampleViewSEOHtml;

		$this->assertEquals('seohtml', $class->getName());
	}

	/**
	 * Test JViewLegacy::getName()
	 *
	 * @since   3.1.5
	 *
	 * @return  void
	 */
	public function testGetNameMultiLevelCamelCase()
	{
		$class = new MediaViewMediaListItemsHtml;

		$this->assertEquals('medialistitemshtml', $class->getName());
	}

	/**
	 * Test JViewLegacy::getName()
	 *
	 * @since   3.1.5
	 *
	 * @return  void
	 */
	public function testGetNameFormat()
	{
		$class = new ContentViewHtml;

		$this->assertEquals('html', $class->getName());
	}

	/**
	 * Test JViewLegacy::setModel()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testSetModel()
	{
		// Prepare variable to compare against and a bunch of models
		$models = array();
		$model1 = new ModelMockupJView;
		$model2 = new ModelMockupJView;
		$model2->name = 'test';
		$model3 = new ModelMockupJView;
		$model3->name = 'defaulttest';

		// Assert that initial state is empty
		$this->assertAttributeEquals($models, '_models', $this->class);

		// Assert that setModel() returns the model handed over
		$this->assertThat($this->class->setModel($model1), $this->equalTo($model1));
		$models['model'] = $model1;

		// Assert that model was correctly added to array
		$this->assertAttributeEquals($models, '_models', $this->class);

		// Assert that having more than one model works
		$this->class->setModel($model2);
		$models['test'] = $model2;

		$this->assertAttributeEquals($models, '_models', $this->class);

		// Assert that default model works correctly
		$this->assertAttributeEquals('', '_defaultModel', $this->class);

		$this->class->setModel($model3, true);
		$models['defaulttest'] = $model3;

		$this->assertAttributeEquals($models, '_models', $this->class);

		$this->assertAttributeEquals('defaulttest', '_defaultModel', $this->class);
	}

	/**
	 * Test JViewLegacy::setLayout()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testSetLayout()
	{
		$this->assertAttributeEquals('default', '_layout', $this->class);

		$this->class->setLayout('test');

		$this->assertAttributeEquals('test', '_layout', $this->class);
		$this->assertAttributeEquals('_', '_layoutTemplate', $this->class);

		$this->class->setLayout('-:test2');

		$this->assertAttributeEquals('test2', '_layout', $this->class);
		$this->assertAttributeEquals('-', '_layoutTemplate', $this->class);
	}

	/**
	 * Test JViewLegacy::setLayoutExt()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testSetLayoutExt()
	{
		$this->assertAttributeEquals('php', '_layoutExt', $this->class);

		$this->class->setLayoutExt('tmpl');

		$this->assertAttributeEquals('tmpl', '_layoutExt', $this->class);
	}

	/**
	 * Test JViewLegacy::setEscape()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testSetEscape()
	{
		$this->assertAttributeEquals('htmlspecialchars', '_escape', $this->class);

		$this->class->setEscape('escapefunc');

		$this->assertAttributeEquals('escapefunc', '_escape', $this->class);

		$this->class->setEscape(array('EscapeClass', 'func'));

		$this->assertAttributeEquals(array('EscapeClass', 'func'), '_escape', $this->class);
	}

	/**
	 * Test JViewLegacy::addTemplatePath()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testAddTemplatePath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		$this->class->addTemplatePath(JPATH_ROOT . $ds . 'libraries');

		$this->assertAttributeEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			'_path',
			$this->class
		);

		$this->class->addTemplatePath(JPATH_ROOT . $ds . 'cache');

		$this->assertAttributeEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'cache') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			'_path',
			$this->class
		);
	}

	/**
	 * Test JViewLegacy::addHelperPath()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testAddHelperPath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		$this->class->addHelperPath(JPATH_ROOT . $ds . 'libraries');

		$this->assertAttributeEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds), 'template' => array()),
			'_path',
			$this->class
		);

		$this->class->addHelperPath(JPATH_ROOT . $ds . 'cache');

		$this->assertAttributeEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'cache') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds), 'template' => array()),
			'_path',
			$this->class
		);
	}

	/**
	 * Test JViewLegacy::_addPath()
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function test_addPath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		TestReflection::invoke($this->class, '_addPath', 'template', JPATH_ROOT . $ds . 'libraries');

		$this->assertAttributeEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			'_path',
			$this->class
		);

		TestReflection::invoke($this->class, '_addPath', 'helper', realpath(JPATH_ROOT . $ds . 'tests'));

		$this->assertAttributeEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			'_path',
			$this->class
		);

		TestReflection::invoke($this->class, '_addPath', 'template', realpath(JPATH_ROOT . $ds . 'tests'));

		$this->assertAttributeEquals(
			array(
				'helper' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds),
				'template' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)
			),
			'_path',
			$this->class
		);

		TestReflection::invoke($this->class, '_addPath', 'helper', realpath(JPATH_ROOT . $ds . 'libraries'));

		$this->assertAttributeEquals(
			array(
				'helper' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds, realpath(JPATH_ROOT . $ds . 'tests') . $ds),
				'template' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)
			),
			'_path',
			$this->class
		);
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @since   12.1
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
		$this->server = $_SERVER;

		JFactory::$application = TestMockApplication::create($this);
		JFactory::$application->input = new JInput(array());

		defined('JPATH_COMPONENT') or define('JPATH_COMPONENT', JPATH_BASE . '/components/com_foobar');
		$_SERVER['REQUEST_METHOD'] = 'get';
		$_SERVER['HTTP_HOST'] = 'mydomain.com';

		$this->class = new JViewLegacy;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @since    12.1
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		$_SERVER = $this->server;
		JUri::reset();
		unset($this->class);
		parent::tearDown();
	}
}
