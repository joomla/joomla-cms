<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

include_once __DIR__ . '/stubs/JApplicationCliInspector.php';

/**
 * Test class for JApplicationCli.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.3
 */
class JApplicationCliTest extends TestCase
{
	/**
	 * An instance of the object to test.
	 *
	 * @var    JApplicationCliInspector
	 * @since  11.3
	 */
	protected $class;

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

		// Get a new JApplicationCliInspector instance.
		$this->class = new JApplicationCliInspector;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		TestReflection::setValue('JApplicationCli', 'instance', null);

		unset($this->class);
		parent::tearDown();
	}

	/**
	 * Tests the JApplicationCli::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$this->assertAttributeInstanceOf('\\Joomla\\Input\\Input', 'input', $this->class);
		$this->assertAttributeInstanceOf('\\Joomla\\Registry\\Registry', 'config', $this->class);
		$this->assertAttributeEmpty('dispatcher', $this->class);

		// TODO Test that configuration data loaded.

		$this->assertGreaterThan(2001, $this->class->get('execution.datetime'), 'Tests execution.datetime was set.');
		$this->assertGreaterThan(1, $this->class->get('execution.timestamp'), 'Tests execution.timestamp was set.');
	}

	/**
	 * Tests the JApplicationCli::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__constructDependancyInjection()
	{
		// Build the mock object.
		$mockInput = $this->getMockBuilder('JInputCli')
					->setMethods(array('test'))
					->setConstructorArgs(array())
					->setMockClassName('')
					->disableOriginalConstructor()
					->getMock();

		$mockInput->expects($this->any())
			->method('test')
			->willReturn('ok');

		$mockConfig = $this->getMockBuilder('\\Joomla\\Registry\\Registry')
					->setMethods(array('test'))
					->setConstructorArgs(array(null))
					->setMockClassName('')
					->getMock();
		$mockConfig
			->expects($this->any())
			->method('test')
			->willReturn('ok');

		$mockDispatcher = $this->getMockDispatcher();
		$mockDispatcher->expects($this->any())
			->method('test')
			->willReturn('ok');

		$class = $this->getMockBuilder('JApplicationCli')
			->setConstructorArgs([$mockInput, $mockConfig, null, null, $mockDispatcher])
			->getMockForAbstractClass();

		$this->assertEquals('ok', $class->input->test(), 'Tests input injection.');
		$this->assertEquals('ok', TestReflection::getValue($class, 'config')->test(), 'Tests config injection.');
	}

	/**
	 * Tests the JApplicationCli::Execute method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecute()
	{
		$this->class->execute();
	}

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
			// Note: file, class, expectsClass, (expected result array), whether there should be an exception
			'Default configuration class' => array(JPATH_TEST_STUBS . '/configuration.php', null, 'JConfig', 'ConfigEval'),
			'Custom file, invalid class' => array(JPATH_TEST_STUBS . '/config.wrongclass.php', 'noclass', false, array(), true),
		);
	}

	/**
	 * Tests the JApplicationCli::fetchConfigurationData method.
	 *
	 * @param   string   $file               The name of the configuration file.
	 * @param   string   $class              The name of the class.
	 * @param   boolean  $expectsClass       The result is expected to be a class.
	 * @param   array    $expects            The expected result as an array.
	 * @param   boolean  $expectedException  The expected exception
	 *
	 * @return  void
	 *
	 * @dataProvider getFetchConfigurationData
	 * @since    11.3
	 */
	public function testFetchConfigurationData($file, $class, $expectsClass, $expects, $expectedException = false)
	{
		if ($expectedException)
		{
			$this->expectException('RuntimeException');
		}

		if (is_null($file) && is_null($class))
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData');
		}
		elseif (is_null($class))
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData', $file);
		}
		else
		{
			$config = TestReflection::invoke($this->class, 'fetchConfigurationData', $file, $class);
		}

		if ($expects == 'ConfigEval')
		{
			$expects = new JConfig;
			$expects = (array) $expects;
		}

		if ($expectsClass)
		{
			$this->assertInstanceOf($expectsClass, $config, 'Checks the configuration object is the appropriate class.');
		}

		$this->assertEquals($expects, (array) $config, 'Checks the content of the configuration object.');
	}

	/**
	 * Tests the JApplicationCli::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$this->assertEquals('bar', $this->class->get('foo', 'bar'), 'Checks a known configuration setting is returned.');
		$this->assertEquals('car', $this->class->get('goo', 'car'), 'Checks an unknown configuration setting returns the default.');
	}

	/**
	 * Tests the JApplicationCli::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JApplicationCliInspector',
			JApplicationCli::getInstance('JApplicationCliInspector'),
			'Tests that getInstance will instantiate a valid child class of JApplicationCli.'
		);

		TestReflection::setValue('JApplicationCli', 'instance', 'foo');

		$this->assertEquals('foo', JApplicationCli::getInstance('JApplicationCliInspector'), 'Tests that singleton value is returned.');
	}

	/**
	 * Tests the JApplicationCli::getInstance method for an unexisting class.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @expectedException  RuntimeException
	 */
	public function testGetInstanceForUnexistingClass()
	{
		JApplicationCli::getInstance('Foo');
	}

	/**
	 * Tests the JApplicationCli::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadConfiguration()
	{
		$this->assertSame(
			$this->class, $this->class->loadConfiguration(array('foo' => 'bar')));

		$this->assertEquals('bar', TestReflection::getValue($this->class, 'config')->get('foo'), 'Check the configuration array was loaded.');

		$this->class->loadConfiguration(
			(object) array(
				'goo' => 'car',
			)
		);

		$this->assertEquals('car', TestReflection::getValue($this->class, 'config')->get('goo'), 'Check the configuration object was loaded.');
	}
}
