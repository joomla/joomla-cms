<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_ADMINISTRATOR . '/includes/helper.php';

/**
 * Test class for JAdministratorHelper.
 */
class JAdministratorHelperTest extends TestCase
{
	/**
	 * @var JAdministratorHelper
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		//$this->object = new JErrorPage;
		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$application->input = new JInput(array());
		$this->user = $this->getMock('Observer', array('get', 'authorise'));

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
	}

	/**
	 * Tests the findOption() method simulating a guest.
	 */
	public function testFindOptionGuest()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->will($this->returnValue(true));
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
	 * Tests the findOption() method simulating an user without login admin permissions.
	 */
	public function testFindOptionCanNotLoginAdmin()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->will($this->returnValue(false));
		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->will($this->returnValue(false));

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
	 * Tests the findOption() method simulating an user who is able to log in to admin.
	 */
	public function testFindOptionCanLoginAdmin()
	{
		$this->user->expects($this->once())
			->method('get')
			->with($this->equalTo('guest'))
			->will($this->returnValue(false));
		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->will($this->returnValue(true));

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
			->will($this->returnValue(false));
		$this->user->expects($this->once())
			->method('authorise')
			->with($this->equalTo('core.login.admin'))
			->will($this->returnValue(true));

		JFactory::$application->input->set('option', 'foo');

		$this->assertEquals(
			'foo',
			JAdministratorHelper::findOption()
		);

		$this->assertEquals(
			'foo',
			JFactory::$application->input->get('option')
		);
	}
}
