<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JHtmlBehaviorInspector.php';

/**
 * Test class for JHtmlBehavior.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlBehaviorTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		// Ensure the loaded states are reset
		JHtmlBehaviorInspector::resetLoaded();

		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$document = $this->getMockDocument();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Data for the testFramework method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getFrameworkData()
	{
		$data = array(
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true))),
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true),
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true)), false, false),
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true)), false, true),
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true, false),
			array(array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true, true)
		);

		return $data;
	}

	/**
	 * Tests the framework method.
	 *
	 * @param   string   $expected  @todo
	 * @param   boolean  $extras    @todo
	 * @param   boolean  $debug     @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getFrameworkData
	 */
	public function testFramework($expected, $extras = false, $debug = null)
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::framework($extras, $debug);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Data for the testCaption method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getCaptionData()
	{
		$data = array(
			array(array('JHtmlBehavior::caption' => array('img.caption' => true))),
			array(array('JHtmlBehavior::caption' => array('img.caption2' => true)), 'img.caption2'),
		);

		return $data;
	}

	/**
	 * Tests the caption method.
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getCaptionData
	 */
	public function testCaption($expected, $selector = 'img.caption')
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::caption($selector);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the formvalidation method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testFormvalidation()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::formvalidation();
		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true), 'JHtmlBehavior::formvalidator' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the switcher method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testSwitcher()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::switcher();
		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::framework' => array('core' => true), 'JHtmlBehavior::switcher' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the combobox method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCombobox()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::combobox();
		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::combobox' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Data for the testTooltip method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getTooltipData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTooltip', array()))) => true
					),
				),
			),
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTooltip2', array()))) => true
					),
				),
				'.hasTooltip2'
			),
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTooltip2', array('showDelay' => 1000)))) => true
					),
				),
				'.hasTooltip2',
				array('showDelay' => 1000)
			),
		);

		return $data;
	}

	/**
	 * Tests the tooltip method.
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 * @param   array   $params    @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getTooltipData
	 */
	public function testTooltip($expected, $selector = '.hasTooltip', $params = array())
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::tooltip($selector, $params);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Data for the testModal method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getModalData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::modal' => array(
						md5(serialize(array('a.modal', array()))) => true
					)
				)
			),
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::modal' => array(
						md5(serialize(array('a.modal2', array()))) => true
					)
				),
				'a.modal2'
			),
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::modal' => array(
						md5(serialize(array('a.modal2', array('size' => 1000)))) => true
					)
				),
				'a.modal2',
				array('size' => 1000)
			)
		);

		return $data;
	}

	/**
	 * Tests the modal method.
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 * @param   array   $params    @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getModalData
	 */
	public function testModal($expected, $selector = 'a.modal', $params = array())
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::modal($selector, $params);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Data for the testMultiselect method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getMultiselectData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::multiselect' => array('adminForm' => true),
				)
			),
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::multiselect' => array('adminForm2' => true),
				),
				'adminForm2'
			),
		);

		return $data;
	}

	/**
	 * Tests the multiselect method.
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $id        @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getMultiselectData
	 */
	public function testMultiselect($expected, $id = 'adminForm')
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::multiselect($id);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Data for the testTree method
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getTreeData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::core' => true,
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::tree' => array('myid' => true)
				),
				'myid'
			),
		);

		return $data;
	}

	/**
	 * Tests the tree method.
	 *
	 * @param   string   $expected  @todo
	 * @param   integer  $id        @todo
	 * @param   array    $params    @todo
	 * @param   array    $root      @todo
	 *
	 * @return  void
	 *
	 * @since         3.1
	 * @dataProvider  getTreeData
	 */
	public function testTree($expected, $id, $params = array(), $root = array())
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::tree($id, $params, $root);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the calendar method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testCalendar()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::calendar();
		$this->assertEquals(
			array('JHtmlBehavior::calendar' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the colorpicker method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testColorpicker()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::colorpicker();
		$this->assertEquals(
			array('JHtmlBehavior::colorpicker' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the keepalive method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testKeepalive()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not) to return a value from getTemplate
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->willReturn($template);

		JHtmlBehaviorInspector::keepalive();
		$this->assertEquals(
			array('JHtmlBehavior::keepalive' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Tests the noframes method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testNoFrames()
	{
		// We generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// We create a stub (not a mock because we don't enforce whether it is called or not)
		// to return a value from getTemplate
		$mock = $this->getMock('myMockObject', array('getTemplate', 'setHeader'));
		$mock->expects($this->any())
			->method('getTemplate')
			->will($this->returnValue($template));

		// @todo We need to mock this.
		$mock->input = new JInput;

		JFactory::$application = $mock;

		JHtmlBehaviorInspector::noframes();
		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::noframes' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}
}
