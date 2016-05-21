<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JToolbarButton.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarButtonTest extends TestCaseDatabase
{
	/**
	 * @var    JToolbar
	 * @since  3.0
	 */
	protected $toolbar;

	/**
	 * Since JToolbarButton is abstract, test that class with a child class
	 *
	 * @var    JToolbarButtonStandard
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->toolbar = JToolbar::getInstance();
		$this->object  = $this->toolbar->loadButtonType('standard');

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

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
	 * Tests the constructor
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function test__construct()
	{
		$this->assertThat(
			new JToolbarButtonStandard($this->toolbar),
			$this->isInstanceOf('JToolbarButton')
		);
	}

	/**
	 * Tests the getName method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetName()
	{
		$this->assertThat(
			$this->object->getName(),
			$this->equalTo('Standard')
		);
	}

	/**
	 * Tests the render method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRender()
	{
		$type = array('Standard', 'test');

		$expected = "<div class=\"btn-wrapper\"  id=\"toolbar-test\">" . PHP_EOL
			. "\t<button onclick=\"if (document.adminForm.boxchecked.value==0){alert('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');}else{ Joomla.submitbutton('')}\" class=\"btn btn-small\">" . PHP_EOL
			. "\t<span class=\"icon-test\"></span>" . PHP_EOL
			. "\t</button>" . PHP_EOL
			. "</div>" . PHP_EOL;

		$this->assertEquals(
			$expected,
			$this->object->render($type)
		);
	}

	/**
	 * Tests the fetchIconClass method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testFetchIconClass()
	{
		$this->assertThat(
			$this->object->fetchIconClass('standard'),
			$this->equalTo('icon-standard')
		);
	}
}
