<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/JHtmlJqueryInspector.php';

/**
 * Test class for JHtmlJquery.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       3.1
 */
class JHtmlJqueryTest extends TestCase
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
		JHtmlJqueryInspector::resetLoaded();

		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$config = $this->getMockConfig();
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
		unset($this->backupServer);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the framework method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testFramework()
	{
		// Initialise the Bootstrap JS framework
		JHtmlJquery::framework(true, '', true);

		// Get the document instance
		$document = JFactory::getDocument();

		$this->assertArrayHasKey(
			'/media/vendor/jquery/js/jquery.min.js',
			$document->_scripts,
			'Verify that the jQuery JS is loaded'
		);

		$this->assertArrayHasKey(
			'/media/vendor/jquery/js/jquery-migrate.min.js',
			$document->_scripts,
			'Verify that the jQuery Migrate JS is loaded'
		);
	}

	/**
	 * Tests the ui method.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function testUi()
	{
		// Initialise the Bootstrap JS framework
		JHtmlJquery::ui(array('core', 'sortable'));

		// Get the document instance
		$document = JFactory::getDocument();

		$this->assertArrayHasKey(
			'/media/vendor/jquery/js/jquery.min.js',
			$document->_scripts,
			'Verify that the jQuery JS is loaded as well'
		);

		$this->assertArrayHasKey(
			'/media/vendor/jquery-ui/js/jquery.ui.sortable.min.js',
			$document->_scripts,
			'Verify that the jQueryUI sortable script is loaded'
		);
	}
}
