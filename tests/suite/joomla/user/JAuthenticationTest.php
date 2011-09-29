<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  User
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

require_once JPATH_PLATFORM.'/joomla/user/authentication.php';
require_once JPATH_TESTS.'/suite/joomla/event/JDispatcherInspector.php';
require_once JPATH_TESTS.'/suite/joomla/plugin/JPluginHelperInspector.php';

/**
 * Tests for the JAuthentication class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Utilities
 * @since       11.1
 *
 * @runInSeparateProcess
 */
class JAuthenticationTest extends JoomlaTestCase
{
	/**
	 * @var	   JAuthentication
	 * @since  11.1
	 */
	protected $object;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();

		// Mock the event dispatcher.
		$dispatcher = $this->getMockDispatcher(false);
		$this->assignMockCallbacks(
			$dispatcher,
			array(
				'trigger' => array(get_called_class(), 'mockTrigger'),
			)
		);

		// Inject the mock dispatcher into the JDispatcher singleton.
		JDispatcherInspector::setInstance($dispatcher);

		// Mock the authentication plugin
		require_once __DIR__.'/TestStubs/FakeAuthenticationPlugin.php';

		// Inject the mocked plugin list.
		JPluginHelperInspector::setPlugins(
			array(
				(object) array(
					'type' => 'authentication',
					'name' => 'fake'
				)
			)
		);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		// Reset the dispatcher instance.
		JDispatcherInspector::setInstance(null);

		// Reset the loaded plugins.
		JPluginHelperInspector::setPlugins(null);

		parent::tearDown();
	}

	/**
	 * Callback for the JDispatcher trigger method.
	 *
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since  11.3
	 */
	public function mockTrigger($event, $args = array())
	{
		switch ($event)
		{
			case 'onUserAuthorisation':
				// Emulate onUserAuthorisation($response, $options=array())
				$returnValue = new JAuthenticationResponse;

				switch($args[0]->username)
				{
					case 'test':
						$returnValue->status = JAuthentication::STATUS_SUCCESS;
						break;

					case 'expired':
						$returnValue->status = JAuthentication::STATUS_EXPIRED;
						break;

					case 'denied':
						$returnValue->status = JAuthentication::STATUS_DENIED;
						break;

					default:
						$returnValue->status = JAuthentication::STATUS_UNKNOWN;
						break;
				}

				return array($returnValue);

				break;

		}
	}

	/**
	 * Data cases for testAuthentication.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesAuthentication()
	{
		// Successful authentication from the FakeAuthenticationPlugin
		$success = new JAuthenticationResponse;
		$success->status = JAuthentication::STATUS_SUCCESS;
		$success->type = 'fake';
		$success->username = 'test';
		$success->password = 'test';
		$success->fullname = 'test';

		// Failed authentication
		$failure = new JAuthenticationResponse;
		$failure->status = JAuthentication::STATUS_FAILURE;
		$failure->username = 'test';
		$failure->password = 'wrongpassword';
		$failure->fullname = 'test';

		return array(
			array(
				array('username'=>'test', 'password'=>'test'),
				$success,
				'Testing correct username and password'
			),
			array(
				array('username' => 'test', 'password' => 'wrongpassword'),
				$failure,
				'Testing incorrect username and password'
			)
		);
	}

	/**
	 * This checks for the correct Long Version.
	 *
	 * @return  void
	 *
	 * @dataProvider casesAuthentication
	 * @since   11.1
	 */
	public function testAuthentication($input, $expect, $message)
	{
		$authenticate = JAuthentication::getInstance();
		$this->assertEquals(
			$expect,
			$authenticate->authenticate($input),
			$message
		);
	}

	/**
	 * These are the authorisation test cases
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function casesAuthorise()
	{
		$cases = Array();
		$expect = new JAuthenticationResponse;
		$response = new JAuthenticationResponse;

		$response->username = 'test';
		$expect->status = JAuthentication::STATUS_SUCCESS;

		$cases[] = Array(
			clone($response),
			Array(clone($expect)),
			'Successful login'
		);

		$response->username = 'denied';
		$expect->status = JAuthentication::STATUS_DENIED;

		$cases[] = Array(
			clone($response),
			Array(clone($expect)),
			'Denied (blocked) login'
		);

		$response->username = 'expired';
		$expect->status = JAuthentication::STATUS_EXPIRED;

		$cases[] = Array(
			clone($response),
			Array(clone($expect)),
			'Expired login'
		);

		$response->username = 'unknown';
		$expect->status = JAuthentication::STATUS_UNKNOWN;

		$cases[] = Array(
			clone($response),
			Array(clone($expect)),
			'Unknown login'
		);

		return $cases;
	}

	/**
	 * This checks for the correct response to authorising a user
	 *
	 * @return  void
	 *
	 * @dataProvider casesAuthorise
	 * @since   11.1
	 */
	public function testAuthorise($input, $expect, $message)
	{
		$authentication = JAuthentication::getInstance();
		$this->assertEquals(
			$expect,
			$authentication->authorise($input),
			$message
		);
	}
}
