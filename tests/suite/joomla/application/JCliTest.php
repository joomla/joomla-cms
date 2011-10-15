<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/cli.php';
require_once JPATH_TESTS.'/suite/joomla/event/JDispatcherInspector.php';
include_once __DIR__.'/TestStubs/JCli_Inspector.php';

/**
 * Test class for JCli.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.3
 */
class JCliTest extends JoomlaTestCase
{
	/**
	 * An instance of a JCli inspector.
	 *
	 * @var    JCliInspector
	 * @since  11.3
	 */
	protected $inspector;

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getFetchConfigurationData()
	{
		return array(
			// fileName, expectsClass, (expected result array)
			'Default configuration class' => array(null, true, array('foo' => 'bar')),
			'Custom file with array' => array('config.jweb-array', false, array('foo' => 'bar')),
// 			'Custom file, invalid class' => array('config.JCli-wrongclass', false, array()),
			'Custom file, snooping' => array('../test_application/config.jcli-snoopy', false, array()),
		);
	}

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		// Get a new JCliInspector instance.
		$this->inspector = new JCliInspector;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		JDispatcherInspector::setInstance(null);

		parent::tearDown();
	}

	/**
	 * Tests the JCli::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$this->assertInstanceOf(
			'JInput',
			$this->inspector->input,
			'Input property wrong type'
		);

		$this->assertInstanceOf(
			'JRegistry',
			$this->inspector->getClassProperty('config'),
			'Config property wrong type'
		);

		$this->assertInstanceOf(
			'JDispatcher',
			$this->inspector->getClassProperty('dispatcher'),
			'Dispatcher property wrong type'
		);

		// TODO Test that configuration data loaded.

		$this->assertThat(
			$this->inspector->get('execution.datetime'),
			$this->greaterThan('2001'),
			'Tests execution.datetime was set.'
		);

		$this->assertThat(
			$this->inspector->get('execution.timestamp'),
			$this->greaterThan(1),
			'Tests execution.timestamp was set.'
		);
	}

	/**
	 * Tests the JCli::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__constructDependancyInjection()
	{
		$mockInput = $this->getMock('JInputCli', array('test'), array(), '', false);
		$mockInput
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('ok')
			);

		$mockConfig = $this->getMock('JRegistry', array('test'), array(), '', false);
		$mockConfig
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('ok')
			);

		$mockDispatcher = $this->getMockDispatcher();
		$mockDispatcher
			->expects($this->any())
			->method('test')
			->will(
				$this->returnValue('ok')
			);

		$inspector = new JCliInspector($mockInput, $mockConfig, $mockDispatcher);

		$this->assertThat(
			$inspector->input->test(),
			$this->equalTo('ok'),
			'Tests input injection.'
		);

		$this->assertThat(
			$inspector->getClassProperty('config')->test(),
			$this->equalTo('ok'),
			'Tests config injection.'
		);

		$this->assertThat(
			$inspector->getClassProperty('dispatcher')->test(),
			$this->equalTo('ok'),
			'Tests dispatcher injection.'
		);
	}

	/**
	 * Tests the JCli::close method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClose()
	{
		// Make sure the application is not already closed.
		$this->assertSame(
			$this->inspector->closed,
			null,
			'Checks the application doesn\'t start closed.'
		);

		$this->inspector->close(3);

		// Make sure the application is closed with code 3.
		$this->assertSame(
			$this->inspector->closed,
			3,
			'Checks the application was closed with exit code 3.'
		);
	}

	/**
	 * Tests the JCli::Execute method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecute()
	{
		// Manually inject the dispatcher.
		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());

		// Register all the methods so that we can track if they have been fired.
		$this->inspector->registerEvent('onBeforeExecute', 'JWebTestExecute-onBeforeExecute')
			->registerEvent('JWebDoExecute', 'JWebTestExecute-JWebDoExecute')
			->registerEvent('onAfterExecute', 'JWebTestExecute-onAfterExecute');

		$this->inspector->execute();

		$this->assertThat(
			JDispatcherGlobalMock::$triggered,
			$this->equalTo(
				array(
					'onBeforeExecute',
					'JWebDoExecute',
					'onAfterExecute',
				)
			),
			'Check that events fire in the right order.'
		);
	}

	/**
	 * Tests the JCli::fetchConfigurationData method.
	 *
	 * @param   string   $fileName      The name of the configuration file.
	 * @param   boolean  $expectsClass  The result is expected to be a class.
	 * @param   array    $expects       The expected result as an array.
	 *
	 * @return  void
	 *
	 * @dataProvider getFetchConfigurationData
	 * @since   11.3
	 */
	public function testFetchConfigurationData($fileName, $expectsClass, $expects)
	{
		$config = $this->inspector->fetchConfigurationData($fileName);

		if ($expectsClass)
		{
			$this->assertInstanceOf(
				'JConfig',
				$config,
				'Checks the configuration object is the appropriate class.'
			);
		}

		$this->assertThat(
			(array) $config,
			$this->equalTo($expects),
			'Checks the content of the configuration object.'
		);
	}

	/**
	 * Tests the JCli::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$config = new JRegistry(array('foo' => 'bar'));

		$this->inspector->setClassProperty('config', $config);

		$this->assertThat(
			$this->inspector->get('foo', 'car'),
			$this->equalTo('bar'),
			'Checks a known configuration setting is returned.'
		);

		$this->assertThat(
			$this->inspector->get('goo', 'car'),
			$this->equalTo('car'),
			'Checks an unknown configuration setting returns the default.'
		);
	}

	/**
	 * Tests the JCli::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JCliInspector',
			JCli::getInstance('JCliInspector'),
			'Tests that getInstance will instantiate a valid child class of JCli.'
		);

		$this->inspector->setClassInstance('foo');

		$this->assertThat(
			JCli::getInstance('JCliInspector'),
			$this->equalTo('foo'),
			'Tests that singleton value is returned.'
		);

		$this->inspector->setClassInstance(null);

		$this->assertInstanceOf(
			'JCli',
			JCli::getInstance('Foo'),
			'Tests that getInstance will instantiate a valid child class of JCli given a non-existent type.'
		);
	}

	/**
	 * Tests the JCli::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadConfiguration()
	{
		$this->assertThat(
			$this->inspector->loadConfiguration(
				array(
					'foo' => 'bar',
				)
			),
			$this->identicalTo($this->inspector),
			'Check chaining.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('foo'),
			$this->equalTo('bar'),
			'Check the configuration array was loaded.'
		);

		$this->inspector->loadConfiguration(
			(object) array(
				'goo' => 'car',
			)
		);

		$this->assertThat(
			$this->inspector->getClassProperty('config')->get('goo'),
			$this->equalTo('car'),
			'Check the configuration object was loaded.'
		);
	}

	/**
	 * Tests the JCli::loadDispatcher method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDispatcher()
	{
		// Inject the mock dispatcher into the JDispatcher singleton.
		JDispatcherInspector::setInstance($this->getMockDispatcher());

		$this->inspector->loadDispatcher();

		$this->assertInstanceOf(
			'JDispatcher',
			$this->inspector->getClassProperty('dispatcher'),
			'Tests that the dispatcher object is the correct class.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('dispatcher')->test(),
			$this->equalTo('ok'),
			'Tests that we got the dispatcher from the factory.'
		);
	}

	/**
	 * Tests the JCli::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRegisterEvent()
	{
		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());

		$this->assertThat(
			$this->inspector->registerEvent('onJCliRegisterEvent', 'function'),
			$this->identicalTo($this->inspector),
			'Check chaining.'
		);

		$this->assertArrayHasKey(
			'onJCliRegisterEvent',
			JDispatcherGlobalMock::$handlers,
			'Checks the events were passed to the mock dispatcher.'
		);
	}

	/**
	 * Tests the JCli::set method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSet()
	{
		$config = new JRegistry(array('foo' => 'bar'));

		$this->inspector->setClassProperty('config', $config);

		$this->assertThat(
			$this->inspector->set('foo', 'car'),
			$this->equalTo('bar'),
			'Checks set returns the previous value.'
		);

		$this->assertThat(
			$config->get('foo'),
			$this->equalTo('car'),
			'Checks the new value has been set.'
		);
	}

	/**
	 * Tests the JCli::triggerEvents method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTriggerEvents()
	{
		$this->inspector->setClassProperty('dispatcher', null);
		$this->assertThat(
			$this->inspector->triggerEvent('onJCliTriggerEvent'),
			$this->isNull(),
			'Checks that for a non-dispatcher object, null is returned.'
		);

		$this->inspector->setClassProperty('dispatcher', $this->getMockDispatcher());
		$this->inspector->registerEvent('onJCliTriggerEvent', 'function');

		$this->assertThat(
			$this->inspector->triggerEvent('onJCliTriggerEvent'),
			$this->equalTo(
				array('function' => null)
			),
			'Checks the correct dispatcher method is called.'
		);
	}
}
