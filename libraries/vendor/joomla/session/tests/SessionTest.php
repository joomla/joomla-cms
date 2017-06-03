<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Tests;

use Joomla\Session\Session;
use Joomla\Session\Storage\RuntimeStorage;
use Joomla\Session\Validator\AddressValidator;
use Joomla\Session\Validator\ForwardedValidator;
use Joomla\Test\TestHelper;

/**
 * Test class for Joomla\Session\Session.
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Session object for testing
	 *
	 * @var  Session
	 */
	private $session;

	/**
	 * Storage object for testing
	 *
	 * @var  RuntimeStorage
	 */
	private $storage;

	/**
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		$mockInput = $this->getMock('Joomla\Input\Input', array('get'));

		// Mock the Input object internals
		$mockServerInput = $this->getMock('Joomla\Input\Input', array('get', 'set'));
		$inputInternals = array(
			'server' => $mockServerInput
		);

		TestHelper::setValue($mockInput, 'inputs', $inputInternals);

		$this->storage = new RuntimeStorage;
		$this->session = new Session($this->storage);
		$addressValidator = new AddressValidator($mockInput, $this->session);
		$forwardedValidator = new ForwardedValidator($mockInput, $this->session);
		$this->session->addValidator($addressValidator);
		$this->session->addValidator($forwardedValidator);
	}

	/**
	 * Data provider for set tests
	 *
	 * @return  array
	 */
	public function setProvider()
	{
		return array(
			array('joomla', 'rocks'),
			array('joomla.framework', 'too much awesomeness')
		);
	}

	/**
	 * @covers  Joomla\Session\Session::__construct()
	 * @covers  Joomla\Session\Session::getState()
	 * @covers  Joomla\Session\Session::isActive()
	 * @covers  Joomla\Session\Session::setDispatcher()
	 * @covers  Joomla\Session\Session::setOptions()
	 */
	public function testValidateASessionObjectIsCreatedCorrectly()
	{
		// Build a mock event dispatcher
		$mockDispatcher = $this->getMock('\\Joomla\\Event\\DispatcherInterface');

		$session = new Session($this->storage, $mockDispatcher);

		// The state should be inactive
		$this->assertSame('inactive', $session->getState());

		// And the session should not be active
		$this->assertFalse($session->isActive());
	}

	/**
	 * @covers  Joomla\Session\Session::getId()
	 * @covers  Joomla\Session\Session::isActive()
	 * @covers  Joomla\Session\Session::isNew()
	 * @covers  Joomla\Session\Session::isStarted()
	 * @covers  Joomla\Session\Session::setCounter()
	 * @covers  Joomla\Session\Session::setTimers()
	 * @covers  Joomla\Session\Session::start()
	 * @covers  Joomla\Session\Session::validate()
	 */
	public function testValidateASessionStartsCorrectly()
	{
		// There shouldn't be an ID yet
		$this->assertEmpty($this->session->getId());

		// The session should successfully start
		$this->session->start();
		$this->assertTrue($this->session->isStarted());

		// There should now be an ID
		$this->assertNotEmpty($this->session->getId());

		// And the session should be active
		$this->assertTrue($this->session->isActive());

		// As well as new
		$this->assertTrue($this->session->isNew());
	}

	/**
	 * @covers  Joomla\Session\Session::isStarted()
	 * @covers  Joomla\Session\Session::setCounter()
	 * @covers  Joomla\Session\Session::setDispatcher()
	 * @covers  Joomla\Session\Session::setTimers()
	 * @covers  Joomla\Session\Session::start()
	 * @covers  Joomla\Session\Session::validate()
	 */
	public function testValidateTheDispatcherIsTriggeredWhenTheSessionIsStarted()
	{
		// Build a mock event dispatcher
		$mockDispatcher = $this->getMock('\\Joomla\\Event\\DispatcherInterface');
		$mockDispatcher->expects($this->once())
			->method('dispatch');

		$this->session->setDispatcher($mockDispatcher);

		// The session should successfully start
		$this->session->start();
		$this->assertTrue($this->session->isStarted());
	}

	/**
	 * @covers  Joomla\Session\Session::getId()
	 * @covers  Joomla\Session\Session::setId()
	 * @covers  Joomla\Session\Session::setCounter()
	 * @covers  Joomla\Session\Session::setTimers()
	 * @covers  Joomla\Session\Session::start()
	 * @covers  Joomla\Session\Session::validate()
	 */
	public function testValidateAnInjectedSessionIdIsUsedWhenTheSessionStarts()
	{
		$mockId = '1234abcd';

		// Inject our ID
		$this->session->setId($mockId);

		// The session should successfully start
		$this->session->start();

		// The ID should match our injected value
		$this->assertSame($mockId, $this->session->getId());
	}

	/**
	 * @covers  Joomla\Session\Session::getName()
	 * @covers  Joomla\Session\Session::setName()
	 * @covers  Joomla\Session\Session::setCounter()
	 * @covers  Joomla\Session\Session::setTimers()
	 * @covers  Joomla\Session\Session::start()
	 * @covers  Joomla\Session\Session::validate()
	 */
	public function testValidateAnInjectedSessionNameIsUsedWhenTheSessionStarts()
	{
		$mockName = 'TestSessionName';

		// Inject our name
		$this->session->setName($mockName);

		// The session should successfully start
		$this->session->start();

		// The ID should match our injected value
		$this->assertSame($mockName, $this->session->getName());
	}

	/**
	 * @covers  Joomla\Session\Session::getHandlers()
	 */
	public function testValidateAListOfAvailableHandlersIsReturned()
	{
		// There should be at least one handler available in our test environment
		$this->assertGreaterThan(0, Session::getHandlers());
	}

	/**
	 * @covers  Joomla\Session\Session::getIterator()
	 */
	public function testValidateAnIteratorIsReturned()
	{
		$this->assertInstanceOf('\\ArrayIterator', $this->session->getIterator());
	}

	/**
	 * @covers  Joomla\Session\Session::get()
	 */
	public function testValidateTheCorrectValueIsReturnedWhenGetIsCalled()
	{
		// Default return null
		$this->assertNull($this->session->get('foo'));

		// Return the specified default
		$this->assertSame('bar', $this->session->get('foo', 'bar'));
	}

	/**
	 * @param   string  $key    The key to set
	 * @param   string  $value  The value to set
	 *
	 * @dataProvider  setProvider
	 *
	 * @covers  Joomla\Session\Session::set()
	 * @uses    Joomla\Session\Session::get()
	 */
	public function testValidateAValueIsCorrectlyStoredToTheSession($key, $value)
	{
		$this->session->set($key, $value);
		$this->assertSame($value, $this->session->get($key));
	}

	/**
	 * @param   string  $key    The key to set
	 * @param   string  $value  The value to set
	 *
	 * @dataProvider  setProvider
	 *
	 * @covers  Joomla\Session\Session::has()
	 * @uses    Joomla\Session\Session::set()
	 */
	public function testValidateTheKeyIsCorrectlyCheckedForExistence($key, $value)
	{
		$this->session->set($key, $value);
		$this->assertTrue($this->session->has($key));
		$this->assertFalse($this->session->has($key . '.fake.ending'));
	}

	/**
	 * @covers  Joomla\Session\Session::remove()
	 * @uses    Joomla\Session\Session::has()
	 * @uses    Joomla\Session\Session::set()
	 */
	public function testValidateAKeyIsCorrectlyRemovedFromTheStore()
	{
		$this->session->set('foo', 'bar');
		$this->assertTrue($this->session->has('foo'));

		$this->session->remove('foo');
		$this->assertFalse($this->session->has('foo'));
	}

	/**
	 * @covers  Joomla\Session\Session::all()
	 * @covers  Joomla\Session\Session::clear()
	 * @uses    Joomla\Session\Session::set()
	 */
	public function testValidateAllDataIsReturnedFromTheSessionStore()
	{
		// Set some data into our session
		$this->session->set('foo', 'bar');
		$this->session->set('joomla.framework', 'is awesome');

		$this->assertArrayHasKey(
			'joomla.framework',
			$this->session->all()
		);

		// Now clear the data
		$this->session->clear();
		$this->assertEmpty($this->session->all());
	}

	/**
	 * @covers  Joomla\Session\Session::destroy()
	 * @uses    Joomla\Session\Session::fork()
	 * @uses    Joomla\Session\Session::getId()
	 * @uses    Joomla\Session\Session::getState()
	 * @uses    Joomla\Session\Session::set()
	 * @uses    Joomla\Session\Session::start()
	 */
	public function testValidateTheSessionIsCorrectlyDestroyed()
	{
		// First start a session to destroy it
		$this->session->start();

		// Grab the session ID to check in a moment
		$sessionId = $this->session->getId();

		// And add some data to validate it is cleared
		$this->session->set('foo', 'bar');

		// Now destroy the session
		$this->assertTrue($this->session->destroy());

		// Validate the destruction
		$this->assertNotSame($sessionId, $this->session->getId());
		$this->assertArrayNotHasKey('foo', $this->session->all());
		$this->assertSame('destroyed', $this->session->getState());
	}

	/**
	 * @covers  Joomla\Session\Session::restart()
	 * @covers  Joomla\Session\Session::setDispatcher()
	 * @covers  Joomla\Session\Session::validate()
	 * @uses    Joomla\Session\Session::getId()
	 * @uses    Joomla\Session\Session::set()
	 * @uses    Joomla\Session\Session::start()
	 */
	public function testValidateTheSessionIsCorrectlyRestarted()
	{
		// Build a mock event dispatcher
		$mockDispatcher = $this->getMock('\\Joomla\\Event\\DispatcherInterface');

		$this->session->setDispatcher($mockDispatcher);
		$this->session->start();

		// Grab the session ID to check in a moment
		$sessionId = $this->session->getId();

		// And add some data to validate it is carried forward
		$this->session->set('foo', 'bar');

		// Now restart the session
		$mockDispatcher->expects($this->once())
			->method('dispatch');
		$this->assertTrue($this->session->restart());

		// Validate the restart
		$this->assertNotSame($sessionId, $this->session->getId());
		$this->assertArrayHasKey('foo', $this->session->all());
		$this->assertSame('active', $this->session->getState());
	}

	/**
	 * @covers  Joomla\Session\Session::fork()
	 * @uses    Joomla\Session\Session::all()
	 * @uses    Joomla\Session\Session::getId()
	 * @uses    Joomla\Session\Session::getState()
	 * @uses    Joomla\Session\Session::set()
	 * @uses    Joomla\Session\Session::start()
	 */
	public function testValidateTheSessionIsCorrectlyForkedWithoutDestruction()
	{
		// First make sure an inactive session cannot be forked
		$this->assertFalse($this->session->fork());

		$this->session->start();

		// Grab the session ID to check in a moment
		$sessionId = $this->session->getId();

		// And add some data to validate it is carried forward
		$this->session->set('foo', 'bar');

		// Now fork the session
		$this->assertTrue($this->session->fork());

		// Validate the fork
		$this->assertSame($sessionId, $this->session->getId());
		$this->assertArrayHasKey('foo', $this->session->all());
		$this->assertSame('active', $this->session->getState());
	}

	/**
	 * @covers  Joomla\Session\Session::fork()
	 * @uses    Joomla\Session\Session::all()
	 * @uses    Joomla\Session\Session::getId()
	 * @uses    Joomla\Session\Session::getState()
	 * @uses    Joomla\Session\Session::set()
	 * @uses    Joomla\Session\Session::start()
	 */
	public function testValidateTheSessionIsCorrectlyForkedWithDestruction()
	{
		$this->session->start();

		// Grab the session ID to check in a moment
		$sessionId = $this->session->getId();

		// And add some data to validate it is carried forward
		$this->session->set('foo', 'bar');

		// Now fork the session
		$this->assertTrue($this->session->fork(true));

		// Validate the fork
		$this->assertNotSame($sessionId, $this->session->getId());
		$this->assertArrayHasKey('foo', $this->session->all());
		$this->assertSame('active', $this->session->getState());
	}

	/**
	 * @covers  Joomla\Session\Session::close()
	 * @uses    Joomla\Session\Session::getState()
	 * @uses    Joomla\Session\Session::start()
	 */
	public function testValidateTheSessionIsCorrectlyClosed()
	{
		$this->session->start();

		// Now close the session
		$this->session->close();

		// Validate the closure
		$this->assertSame('closed', $this->session->getState());
	}
}
