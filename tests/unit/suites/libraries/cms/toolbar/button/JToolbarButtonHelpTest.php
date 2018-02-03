<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JToolbarButtonHelp.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarButtonHelpTest extends TestCaseDatabase
{
	/**
	 * Toolbar object
	 *
	 * @var    JToolbar
	 * @since  3.0
	 */
	protected $toolbar;

	/**
	 * Object under test
	 *
	 * @var    JToolbarButtonHelp
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
		$this->object  = $this->toolbar->loadButtonType('help');

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

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
	 * @since   3.2
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;
		unset($this->backupServer, $this->toolbar, $this->object);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the fetchButton method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testFetchButton()
	{
		$html = "<button id=\"toolbar-help\" onclick=\"Joomla.popupWindow('help/en-GB/JHELP_CONTENT_ARTICLE_MANAGER.html', 'JHELP', 700, 500, 1)\" rel=\"help\" class=\"btn btn-outline-info btn-sm\">\n"
			. "\t<span class=\"fa fa-question\" aria-hidden=\"true\"></span>\n"
			. "\tJTOOLBAR_HELP</button>\n";

		$this->assertEquals(
			$html,
			$this->object->fetchButton('Help', 'JHELP_CONTENT_ARTICLE_MANAGER')
		);
	}

	/**
	 * Tests the fetchId method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testFetchId()
	{
		$this->assertThat(
			$this->object->fetchId(),
			$this->equalTo('toolbar-help')
		);
	}
}
