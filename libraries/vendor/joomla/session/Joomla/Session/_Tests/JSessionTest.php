<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Test\TestHelper;

/**
 * Test class for JSession.
 *
 * @since  1.0
 */
class JSessionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var  JSession
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		if (!class_exists('JSession'))
		{
			$this->markTestSkipped(
				'The JSession class does not exist.');
		}

		// TODO: This code logic appears useless, many missing methods are being called.  Review.
		parent::setUp();
		$this->saveFactoryState();
		$this->object = JSession::getInstance('none', array('expire' => 20, 'force_ssl' => true, 'name' => 'name', 'id' => 'id', 'security' => 'security'));
		$this->input = new JInput;
		$this->input->cookie = $this->getMock('JInputCookie', array('set', 'get'));
		$this->object->initialise($this->input);
		$this->input->cookie->expects($this->any())
			->method('set');
		$this->input->cookie->expects($this->any())
			->method('get')
			->will($this->returnValue(null));
		$this->object->start();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		// Only tear down if JSession exists, avoids aborting Unit Testing due to missing methods.
		if (class_exists('JSession'))
		{
			if (session_id())
			{
				session_unset();
				session_destroy();
			}

			$this->restoreFactoryState();
		}
	}

	/**
	 * Test cases for getInstance
	 * string    handler of type JSessionStorage: none or database
	 * array    arguments for $options in form of associative array
	 * string    message if test case fails
	 *
	 * @return array
	 */
	Public function casesGetInstance()
	{
		return array(
			'first_instance' => array(
				'none',
				array('expire' => 99),
				'Line: ' . __LINE__ . ': ' . 'Should not be a different instance and options should not change'
			),
			'second_instance' => array(
				'database',
				array(),
				'Line: ' . __LINE__ . ': ' . 'Should not be a different instance '
			)
		);
	}

	/**
	 * Test getInstance
	 *
	 * @param   string  $store    Type of storage for the session
	 * @param   array   $options  Optional parameters
	 *
	 * @dataProvider casesGetInstance
	 * @covers  JSession::getInstance
	 *
	 * @return void
	 */
	public function testGetInstance($store, $options)
	{
		$oldSession = $this->object;
		$newSession = JSession::getInstance($store, $options);

		// The properties and values should be identical to each other.
		$this->assertThat(
			$oldSession,
			$this->identicalTo($newSession)
		);

		// They should be the same object.
		$this->assertSame($oldSession, $newSession);
	}

	/**
	 * Test getState
	 *
	 * @covers  JSession::getState
	 *
	 * @return void
	 */
	public function testGetState()
	{
		$this->assertEquals(
			TestHelper::getValue($this->object, '_state'),
			$this->object->getState(),
			'Session state should be the same'
		);
	}

	/**
	 * Test getExpire()
	 *
	 * @covers  JSession::getExpire
	 *
	 * @return void
	 */
	public function testGetExpire()
	{
		$this->assertEquals(
			TestHelper::getValue($this->object, '_expire'),
			$this->object->getExpire(),
			'Session expire time should be the same'
		);
	}

	/**
	 * Test getToken
	 *
	 * @covers  JSession::getToken
	 *
	 * @return void
	 */
	public function testGetToken()
	{
		$this->object->set('session.token', 'abc');
		$this->assertEquals('abc', $this->object->getToken(), 'Token should be abc');

		$this->object->set('session.token', null);
		$token = $this->object->getToken();
		$this->assertEquals(32, strlen($token), 'Line: ' . __LINE__ . ' Token should be length 32');

		$token2 = $this->object->getToken(true);
		$this->assertNotEquals($token, $token2, 'Line: ' . __LINE__ . ' New token should be different');
	}

	/**
	 * Test hasToken
	 *
	 * @covers  JSession::hasToken
	 *
	 * @return void
	 */
	public function testHasToken()
	{
		$token = $this->object->getToken();
		$this->assertTrue($this->object->hasToken($token), 'Line: ' . __LINE__ . ' Correct token should be true');

		$this->assertFalse($this->object->hasToken('abc', false), 'Line: ' . __LINE__ . ' Should return false with wrong token');
		$this->assertEquals('active', $this->object->getState(), 'Line: ' . __LINE__ . ' State should not be set to expired');

		$this->assertFalse($this->object->hasToken('abc'), 'Line: ' . __LINE__ . ' Should return false with wrong token');
		$this->assertEquals('expired', $this->object->getState(), 'Line: ' . __LINE__ . ' State should be set to expired by default');
	}

	/**
	 * Test getName
	 *
	 * @covers  JSession::getName
	 *
	 * @return void
	 */
	public function testGetName()
	{
		$this->assertEquals(session_name(), $this->object->getName(), 'Session names should match.');
	}

	/**
	 * Test getId
	 *
	 * @covers  JSession::getId
	 *
	 * @return void
	 */
	public function testGetId()
	{
		$this->assertEquals(session_id(), $this->object->getId(), 'Session ids should match.');
	}

	/**
	 * Test getStores
	 *
	 * @covers  JSession::getStores
	 *
	 * @return void
	 */
	public function testGetStores()
	{
		$return = JSession::getStores();

		$this->assertTrue(
			is_array($return),
			'Line: ' . __LINE__ . ' JSession::getStores must return an array.'
		);
		$this->assertContains(
			'Database',
			$return,
			'Line: ' . __LINE__ . ' session storage database should always be available.'
		);
		$this->assertContains(
			'None',
			$return,
			'Line: ' . __LINE__ . ' session storage "none" should always be available.'
		);
	}

	/**
	 * Test isNew
	 *
	 * @return void
	 */
	public function testIsNew()
	{
		$this->object->set('session.counter', 1);

		$this->assertEquals(true, $this->object->isNew(), '$isNew should be true.');
	}

	/**
	 * Test...
	 *
	 * @todo Implement testGet().
	 *
	 * @return void
	 */
	public function testGet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testSet().
	 *
	 * @return void
	 */
	public function testSet()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testHas().
	 *
	 * @return void
	 */
	public function testHas()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testClear().
	 *
	 * @return void
	 */
	public function testClear()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testDestroy().
	 *
	 * @return void
	 */
	public function testDestroy()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testRestart().
	 *
	 * @return void
	 */
	public function testRestart()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testFork().
	 *
	 * @return void
	 */
	public function testFork()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Test...
	 *
	 * @todo Implement testClose().
	 *
	 * @return void
	 */
	public function testClose()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}
}
