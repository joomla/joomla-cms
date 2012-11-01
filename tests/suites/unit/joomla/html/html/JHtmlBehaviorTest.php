<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Html
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/html/behavior.php';

/**
 * Inspector class for JHtmlBehavior.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 *
 * @since       11.3
 */
class JHtmlBehaviorInspector extends JHtmlBehavior
{
	/**
	 * Method for resetting the loaded files.
	 *
	 * @return  mixed  void.
	 *
	 * @since   11.3
	 */
	public static function resetLoaded()
	{
		self::$loaded = array();
	}

	/**
	 * Method for inspecting protected variables.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   11.3
	 */
	public static function getLoaded()
	{
		return self::$loaded;
	}
}

/**
 * Test class for JHtmlBehavior.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 *
 * @since       11.1
 */
class JHtmlBehaviorTest extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();
		$_SERVER['HTTP_HOST'] = 'example.com';
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
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getFrameworkData()
	{
		$data = array(
			array(array('JHtmlBehavior::framework' => array('core' => true))),
			array(array('JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true),
			array(array('JHtmlBehavior::framework' => array('core' => true)), false, false),
			array(array('JHtmlBehavior::framework' => array('core' => true)), false, true),
			array(array('JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true, false),
			array(array('JHtmlBehavior::framework' => array('core' => true, 'more' => true)), true, true)
		);

		return $data;
	}

	/**
	 * testFramework().
	 *
	 * @param   string   $expected  @todo
	 * @param   boolean  $extras    @todo
	 * @param   boolean  $debug     @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getFrameworkData
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::framework($extras, $debug);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getCaptionData()
	{
		$data = array(
			array(array('JHtmlBehavior::caption' => array('img.caption' => true), 'JHtmlBehavior::framework' => array('core' => true))),
			array(array('JHtmlBehavior::caption' => array('img.caption2' => true), 'JHtmlBehavior::framework' => array('core' => true)), 'img.caption2'),
		);

		return $data;
	}

	/**
	 * testCaption().
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getCaptionData
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::caption($selector);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * testFormvalidation().
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::formvalidation();
		$this->assertEquals(
			array('JHtmlBehavior::framework' => array('core' => true), 'JHtmlBehavior::formvalidation' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * testSwitcher().
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::switcher();
		$this->assertEquals(
			array('JHtmlBehavior::framework' => array('core' => true), 'JHtmlBehavior::switcher' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * testCombobox().
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::combobox();
		$this->assertEquals(
			array('JHtmlBehavior::framework' => array('core' => true), 'JHtmlBehavior::combobox' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getTooltipData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTip', array()))) => true
					),
				),
			),
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTip2', array()))) => true
					),
				),
				'.hasTip2'
			),
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::tooltip' => array(
						md5(serialize(array('.hasTip2', array('showDelay' => 1000)))) => true
					),
				),
				'.hasTip2',
				array('showDelay' => 1000)
			),
		);

		return $data;
	}

	/**
	 * testTooltip().
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 * @param   array   $params    @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getTooltipData
	 *
	 * @since   11.3
	 */
	public function testTooltip($expected, $selector = '.hasTip', $params = array())
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::tooltip($selector, $params);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getModalData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::modal' => array(
						md5(serialize(array('a.modal', array()))) => true
					)
				)
			),
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true, 'more' => true),
					'JHtmlBehavior::modal' => array(
						md5(serialize(array('a.modal2', array()))) => true
					)
				),
				'a.modal2'
			),
			array(
				array(
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
	 * testModal().
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $selector  @todo
	 * @param   array   $params    @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getModalData
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::modal($selector, $params);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test...
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getMultiselectData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::multiselect' => array('adminForm' => true),
				)
			),
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::multiselect' => array('adminForm2' => true),
				),
				'adminForm2'
			),
		);

		return $data;
	}

	/**
	 * testMultiselect().
	 *
	 * @param   string  $expected  @todo
	 * @param   string  $id        @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getMultiselectData
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::multiselect($id);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test getUploaderData
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getUploaderData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::uploader' => array('file-upload' => true),
				)
			),
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::uploader' => array('file-upload2' => true),
				),
				'file-upload2'
			),
		);

		return $data;
	}

	/**
	 * testUploader().
	 *
	 * @param   string  $expected      @todo
	 * @param   string  $id            @todo
	 * @param   array   $params        @todo
	 * @param   string  $upload_queue  @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getUploaderData
	 *
	 * @since   11.3
	 */
	public function testUploader($expected, $id = 'file-upload', $params = array(), $upload_queue = 'upload-queue')
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::uploader($id, $params, $upload_queue);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test getTreeData
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getTreeData()
	{
		$data = array(
			array(
				array(
					'JHtmlBehavior::framework' => array('core' => true),
					'JHtmlBehavior::tree' => array('myid' => true)
				),
				'myid'
			),
		);

		return $data;
	}

	/**
	 * Test Tree().
	 *
	 * @param   string   $expected  @todo
	 * @param   integer  $id        @todo
	 * @param   array    $params    @todo
	 * @param   array    $root      @todo
	 *
	 * @return  void
	 *
	 * @dataProvider  getTreeData
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::tree($id, $params, $root);
		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test Calendar().
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::calendar();
		$this->assertEquals(
			array('JHtmlBehavior::calendar' => true),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test Colorpicker().
	 *
	 * @return  void
	 *
	 * @since   11.3
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::colorpicker();
		$this->assertEquals(
			array('JHtmlBehavior::colorpicker' => true, 'JHtmlBehavior::framework' => array('core' => true, 'more' => true)),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test Keepalive().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testKeepalive()
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::keepalive();
		$this->assertEquals(
			array('JHtmlBehavior::keepalive' => true, 'JHtmlBehavior::framework' => array('core' => true)),
			JHtmlBehaviorInspector::getLoaded()
		);
	}

	/**
	 * Test NoFrames().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testNoFrames()
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

		JHtmlBehaviorInspector::resetLoaded();
		JHtmlBehaviorInspector::noframes();
		$this->assertEquals(
			array('JHtmlBehavior::noframes' => true, 'JHtmlBehavior::framework' => array('core' => true)),
			JHtmlBehaviorInspector::getLoaded()
		);
	}
}
