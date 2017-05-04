<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_ADMINISTRATOR . '/includes/helper.php';

/**
 * Test class for JAdministratorHelper.
 */
class JAdministratorHelperTest extends TestCase
{
	/**
	 * @var  JAdministratorHelper
	 */
	protected $object;

	/**
	 * @var  PHPUnit_Framework_MockObject_MockObject
	 */
	protected $user;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		//$this->object = new JErrorPage;
		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$application->input = $this->getMockInput();
		$this->user = $this->getMockBuilder('JUser')->setMethods(array('get', 'authorise'))->getMock();

		JFactory::$application->expects($this->once())
			->method('getIdentity')
			->will($this->returnValue($this->user));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		unset($this->user);
	}

	/**
	 * Tests the findOption() method simulating a guest.
	 */
	public function testFindOptionGuest()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(true);

		$this->user->expects($this->never())
			->method('authorise');

		$this->assertEquals(
			'com_login',
			JAdministratorHelper::findOption()
		);

		$this->assertEquals(
			'com_login',
			JFactory::$application->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating a user without login admin permissions.
	 */
	public function testFindOptionCanNotLoginAdmin()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);

		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(false);

		$this->assertEquals(
			'com_login',
			JAdministratorHelper::findOption()
		);

		$this->assertEquals(
			'com_login',
			JFactory::$application->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating a user who is able to log in to admin.
	 */
	public function testFindOptionCanLoginAdmin()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);

		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(true);

		$this->assertEquals(
			'com_cpanel',
			JAdministratorHelper::findOption()
		);

		$this->assertEquals(
			'com_cpanel',
			JFactory::$application->input->get('option')
		);
	}

	/**
	 * Tests the findOption() method simulating the option at a special value.
	 */
	public function testFindOptionCanLoginAdminOptionSet()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->willReturn(false);

		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->willReturn(false);

		JFactory::$application->input->set('option', 'foo');

		$this->assertEquals(
			'com_login',
			JAdministratorHelper::findOption()
		);

		$this->assertEquals(
			'com_login',
			JFactory::$application->input->get('option')
		);
	}
}
