<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Html
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/html/html/behavior.php';

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
	 * @return  void
	 *
	 * @dataProvider  getFrameworkData
	 *
	 * @since   11.3
	 */
	public function testFramework($expected, $extras = false, $debug = null)
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * @return  void
	 *
	 * @dataProvider  getCaptionData
	 *
	 * @since   11.3
	 */
	public function testCaption($expected, $selector = 'img.caption')
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * @return  void
	 *
	 * @dataProvider  getTooltipData
	 *
	 * @since   11.3
	 */
	public function testTooltip($expected, $selector = '.hasTip', $params = array())
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * @return  void
	 *
	 * @dataProvider  getModalData
	 *
	 * @since   11.3
	 */
	public function testModal($expected, $selector = 'a.modal', $params = array())
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * @return  void
	 *
	 * @dataProvider  getMultiselectData
	 *
	 * @since   11.3
	 */
	public function testMultiselect($expected, $id = 'adminForm')
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * @return  void
	 *
	 * @dataProvider  getUploaderData
	 *
	 * @since   11.3
	 */
	public function testUploader($expected, $id = 'file-upload', $params = array(), $upload_queue = 'upload-queue')
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * testTree().
	 *
	 * @return  void
	 *
	 * @dataProvider  getTreeData
	 *
	 * @since   11.3
	 */
	public function testTree($expected, $id, $params = array(), $root = array())
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * testCalendar().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCalendar()
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * testColorpicker().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testColorpicker()
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * testKeepalive().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testKeepalive()
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
	 * testNoFrames().
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testNoFrames()
	{
		// we generate a random template name so that we don't collide or hit anything//
		$template = 'mytemplate' . rand(1, 10000);

		// we create a stub (not a mock because we don't enforce whether it is called or not)
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
