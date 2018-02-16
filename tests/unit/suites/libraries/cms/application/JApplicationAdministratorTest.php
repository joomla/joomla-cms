<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

/**
 * Test class for JApplicationAdministrator.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       3.2
 */
class JApplicationAdministratorTest extends TestCaseDatabase
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
	 * @var    \Joomla\CMS\Application\AdministratorApplication
	 * @since  3.2
	 */
	protected $class;

	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $backupServer;

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
		$config = new Registry;
		$config->set('session', false);

		// Get a new JApplicationAdministrator instance.
		$this->class = new \Joomla\CMS\Application\AdministratorApplication($this->getMockInput(), $config);
		$this->class->setSession(JFactory::$session);
		$this->class->setDispatcher($this->getMockDispatcher());
		TestReflection::setValue('JApplicationCms', 'instances', array('administrator' => $this->class));

		JFactory::$application = $this->class;
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.2
	 */
	protected function tearDown()
	{
		// Reset the application instance.
		TestReflection::setValue('JApplicationCms', 'instances', array());
		TestReflection::setValue('JPluginHelper', 'plugins', null);

		$_SERVER = $this->backupServer;
		unset($this->backupServer, $config, $this->class);
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  \PHPUnit\DbUnit\DataSet\CsvDataSet
	 *
	 * @since   3.2
	 */
	protected function getDataSet()
	{
		$dataSet = new \PHPUnit\DbUnit\DataSet\CsvDataSet(',', "'", '\\');

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
	 * @covers  JApplicationAdministrator::getClientId
	 */
	public function testGetClientId()
	{
		$this->assertSame(1, $this->class->getClientId());
	}

	/**
	 * Tests the JApplicationCms::getName method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::getName
	 */
	public function testGetName()
	{
		$this->assertSame('administrator', $this->class->getName());
	}

	/**
	 * Tests the JApplicationCms::getMenu method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::getMenu
	 */
	public function testGetMenu()
	{
		$this->assertInstanceOf('JMenuAdministrator', $this->class->getMenu());
	}

	/**
	 * Tests the JApplicationCms::getPathway method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 *
	 * @expectedException  RuntimeException
	 * @covers  JApplicationAdministrator::getPathway
	 */
	public function testGetPathway()
	{
		$this->class->getPathway();
	}

	/**
	 * Tests the JApplicationAdministrator::getRouter method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::getRouter
	 */
	public function testGetRouter()
	{
		$this->assertInstanceOf('JRouterAdministrator', JApplicationAdministrator::getRouter());
	}

	/**
	 * Tests the JApplicationAdministrator::getTemplate method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::getTemplate
	 */
	public function testGetTemplate()
	{
		$this->markTestSkipped('Test fails due to JPATH_THEMES being defined for site.');

		$template = $this->class->getTemplate(true);

		$this->assertInstanceOf('\\Joomla\\Registry\\Registry', $template->params);

		$this->assertEquals('atum', $template->template);
	}

	/**
	 * Tests the JApplicationCms::isAdmin method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::isAdmin
	 */
	public function testIsAdmin()
	{
		$this->assertTrue($this->class->isAdmin());
	}

	/**
	 * Tests the JApplicationCms::isSite method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::isSite
	 */
	public function testIsSite()
	{
		$this->assertFalse($this->class->isSite());
	}

	/**
	 * Tests the JApplicationCms::isClient method.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 * @covers  JApplicationAdministrator::isClient
	 */
	public function testIsClient()
	{
		$this->assertTrue($this->class->isClient('administrator'));
		$this->assertFalse($this->class->isClient('site'));
	}

	/**
	 * Tests the JApplicationCms::render method.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @covers  JApplicationAdministrator::render
	 */
	public function testRender()
	{
		$document = $this->getMockDocument();

		$this->assignMockReturns($document, array('render' => 'JWeb Body'));
		$this->assignMockReturns($this->class->getDispatcher(), array('dispatch' => new Joomla\Event\Event('test')));

		// Manually inject the document.
		TestReflection::setValue($this->class, 'document', $document);

		TestReflection::invoke($this->class, 'render');

		$this->assertEquals('JWeb Body', (string) $this->class->getResponse()->getBody());
	}

	/**
	 * Tests the findOption() method simulating a guest.
	 */
	public function testFindOptionGuest()
	{
		$user = $this->createMock('JUser', array('get', 'authorise'));
		$user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(true);
		$user->expects($this->never())
			->method('authorise');
		$this->class->loadIdentity($user);
		$this->assertEquals(
			'com_login',
			$this->class->findOption()
		);
		$this->assertEquals(
			'com_login',
			$this->class->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating an user without login admin permissions.
	 */
	public function testFindOptionCanNotLoginAdmin()
	{
		$user = $this->getMockBuilder('JUser')
			->setMethods(array('get', 'authorise'))
			->getMock();
		$user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);
		$user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(false);
		$this->class->loadIdentity($user);
		$this->assertEquals(
			'com_login',
			$this->class->findOption()
		);
		$this->assertEquals(
			'com_login',
			$this->class->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating an user who is able to log in to admin.
	 */
	public function testFindOptionCanLoginAdmin()
	{
		$user = $this->getMockBuilder('JUser')
			->setMethods(array('get', 'authorise'))
			->getMock();
		$user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);
		$user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(true);
		$this->class->loadIdentity($user);
		$this->assertEquals(
			'com_cpanel',
			$this->class->findOption()
		);
		$this->assertEquals(
			'com_cpanel',
			$this->class->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating the option at a special value.
	 */
	public function testFindOptionCanLoginAdminOptionSet()
	{
		$user = $this->getMockBuilder('JUser')
			->setMethods(array('get', 'authorise'))
			->getMock();
		$user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);
		$user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(false);
		$this->class->loadIdentity($user);
		$this->class->input->set('option', 'foo');
		$this->assertEquals(
			'com_login',
			$this->class->findOption()
		);
		$this->assertEquals(
			'com_login',
			JFactory::$application->input->get('option')
		);
	}
}
