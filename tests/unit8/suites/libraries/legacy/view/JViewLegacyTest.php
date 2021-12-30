<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  View
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
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
 * @since       1.7.3
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
 * @since       3.1.4
 */
class JViewLegacyTest extends TestCase
{
	/**
	 * An instance of the test object.
	 *
	 * @var     JViewLegacy
	 * @since   3.0.0
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
	 * @since   1.7.3
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
		$this->assertEquals($models, $this->getPropertyValue($this->class, '_models'));

		// Assert that setModel() returns the model handed over
		$this->assertThat($this->class->setModel($model1), $this->equalTo($model1));
		$models['model'] = $model1;

		// Assert that model was correctly added to array
		$this->assertEquals($models, $this->getPropertyValue($this->class, '_models'));

		// Assert that having more than one model works
		$this->class->setModel($model2);
		$models['test'] = $model2;

		$this->assertEquals($models, $this->getPropertyValue($this->class, '_models'));

		// Assert that default model works correctly
		$this->assertEquals('', $this->getPropertyValue($this->class, '_defaultModel'));

		$this->class->setModel($model3, true);
		$models['defaulttest'] = $model3;

		$this->assertEquals($models, $this->getPropertyValue($this->class, '_models'));

		$this->assertEquals('defaulttest', $this->getPropertyValue($this->class, '_defaultModel'));

	}

	/**
	 * Test JViewLegacy::setLayout()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function testSetLayout()
	{
		$this->assertEquals('default', $this->getPropertyValue($this->class, '_layout'));

		$this->class->setLayout('test');

		$this->assertEquals('test', $this->getPropertyValue($this->class, '_layout'));
		$this->assertEquals('_', $this->getPropertyValue($this->class, '_layoutTemplate'));

		$this->class->setLayout('-:test2');

		$this->assertEquals('test2', $this->getPropertyValue($this->class, '_layout'));
		$this->assertEquals('-', $this->getPropertyValue($this->class, '_layoutTemplate'));
	}

	/**
	 * Test JViewLegacy::setLayoutExt()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function testSetLayoutExt()
	{
		$this->assertEquals('php', $this->getPropertyValue($this->class, '_layoutExt'));

		$this->class->setLayoutExt('tmpl');

		$this->assertEquals('tmpl', $this->getPropertyValue($this->class, '_layoutExt'));
	}

	/**
	 * Test JViewLegacy::setEscape()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function testSetEscape()
	{
		$this->assertEquals('htmlspecialchars', $this->getPropertyValue($this->class, '_escape'));

		$this->class->setEscape('escapefunc');

		$this->assertEquals('escapefunc', $this->getPropertyValue($this->class, '_escape'));

		$this->class->setEscape(array('EscapeClass', 'func'));

		$this->assertEquals(array('EscapeClass', 'func'), $this->getPropertyValue($this->class, '_escape'));
	}

	/**
	 * Test JViewLegacy::addTemplatePath()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function testAddTemplatePath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		$this->class->addTemplatePath(JPATH_ROOT . $ds . 'libraries');

		$this->assertEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			$this->getPropertyValue($this->class, '_path')
		);

		$this->class->addTemplatePath(JPATH_ROOT . $ds . 'cache');

		$this->assertEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'cache') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			$this->getPropertyValue($this->class, '_path')
		);
	}

	/**
	 * Test JViewLegacy::addHelperPath()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function testAddHelperPath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		$this->class->addHelperPath(JPATH_ROOT . $ds . 'libraries');

		$this->assertEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds), 'template' => array()),
			$this->getPropertyValue($this->class, '_path')
		);

		$this->class->addHelperPath(JPATH_ROOT . $ds . 'cache');

		$this->assertEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'cache') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds), 'template' => array()),
			$this->getPropertyValue($this->class, '_path')
		);
	}

	/**
	 * Test JViewLegacy::_addPath()
	 *
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function test_addPath()
	{
		$ds = DIRECTORY_SEPARATOR;

		// Reset the internal _path property so we can track it more easily.
		TestReflection::setValue($this->class, '_path', array('helper' => array(), 'template' => array()));

		TestReflection::invoke($this->class, '_addPath', 'template', JPATH_ROOT . $ds . 'libraries');

		$this->assertEquals(
			array('helper' => array(), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			$this->getPropertyValue($this->class, '_path')
		);

		TestReflection::invoke($this->class, '_addPath', 'helper', realpath(JPATH_ROOT . $ds . 'tests'));

		$this->assertEquals(
			array('helper' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds), 'template' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds)),
			$this->getPropertyValue($this->class, '_path')
		);

		TestReflection::invoke($this->class, '_addPath', 'template', realpath(JPATH_ROOT . $ds . 'tests'));

		$this->assertEquals(
			array(
				'helper' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds),
				'template' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)
			),
			$this->getPropertyValue($this->class, '_path')
		);

		TestReflection::invoke($this->class, '_addPath', 'helper', realpath(JPATH_ROOT . $ds . 'libraries'));

		$this->assertEquals(
			array(
				'helper' => array(realpath(JPATH_ROOT . $ds . 'libraries') . $ds, realpath(JPATH_ROOT . $ds . 'tests') . $ds),
				'template' => array(realpath(JPATH_ROOT . $ds . 'tests') . $ds, realpath(JPATH_ROOT . $ds . 'libraries') . $ds)
			),
			$this->getPropertyValue($this->class, '_path')
		);
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @since   3.0.0
	 *
	 * @return  void
	 */
	protected function setUp(): void
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
	 * @since    3.0.0
	 *
	 * @return  void
	 */
	protected function tearDown(): void
	{
		$this->restoreFactoryState();
		$_SERVER = $this->server;
		JUri::reset();
		unset($this->class);
		parent::tearDown();
	}
}
