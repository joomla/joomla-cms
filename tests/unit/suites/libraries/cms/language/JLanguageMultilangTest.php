<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Language
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

/**
 * Test class for JLanguageMultiLang.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Language
 * @since       3.4
 */
class JLanguageMultiLangTest extends TestCaseDatabase
{
	/**
	 * Value for test host.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_HTTP_HOST = 'mydomain.com';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_USER_AGENT = 'Mozilla/5.0';

	/**
	 * Value for test user agent.
	 *
	 * @var    string
	 * @since  3.2
	 */
	const TEST_REQUEST_URI = '/index.php';

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $backupServer;

	/**
	 * Config to be injected into the Application object
	 *
	 * @var    Registry
	 * @since  3.4
	 */
	protected $config;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		JFactory::$language = $this->getMockLanguage();
		JFactory::$session  = $this->getMockSession();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Set the config for the app
		$this->config = new Registry;
		$this->config->set('session', false);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.2
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		TestReflection::setValue('JEventDispatcher', 'instance', null);

		$_SERVER = $this->backupServer;
		$this->restoreFactoryState();

		parent::tearDown();
	}


	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.4
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');

		return $dataSet;
	}

	/**
	 * @testdox  Ensure isEnabled() proxies correctly to JApplicationSite
	 *
	 * @covers   JLanguageMultiLang::isEnabled
	 * @uses     JApplicationSite
	 */
	public function testIsEnabledWithSiteApp()
	{
		JFactory::$application = new JApplicationSite($this->getMockInput(), $this->config);

		$this->assertFalse(
			JLanguageMultilang::isEnabled()
		);
	}

	/**
	 * @testdox  Ensure isEnabled() database query works correctly
	 *
	 * @covers   JLanguageMultiLang::isEnabled
	 * @uses     JApplicationAdministrator
	 */
	public function testIsEnabledWithAdminApp()
	{
		JFactory::$application = new JApplicationAdministrator($this->getMockInput(), $this->config);

		$this->assertFalse(
			JLanguageMultilang::isEnabled()
		);
	}
}
