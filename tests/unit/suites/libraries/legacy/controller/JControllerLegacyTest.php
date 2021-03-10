<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/stubs/controller.php';

/**
 * Test class for JControllerLegacy.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @since       3.1.4
 */
class JControllerLegacyTest extends TestCase
{
	/**
	 * An instance of the test object.
	 *
	 * @var    JControllerLegacy
	 * @since  1.7.0
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

		JFactory::$application = $this->getMockCmsApp();
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
	 * @testdox  Ensure addModelPath() adds a model path to the internal array
	 *
	 * @covers   JControllerLegacy::addModelPath
	 */
	public function testAddModelPath()
	{
		$path = JPath::clean(JPATH_ROOT . '/addmodelpath');
		JControllerLegacy::addModelPath($path);

		$this->assertTrue(
			in_array($path, JModelLegacy::addIncludePath()),
			'Line:' . __LINE__ . ' The path should be added to the JModel paths.'
		);
	}

	/**
	 * @testdox  Ensure createFileName() correctly returns the file name for a controller
	 *
	 * @covers   JControllerLegacy::createFileName
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
	 * @testdox  Ensure the constructor correctly initialises the class variables
	 *
	 * @covers   JControllerLegacy::__construct
	 */
	public function testConstructer()
	{
		$controller = new TestTestController;
		$this->assertEquals(
			$controller->getTasks(),
			array('task5', 'task1', 'task2', 'display'),
			'The available tasks should be the public tasks in _all_ the derived classes after controller plus "display".'
		);
	}

	/**
	 * @testdox  Ensure the constructor correctly sets the name of the controller when injected via the config
	 *
	 * @covers   JControllerLegacy::__construct
	 */
	public function testConstructerWithInjectedName()
	{
		$name = 'foobar';
		$config = array(
			'name' => $name
		);

		$controller = new TestTestController($config);

		$this->assertEquals(
			TestReflection::getValue($controller, 'name'),
			$name
		);
	}

	/**
	 * @testdox  Ensure the addPath() correctly adds a path
	 *
	 * @covers   JControllerLegacy::addPath
	 * @note     addPath call JPath::check which will exit if the path is out of bounds.
	 *           If execution halts for some reason, a bad path could be the culprit.
	 */
	public function testAddPath()
	{
		$path = JPATH_ROOT . '/foobar';

		TestReflection::invoke($this->class, 'addPath', 'test', $path);

		$paths = TestReflection::getValue($this->class, 'paths');

		$this->assertTrue(is_array($paths['test']), 'The path type should be an array.');

		$this->assertEquals(
			str_replace(DIRECTORY_SEPARATOR, '/', $paths['test'][0]),
			str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT . '/foobar/'),
			'Line:' . __LINE__ . ' The path type should be present, clean and with a trailing slash.'
		);
	}

	/**
	 * @testdox  Ensure the addViewPath() correctly adds a path when initialising views
	 *
	 * @covers   JControllerLegacy::addViewPath
	 * @note     addPath call JPath::check which will exit if the path is out of bounds.
	 *           If execution halts for some reason, a bad path could be the culprit.
	 */
	public function testAddViewPath()
	{
		$this->class->addViewPath(JPATH_ROOT . '/views');

		$paths = TestReflection::getValue($this->class, 'paths');

		$this->assertTrue(is_array($paths['view']), 'The path type should be an array.');

		$this->assertEquals(
			str_replace(DIRECTORY_SEPARATOR, '/', $paths['view'][0]),
			str_replace(DIRECTORY_SEPARATOR, '/', JPATH_ROOT . '/views/'),
			'Line:' . __LINE__ . ' The path type should be present, clean and with a trailing slash.'
		);
	}

	/**
	 * @testdox  Ensure the getName() correctly returns the name of the controller
	 *
	 * @covers   JControllerLegacy::getName
	 */
	public function testGetName()
	{
		$this->assertEquals($this->class->getName(), 'joomla\\cms\\mvc\\controller\\base');

		TestReflection::setValue($this->class, 'name', 'inspector');

		$this->assertEquals($this->class->getName(), 'inspector');
	}

	/**
	 * @testdox  Ensure the getTask() correctly returns the name of the task variable
	 *
	 * @covers   JControllerLegacy::getTask
	 */
	public function testGetTask()
	{
		TestReflection::setValue($this->class, 'task', 'test');

		$this->assertEquals('test', $this->class->getTask());
	}

	/**
	 * @testdox  The available tasks should be the public tasks in the derived controller plus "display".
	 *
	 * @covers   JControllerLegacy::getTasks
	 */
	public function testGetTasks()
	{
		$class = new TestController;

		$this->assertEquals(array('task1', 'task2', 'display'), $class->getTasks());
	}

	/**
	 * @testdox  Tests setting an error message in the controller
	 *
	 * @covers   JControllerLegacy::setMessage
	 */
	public function testSetMessage()
	{
		$this->class->setMessage('Hello World');

		$this->assertAttributeEquals('Hello World', 'message', $this->class, 'Checks the message.');
		$this->assertAttributeEquals('message', 'messageType', $this->class, 'Checks the message type.');

		$this->class->setMessage('Morning Universe', 'notice');

		$this->assertAttributeEquals('Morning Universe', 'message', $this->class, 'Checks a change in the message.');
		$this->assertAttributeEquals('notice', 'messageType', $this->class, 'Checks a change in the message type.');
	}

	/**
	 * @testdox  Tests setting a redirect in the controller with only a URL
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlOnly()
	{
		$this->class->setRedirect('index.php?option=com_foobar');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals(null, 'message', $this->class);
		$this->assertAttributeEquals('message', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setting a redirect in the controller with a URL and message
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlAndMessageWithoutType()
	{
		$this->class->setRedirect('index.php?option=com_foobar', 'Hello World');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Hello World', 'message', $this->class);
		$this->assertAttributeEquals('message', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setting a redirect in the controller with a URL, message and message type
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlAndMessageWithType()
	{
		$this->class->setRedirect('index.php?option=com_foobar', 'Morning Universe', 'notice');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Morning Universe', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setting a redirect in the controller with a URL and message in separate functions
	 *
	 * @covers   JControllerLegacy::setRedirect
	 * @uses     JControllerLegacy::setMessage
	 */
	public function testSetRedirectWithUrlAndMessageWithoutTypeWithPreviouslySetMessage()
	{
		$this->class->setMessage('Hi all');
		$this->class->setRedirect('index.php?option=com_foobar');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Hi all', 'message', $this->class);
		$this->assertAttributeEquals('message', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setRedirect() with a message overwrites a message that was set with setMessage()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 * @uses     JControllerLegacy::setMessage
	 */
	public function testSetRedirectWithMessageOverwritesPreviousMessage()
	{
		$this->class->setMessage('Hi all');
		$this->class->setRedirect('index.php?option=com_foobar', 'Bye all');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Bye all', 'message', $this->class);
		$this->assertAttributeEquals('message', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setRedirect() works when message and message type are set in setMessage() and the message is overridden by setRedirect()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlMessageAndMessageTypeOverwritesPreviouslySetMessageAndMessageType()
	{
		$this->class->setMessage('Hello folks', 'notice');
		$this->class->setRedirect('index.php?option=com_foobar', 'Bye, Folks');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Bye, Folks', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setRedirect() with a message and message type overwrites a message that was set with setMessage()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlMessageAndMessageTypeOverwritesPreviouslySetMessage()
	{
		$this->class->setMessage('Hi all');
		$this->class->setRedirect('index.php?option=com_foobar', 'Bye all', 'notice');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Bye all', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests setRedirect() with a message type set but with the message set using setMessage()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlWithoutMessgeAndWithMessageType()
	{
		$this->class->setMessage('Hi all');
		$this->class->setRedirect('index.php?option=com_foobar', null, 'notice');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Hi all', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Checks that setRedirect() works when a message and message type is previously set with setMessage()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithPreviouslySetMessageAndMessageType()
	{
		$this->class->setMessage('Hello folks', 'notice');
		$this->class->setRedirect('index.php?option=com_foobar');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Hello folks', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests that message and message type set in setMessage() are overwritten by setRedirect()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlMessageAndMessageTypeOverwritesPreviouslySetMessageAndType()
	{
		$this->class->setMessage('Hello folks', 'notice');
		$this->class->setRedirect('index.php?option=com_foobar', 'Bye, folks', 'notice');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Bye, folks', 'message', $this->class);
		$this->assertAttributeEquals('notice', 'messageType', $this->class);
	}

	/**
	 * @testdox  Tests that message and message type set in setMessage() are overridden by setRedirect()
	 *
	 * @covers   JControllerLegacy::setRedirect
	 */
	public function testSetRedirectWithUrlNoMessageAndMessageTypeWithPreviouslySetMessage()
	{
		$this->class->setMessage('Folks?', 'notice');
		$this->class->setRedirect('index.php?option=com_foobar', null, 'question');

		$this->assertAttributeEquals('index.php?option=com_foobar', 'redirect', $this->class);
		$this->assertAttributeEquals('Folks?', 'message', $this->class);
		$this->assertAttributeEquals('question', 'messageType', $this->class);
	}
}
