<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplicationSite.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.2
 */
class JApplicationSiteTest extends TestCaseDatabase
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
	 * An instance of the class to test.
	 *
	 * @var    JApplicationSite
	 * @since  3.2
	 */
	protected $class;

	/**
	 * Data for fetchConfigurationData method.
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public function getRedirectData()
	{
		return array(
			// Note: url, base, request, (expected result)
			array('/foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=3.2', 'http://mydomain.com/foo'),
			array('foo', 'http://mydomain.com/', 'http://mydomain.com/index.php?v=3.2', 'http://mydomain.com/foo'),
		);
	}

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

		$_SERVER['HTTP_HOST'] = self::TEST_HTTP_HOST;
		$_SERVER['HTTP_USER_AGENT'] = self::TEST_USER_AGENT;
		$_SERVER['REQUEST_URI'] = self::TEST_REQUEST_URI;
		$_SERVER['SCRIPT_NAME'] = '/index.php';

		// Set the config for the app
		$config = new JRegistry;
		$config->set('session', false);

		// Get a new JApplicationSite instance.
		$this->class = new JApplicationSite(null, $config);
		TestReflection::setValue('JApplicationCms', 'instances', array('site' => $this->class));

		// We are coupled to Document and Language in JFactory.
		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		//JFactory::$language = $this->getMockLanguage();
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
		// Reset the dispatcher and application instances.
		TestReflection::setValue('JEventDispatcher', 'instance', null);
		TestReflection::setValue('JApplicationCms', 'instances', array());

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', "'", '\\');

		$dataSet->addTable('jos_extensions', JPATH_TEST_DATABASE . '/jos_extensions.csv');
		$dataSet->addTable('jos_menu', JPATH_TEST_DATABASE . '/jos_menu.csv');
		$dataSet->addTable('jos_menu_types', JPATH_TEST_DATABASE . '/jos_menu_types.csv');
		$dataSet->addTable('jos_template_styles', JPATH_TEST_DATABASE . '/jos_template_styles.csv');
		$dataSet->addTable('jos_usergroups', JPATH_TEST_DATABASE . '/jos_usergroups.csv');
		$dataSet->addTable('jos_users', JPATH_TEST_DATABASE . '/jos_users.csv');
		$dataSet->addTable('jos_viewlevels', JPATH_TEST_DATABASE . '/jos_viewlevels.csv');

		return $dataSet;
	}

	/**
	 * Tests the JApplicationCms::getClientId method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetClientId()
	{
		$this->assertEquals(
			$this->class->getClientId(),
			0
		);
	}

	/**
	 * Tests the JApplicationCms::getName method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetName()
	{
		$this->assertEquals(
			$this->class->getName(),
			'site'
		);
	}

	/**
	 * Tests the JApplicationCms::getMenu method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetMenu()
	{
		$this->assertThat(
			$this->class->getMenu(),
			$this->isInstanceOf('JMenuSite')
		);
	}

	/**
	 * Tests the JApplicationSite::getParams method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetParams()
	{
		JFactory::$application = $this->class;
		$this->class->loadLanguage();
		$params = $this->class->getParams('com_content');

		$this->assertEquals(
			$params->get('show_item_navigation'),
			'1',
			'com_content show_item_navigation defaults to 1'
		);
	}

	/**
	 * Tests the JApplicationCms::getPathway method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetPathway()
	{
		$this->assertThat(
			$this->class->getPathway(),
			$this->isInstanceOf('JPathwaySite')
		);
	}

	/**
	 * Tests the JApplicationSite::getRouter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetRouter()
	{
		$this->assertThat(
			$this->class->getRouter(),
			$this->isInstanceOf('JRouterSite')
		);
	}

	/**
	 * Tests the JApplicationSite::getTemplate method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTemplate()
	{
		$template = $this->class->getTemplate(true);

		$this->assertThat(
			$template->params,
			$this->isInstanceOf('JRegistry')
		);

		$this->assertThat(
			$template->template,
			$this->equalTo('protostar')
		);
	}

	/**
	 * Tests the JApplicationCms::isAdmin method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsAdmin()
	{
		$this->assertThat(
			$this->class->isAdmin(),
			$this->isFalse(),
			'JApplicationAdministrator is not an admin app'
		);
	}

	/**
	 * Tests the JApplicationCms::isSite method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testIsSite()
	{
		$this->assertThat(
			$this->class->isSite(),
			$this->isTrue(),
			'JApplicationAdministrator is a site app'
		);
	}

	/**
	 * Tests the JApplicationCms::render method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testRender()
	{
		JFactory::$application = $this->class;

		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));

		// Manually inject the document.
		TestReflection::setValue($this->class, 'document', $document);

		TestReflection::invoke($this->class, 'render');

		$this->assertThat(
			TestReflection::getValue($this->class, 'response')->body,
			$this->equalTo(
				array('JWeb Body')
			)
		);
	}

	/**
	 * Tests the JApplicationSite::setDetectBrowser and getDetectBrowser methods.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testSetGetDetectBrowser()
	{
		$this->assertFalse(
			$this->class->setDetectBrowser(true),
			'setDetectBrowser should return the previous state.'
		);

		$this->assertTrue(
			$this->class->getDetectBrowser(),
			'setDetectBrowser should return the new state.'
		);
	}

	/**
	 * Tests the JApplicationSite::setLanguageFilter and getLanguageFilter methods.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testSetGetLanguageFilter()
	{
		$this->assertFalse(
			$this->class->setLanguageFilter(true),
			'setLanguageFilter should return the previous state.'
		);

		$this->assertTrue(
			$this->class->getLanguageFilter(),
			'setLanguageFilter should return the new state.'
		);
	}

	/**
	 * Tests the JApplicationSite::setTemplate method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testSetTemplate()
	{
		$this->class->setTemplate('beez3');

		$template = $this->class->getTemplate(true);

		$this->assertThat(
			$template->params,
			$this->isInstanceOf('JRegistry')
		);

		$this->assertThat(
			$template->template,
			$this->equalTo('beez3')
		);
	}
}
