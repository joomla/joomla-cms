<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
		JFactory::$session = $this->getMockSession();

		// We generate a random template name so that we don't collide or hit anything
		JFactory::$application->expects($this->any())
			->method('getTemplate')
			->willReturn('mytemplate' . mt_rand(1, 10000));

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
		unset($this->backupServer);
		$this->restoreFactoryState();

		parent::tearDown();
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
		return array(
			array(array('JHtmlBehavior::caption' => array('img.caption' => true))),
			array(array('JHtmlBehavior::caption' => array('img.caption2' => true)), 'img.caption2'),
		);
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
		JHtmlBehavior::caption($selector);

		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded(),
			'The caption behavior is not loaded with all expected dependencies'
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
		JHtmlBehavior::formvalidation();

		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::formvalidator' => true),
			JHtmlBehaviorInspector::getLoaded(),
			'The form validation behavior is not loaded with all dependencies'
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
		JHtmlBehavior::combobox();

		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::combobox' => true),
			JHtmlBehaviorInspector::getLoaded(),
			'The combobox behavior is not loaded with all dependencies'
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
		JHtmlBehavior::multiselect($id);

		$this->assertEquals(
			$expected,
			JHtmlBehaviorInspector::getLoaded(),
			'The multiselect behavior is not loaded with all dependencies'
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
		JHtmlBehavior::colorpicker();

		$this->assertEquals(
			array('JHtmlBehavior::colorpicker' => true),
			JHtmlBehaviorInspector::getLoaded(),
			'The colorpicker behavior is not loaded with all dependencies'
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
		JHtmlBehavior::keepalive();

		$this->assertEquals(
			array(
				'JHtmlBehavior::keepalive' => true,
				'JHtmlBehavior::core'      => true
			),
			JHtmlBehaviorInspector::getLoaded(),
			'The keepalive behavior is not loaded with all dependencies'
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
		JHtmlBehavior::noframes();

		$this->assertEquals(
			array('JHtmlBehavior::core' => true, 'JHtmlBehavior::noframes' => true),
			JHtmlBehaviorInspector::getLoaded(),
			'The no frames behavior is not loaded with all dependencies'
		);
	}
}
