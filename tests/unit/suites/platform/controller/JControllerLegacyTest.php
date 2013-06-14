<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/controller.php';

/**
 * Test class for JControllerLegacy.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @since       12.3
 */
class JControllerLegacyTest extends TestCase
{
	/**
	 * An instance of the test object.
	 *
	 * @var    JControllerLegacy
	 * @since  11.1
	 */
	protected $class;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		if (!defined('JPATH_COMPONENT'))
		{
			define('JPATH_COMPONENT', JPATH_BASE . '/components/com_foobar');
		}

		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$config = $this->getMockConfig();

		$this->class = new JControllerLegacy;

		parent::setUp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		$this->class = null;

		parent::tearDown();
	}

	/**
	 * Test JController::addModelPath
	 *
	 * @since 11.3
	 *
	 * @return  void
	 */
	public function testAddModelPath()
	{
		$path = JPath::clean(JPATH_ROOT . '/addmodelpath');
		JControllerLegacy::addModelPath($path);

		// The default path is the class file folder/forms
		$valid = JPATH_PLATFORM . '/joomla/form/fields';

		$this->assertThat(
			in_array($path, JModelLegacy::addIncludePath()),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The path should be added to the JModel paths.'
		);
	}

	/**
	 * Test JController::createFileName().
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testCreateFileName()
	{
		$parts = array('name' => 'test');

		$this->assertEquals('test.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'controller', $parts), __LINE__);

		$parts['format'] = 'html';

		$this->assertEquals('test.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'controller', $parts), __LINE__);

		$parts['format'] = 'json';

		$this->assertEquals('test.json.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'controller', $parts), __LINE__);

		$parts = array('name' => 'TEST', 'format' => 'JSON');

		$this->assertEquals('test.json.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'controller', $parts), __LINE__);

		$parts = array('name' => 'test');

		$this->assertEquals('test/view.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'view', $parts), __LINE__);

		$parts['type'] = 'json';

		$this->assertEquals('test/view.json.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'view', $parts), __LINE__);

		$parts = array('type' => 'JSON', 'name' => 'TEST');

		$this->assertEquals('test/view.json.php', TestReflection::invoke('JControllerLegacy', 'createFileName', 'view', $parts), __LINE__);
	}

	/**
	 * Test JControllerLegacy::getInstance
	 *
	 * @todo    Implement testGetInstance().
	 *
	 * @return  void
	 */
	public function testGetInstance()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::__construct
	 *
	 * @since 11.3
	 *
	 * @return  void
	 */
	public function test__construct()
	{
		$controller = new TestTestController;
		$this->assertThat(
			$controller->getTasks(),
			$this->equalTo(array('task5', 'task1', 'task2', 'display')),
			'The available tasks should be the public tasks in _all_ the derived classes after controller plus "display".'
		);
	}

	/**
	 * Test JControllerLegacy::addPath().
	 *
	 * Note that addPath call JPath::check which will exit if the path is out of bounds.
	 * If execution halts for some reason, a bad path could be the culprit.
	 *
	 * @since 11.3
	 *
	 * @return  void
	 */
	public function testAddPath()
	{
		$path = JPATH_ROOT . '/foobar';

		TestReflection::invoke($this->class, 'addPath', 'test', $path);

		$paths = TestReflection::getValue($this->class, 'paths');

		$this->assertTrue(is_array($paths['test']), 'The path type should be an array.');

		$this->assertThat(
			str_replace(DIRECTORY_SEPARATOR, '/', $paths['test'][0]),
			$this->equalTo(str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT . '/foobar/')),
			'Line:' . __LINE__ . ' The path type should be present, clean and with a trailing slash.'
		);
	}

	/**
	 * Test JControllerLegacy::addViewPath
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testAddViewPath()
	{
		$this->class->addViewPath(JPATH_ROOT . '/views');

		$paths = TestReflection::getValue($this->class, 'paths');

		$this->assertTrue(is_array($paths['view']), 'The path type should be an array.');

		$this->assertThat(
			str_replace(DIRECTORY_SEPARATOR, '/', $paths['view'][0]),
			$this->equalTo(str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT . '/views/')),
			'Line:' . __LINE__ . ' The path type should be present, clean and with a trailing slash.'
		);
	}

	/**
	 * Test JControllerLegacy::authorise
	 *
	 * @todo    Implement testAuthorise().
	 *
	 * @return  void
	 */
	public function testAuthorise()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::checkEditId
	 *
	 * @todo    Implement testCheckEditId().
	 *
	 * @return  void
	 */
	public function testCheckEditId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::createModel
	 *
	 * @todo    Implement testCreateModel().
	 *
	 * @return  void
	 */
	public function testCreateModel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::createView
	 *
	 * @todo    Implement testCreateView().
	 *
	 * @return  void
	 */
	public function testCreateView()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::display
	 *
	 * @todo    Implement testDisplay().
	 *
	 * @return  void
	 */
	public function testDisplay()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::execute
	 *
	 * @todo    Implement testExecute().
	 *
	 * @return  void
	 */
	public function testExecute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::getModel
	 *
	 * @todo    Implement testGetModel().
	 *
	 * @return  void
	 */
	public function testGetModel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::getName
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetName()
	{
		$this->assertThat($this->class->getName(), $this->equalTo('j'));

		TestReflection::setValue($this->class, 'name', 'inspector');

		$this->assertThat($this->class->getName(), $this->equalTo('inspector'));
	}

	/**
	 * Test JControllerLegacy::getTask().
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetTask()
	{
		TestReflection::setValue($this->class, 'task', 'test');

		$this->assertEquals('test', $this->class->getTask());
	}

	/**
	 * Test JControllerLegacy::getTasks
	 *
	 * @since   11.3
	 *
	 * @return  void
	 */
	public function testGetTasks()
	{
		$class = new TestController;

		// The available tasks should be the public tasks in the derived controller plus "display".
		$this->assertEquals(array('task1', 'task2', 'display'), $class->getTasks());
	}

	/**
	 * Test JControllerLegacy::getView
	 *
	 * @todo    Implement testGetView().
	 *
	 * @return  void
	 */
	public function testGetView()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::holdEditId
	 *
	 * @todo    Implement testHoldEditId().
	 *
	 * @return  void
	 */
	public function testHoldEditId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::redirect
	 *
	 * @todo    Implement testRedirect().
	 *
	 * @return  void
	 */
	public function testRedirect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::registerDefaultTask
	 *
	 * @todo    Implement testRegisterDefaultTask().
	 *
	 * @return  void
	 */
	public function testRegisterDefaultTask()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::registerTask
	 *
	 * @todo    Implement testRegisterTask().
	 *
	 * @return  void
	 */
	public function testRegisterTask()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::unregisterTask
	 *
	 * @todo    Implement testUnregisterTask().
	 *
	 * @return  void
	 */
	public function testUnregisterTask()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::releaseEditId
	 *
	 * @todo    Implement testReleaseEditId().
	 *
	 * @return  void
	 */
	public function testReleaseEditId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::setAccessControl
	 *
	 * @todo    Implement testSetAccessControl().
	 *
	 * @return  void
	 */
	public function testSetAccessControl()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::setPath
	 *
	 * @todo    Implement testSetPath().
	 *
	 * @return  void
	 */
	public function testSetPath()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete();
	}

	/**
	 * Test JControllerLegacy::testSetRedirectWithUrl
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function testSetRedirectWithUrl()
	{
		$this->class->setRedirect('index.php?option=com_foobar');
		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class, 'Checks that the redirect is set.');
	}
	
	/**
	 * Test JControllerLegacy::testSetRedirectWithNullMsg
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function testSetRedirectWithNullMsg()
	{
		$this->class->setRedirect('index.php?option=com_foobar', null);
		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class, 'Checks that the redirect is set');
		$this->assertAttributeEquals(null, 'message', $this->class, 'Checks that message is not set for null value');
	}
	
	/**
	 * Test JControllerLegacy::testSetRedirectWithMsg
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function testSetRedirectWithMsg()
	{
		$this->class->setRedirect('index.php?option=com_foobar', 'Thanks for reading this.');
		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class, 'Checks the redirect is set.');
		$this->assertAttributeEquals('Thanks for reading this.', 'message', $this->class, 'Checks that the message is set when msg has a value');

	}
	
	
	/**
	 * Test JControllerLegacy::testSetMessageWithNullMsg
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	 public function testSetMessageWithNullMsg()
	{
		$this->class->setMessage(null);
		$this->assertAttributeEquals(null, 'message', $this->class, 'Checks the message.');
		$this->assertAttributeEquals(null, 'messageType', $this->class, 'Checks the message type.');
	}

	/**
	 * Test JControllerLegacy::testSetMessageWithMsgAndType
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function testSetMessageWithMsgAndType()
	{
		$this->class->setMessage('Morning Universe', 'notice');
		$this->assertAttributeEquals('Morning Universe', 'message', $this->class, 'Checks a change in the message.');
		$this->assertAttributeEquals('notice', 'messageType', $this->class, 'Checks a change in the message type.');
	}

	/**
	 * Test JControllerLegacy::testSetMessageWithMsgAndNullType
	 *
	 * @since   3.0
	 *
	 * @return  void
	 */
	public function testSetMessageWithMsgAndNullType()
	{
		$this->class->setMessage('Morning Universe', null);
		$this->assertAttributeEquals('Morning Universe', 'message', $this->class, 'Checks a change in the message.');
		$this->assertAttributeEquals('message', 'messageType', $this->class, 'Checks a change in the message type.');
	}

}
